<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Listar facturas
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        $query = Invoice::where('company_id', $companyId)
            ->with(['client', 'order']);

        // Filtros
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('voucher_number', 'LIKE', "%{$search}%")
                    ->orWhere('client_name', 'LIKE', "%{$search}%")
                    ->orWhere('client_cuit', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('voucher_type')) {
            $query->where('voucher_type', $request->input('voucher_type'));
        }

        if ($request->has('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->input('from_date'));
        }

        if ($request->has('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->input('to_date'));
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        // Ordenar
        $sortBy = $request->input('sort_by', 'invoice_date');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = min($request->input('per_page', 15), 100);
        $invoices = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * Ver factura
     */
    public function show(Invoice $invoice)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        if ($invoice->company_id != $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $invoice->load(['client', 'order', 'items']);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Crear factura
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'order_id' => 'nullable|exists:orders,id',
            'voucher_type' => 'required|string|in:A,B,C,X',
            'client_name' => 'required|string|max:255',
            'client_cuit' => 'nullable|string|max:20',
            'client_tax_id' => 'nullable|string|max:20',
            'client_address' => 'nullable|string|max:500',
            'client_tax_condition' => 'nullable|string|max:100',
            'invoice_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['company_id'] = $companyId;
        $validated['status'] = 'draft';

        // Calcular totales
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($validated['items'] as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemSubtotal;

            if (isset($item['tax_rate']) && $item['tax_rate'] > 0) {
                $taxAmount += $itemSubtotal * ($item['tax_rate'] / 100);
            }
        }

        $validated['subtotal'] = $subtotal;
        $validated['tax_amount'] = $taxAmount;
        $validated['total'] = $subtotal + $taxAmount;

        // Crear factura
        $items = $validated['items'];
        unset($validated['items']);

        $invoice = Invoice::create($validated);

        // Crear items
        foreach ($items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'] ?? 0,
                'subtotal' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        $invoice->load(['client', 'order', 'items']);

        return response()->json([
            'success' => true,
            'message' => 'Factura creada exitosamente',
            'data' => $invoice,
        ], 201);
    }

    /**
     * Enviar factura por email
     */
    public function send(Invoice $invoice)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        if ($invoice->company_id != $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        // Verificar que el cliente tenga email
        if (!$invoice->client || !$invoice->client->email) {
            return response()->json([
                'success' => false,
                'message' => 'El cliente no tiene un email registrado',
            ], 422);
        }

        // Enviar email con la factura
        try {
            \Illuminate\Support\Facades\Mail::to($invoice->client->email)
                ->send(new \App\Mail\InvoiceMail($invoice));

            return response()->json([
                'success' => true,
                'message' => 'Factura enviada correctamente a ' . $invoice->client->email,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sending invoice email', [
                'invoice_id' => $invoice->id,
                'client_email' => $invoice->client->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener ID de la compañía del usuario autenticado
     */
    private function getCompanyId($user): int
    {
        if ($user->isCompany()) {
            return $user->id;
        }

        if ($user->parent_id) {
            return $user->parent_id;
        }

        return $user->id;
    }
}
