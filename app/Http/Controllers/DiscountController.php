<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscountController extends Controller
{
    /**
     * Obtener el company_id del usuario actual
     */
    private function currentCompanyId(): int
    {
        $user = Auth::user();

        if ($user && $user->parent_id) {
            return (int) $user->parent_id;
        }

        return (int) Auth::id();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyId = $this->currentCompanyId();

        $discounts = Discount::where('company_id', $companyId)
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(20);

        return view('discounts.index', compact('discounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('discounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'required|in:free_minutes,percentage,fixed_amount',
            'value' => 'required|numeric|min:0',
            'partner' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $validated['company_id'] = $this->currentCompanyId();
        $validated['is_active'] = $request->has('is_active');

        Discount::create($validated);

        return redirect()
            ->route('discounts.index')
            ->with('success', 'Descuento creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discount $discount)
    {
        // Verificar que el descuento pertenece a la company del usuario
        if ($discount->company_id !== $this->currentCompanyId()) {
            abort(403);
        }

        return view('discounts.edit', compact('discount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discount $discount)
    {
        // Verificar que el descuento pertenece a la company del usuario
        if ($discount->company_id !== $this->currentCompanyId()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'required|in:free_minutes,percentage,fixed_amount',
            'value' => 'required|numeric|min:0',
            'partner' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $discount->update($validated);

        return redirect()
            ->route('discounts.index')
            ->with('success', 'Descuento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount)
    {
        // Verificar que el descuento pertenece a la company del usuario
        if ($discount->company_id !== $this->currentCompanyId()) {
            abort(403);
        }

        $discount->delete();

        return redirect()
            ->route('discounts.index')
            ->with('success', 'Descuento eliminado exitosamente.');
    }

    /**
     * Toggle active status
     */
    public function toggle(Discount $discount)
    {
        // Verificar que el descuento pertenece a la company del usuario
        if ($discount->company_id !== $this->currentCompanyId()) {
            abort(403);
        }

        $discount->update(['is_active' => !$discount->is_active]);

        $status = $discount->is_active ? 'activado' : 'desactivado';

        return redirect()
            ->route('discounts.index')
            ->with('success', "Descuento {$status} exitosamente.");
    }
}
