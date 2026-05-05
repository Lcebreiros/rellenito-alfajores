<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Models\SupplyPurchase;
use App\Models\Supplier;
use App\Models\SupplierExpense;
use App\Services\UnitConverter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $currentMonth = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $currentMonth);
        $user = auth()->user();

        // Month selector: derive range from oldest record → today (no full table scan)
        $oldestPurchase = SupplyPurchase::selectRaw('MIN(IFNULL(purchased_at, DATE(created_at))) as d')->value('d');
        $oldestExpense  = SupplierExpense::whereNotNull('expense_date')->min('expense_date');
        $oldest = collect([$oldestPurchase, $oldestExpense])->filter()->min();

        $months = collect();
        $cursor = now()->startOfMonth();
        $stop   = $oldest ? \Carbon\Carbon::parse($oldest)->startOfMonth() : $cursor->copy();
        while ($cursor->gte($stop)) {
            $months->push($cursor->format('Y-m'));
            $cursor->subMonth();
        }
        if (!$months->contains($currentMonth)) {
            $months = $months->prepend($currentMonth)->values();
        }

        // Items: filter at DB level for the requested month only
        $rawPurchases = SupplyPurchase::with(['supply:id,name,base_unit'])
            ->where(function ($q) use ($year, $mon) {
                $q->where(fn($q1) => $q1->whereNotNull('purchased_at')
                    ->whereYear('purchased_at', $year)->whereMonth('purchased_at', $mon))
                  ->orWhere(fn($q2) => $q2->whereNull('purchased_at')
                    ->whereYear('created_at', $year)->whereMonth('created_at', $mon));
            })
            ->orderByRaw('IFNULL(purchased_at, DATE(created_at)) DESC')
            ->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'type'     => 'supply',
                'date'     => $p->purchased_at?->format('Y-m-d') ?? $p->created_at->format('Y-m-d'),
                'name'     => $p->supply?->name ?? '—',
                'detail'   => $p->qty . ' ' . $p->unit,
                'amount'   => (float) $p->total_cost,
                'supplier' => null,
                'category' => null,
            ]);

        $rawExpenses = SupplierExpense::with(['supplier:id,name'])
            ->whereNotNull('expense_date')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $mon)
            ->orderByDesc('expense_date')
            ->get()
            ->map(fn($e) => [
                'id'       => $e->id,
                'type'     => 'expense',
                'date'     => $e->expense_date->format('Y-m-d'),
                'name'     => $e->description,
                'detail'   => SupplierExpense::CATEGORIES[$e->category] ?? ($e->category ?? '—'),
                'amount'   => (float) ($e->cost * $e->quantity),
                'supplier' => $e->supplier?->name,
                'category' => $e->category,
            ]);

        $filtered       = $rawPurchases->concat($rawExpenses)->sortByDesc('date');
        $grouped        = $filtered->groupBy('date')->sortKeysDesc();
        $totalPurchases = $rawPurchases->sum('amount');
        $totalExpenses  = $rawExpenses->sum('amount');

        $supplies  = Supply::availableFor($user)->select('id', 'name', 'base_unit')->orderBy('name')->get();
        $suppliers = Supplier::select('id', 'name')->where('is_active', true)->orderBy('name')->get();

        return view('purchases.index', compact(
            'grouped', 'totalPurchases', 'totalExpenses',
            'currentMonth', 'months', 'supplies', 'suppliers'
        ));
    }

    public function storeSupply(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supply_id'    => ['required', 'integer'],
            'qty'          => ['required', 'numeric', 'gt:0'],
            'unit'         => ['required', 'string', 'max:10'],
            'total_cost'   => ['required', 'numeric', 'gt:0'],
            'purchased_at' => ['nullable', 'date'],
        ]);

        $supply = Supply::availableFor(auth()->user())->findOrFail($data['supply_id']);

        try {
            $factor = UnitConverter::factorToBase($data['unit'], $supply->base_unit);
        } catch (\InvalidArgumentException) {
            return back()->withErrors(['unit' => __('purchases.unit_incompatible')])->withInput();
        }

        $baseQty = $data['qty'] * $factor;

        SupplyPurchase::create([
            'supply_id'    => $supply->id,
            'qty'          => $data['qty'],
            'unit'         => $data['unit'],
            'unit_to_base' => $factor,
            'total_cost'   => $data['total_cost'],
            'purchased_at' => $data['purchased_at'] ?? now()->toDateString(),
        ]);

        $oldStock = (float) $supply->stock_base_qty;
        $oldAvg   = (float) $supply->avg_cost_per_base;
        $newStock = $oldStock + $baseQty;
        $newAvg   = $newStock > 0 ? (($oldStock * $oldAvg) + $data['total_cost']) / $newStock : 0;

        $supply->update([
            'stock_base_qty'    => $newStock,
            'avg_cost_per_base' => $newAvg,
        ]);

        return back()->with('ok', __('purchases.supply_stored'));
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'description'  => ['required', 'string', 'max:255'],
            'category'     => ['nullable', 'string', 'max:50'],
            'cost'         => ['required', 'numeric', 'gt:0'],
            'quantity'     => ['nullable', 'numeric', 'gt:0'],
            'supplier_id'  => ['nullable', 'integer'],
            'expense_date' => ['nullable', 'date'],
        ]);

        SupplierExpense::create([
            'supplier_id'  => $data['supplier_id'] ?? null,
            'description'  => $data['description'],
            'category'     => $data['category'] ?? 'otros',
            'cost'         => $data['cost'],
            'quantity'     => $data['quantity'] ?? 1,
            'unit'         => 'u',
            'frequency'    => 'unica',
            'is_active'    => true,
            'expense_date' => $data['expense_date'] ?? now()->toDateString(),
        ]);

        return back()->with('ok', __('purchases.expense_stored'));
    }

    public function destroySupply(SupplyPurchase $purchase): RedirectResponse
    {
        $supply = $purchase->supply;
        $purchase->delete();

        // Recalculate stock and weighted average from remaining purchases
        $supply?->recomputeFromPurchases();

        return back()->with('ok', __('purchases.deleted'));
    }

    public function destroyExpense(SupplierExpense $expense): RedirectResponse
    {
        $expense->delete();
        return back()->with('ok', __('purchases.deleted'));
    }
}
