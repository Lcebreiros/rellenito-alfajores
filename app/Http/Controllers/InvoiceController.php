<?php

namespace App\Http\Controllers;

use App\Models\ArcaConfiguration;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Order;
use App\Services\ArcaService;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Mostrar configuración de ARCA
     */
    public function configuration()
    {
        $config = ArcaConfiguration::where('company_id', Auth::id())->first();

        return view('invoices.configuration', compact('config'));
    }

    /**
     * Guardar o actualizar configuración de ARCA
     */
    public function saveConfiguration(Request $request)
    {
        $validated = $request->validate([
            'cuit' => 'required|string|size:13',
            'business_name' => 'required|string|max:255',
            'tax_condition' => 'required|in:IVA Responsable Inscripto,Monotributo,Exento,No Responsable,Consumidor Final',
            'environment' => 'required|in:testing,production',
            'certificate' => 'nullable|file|mimes:crt,pem,txt',
            'private_key' => 'nullable|file|mimes:key,pem,txt',
            'certificate_password' => 'nullable|string',
            'default_sale_point' => 'required|integer|min:1',
        ]);

        $config = ArcaConfiguration::updateOrCreate(
            ['company_id' => Auth::id()],
            [
                'cuit' => $validated['cuit'],
                'business_name' => $validated['business_name'],
                'tax_condition' => $validated['tax_condition'],
                'environment' => $validated['environment'],
                'default_sale_point' => $validated['default_sale_point'],
            ]
        );

        if ($request->hasFile('certificate')) {
            $config->certificate = file_get_contents($request->file('certificate')->getRealPath());
        }

        if ($request->hasFile('private_key')) {
            $config->private_key = file_get_contents($request->file('private_key')->getRealPath());
        }

        if ($request->filled('certificate_password')) {
            $config->certificate_password = $validated['certificate_password'];
        }

        $config->save();

        return redirect()->route('invoices.configuration')
            ->with('success', 'Configuración guardada correctamente');
    }

    /**
     * Listar facturas
     */
    public function index()
    {
        $invoices = Invoice::where('company_id', Auth::id())
            ->with(['client', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $config = ArcaConfiguration::where('company_id', Auth::id())->first();

        if (!$config || !$config->isConfigured()) {
            return redirect()->route('invoices.configuration')
                ->with('error', 'Debe configurar ARCA antes de crear facturas');
        }

        $clients = Client::where('user_id', Auth::id())->get();
        $orders = Order::where('user_id', Auth::id())
            ->whereNull('invoice_id')
            ->get();

        return view('invoices.create', compact('clients', 'orders', 'config'));
    }

    /**
     * Guardar nueva factura
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'order_id' => 'nullable|exists:orders,id',
            'voucher_type' => 'required|in:FC-A,FC-B,FC-C,NC-A,NC-B,NC-C,ND-A,ND-B,ND-C',
            'client_name' => 'required|string|max:255',
            'client_cuit' => 'nullable|string|size:13',
            'client_address' => 'nullable|string',
            'client_tax_condition' => 'required|in:IVA Responsable Inscripto,Monotributo,Exento,No Responsable,Consumidor Final',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        $config = ArcaConfiguration::where('company_id', Auth::id())->first();

        if (!$config || !$config->isConfigured()) {
            return redirect()->route('invoices.configuration')
                ->with('error', 'Debe configurar ARCA antes de crear facturas');
        }

        $invoice = Invoice::create([
            'company_id' => Auth::id(),
            'client_id' => $validated['client_id'],
            'order_id' => $validated['order_id'],
            'voucher_type' => $validated['voucher_type'],
            'sale_point' => $config->default_sale_point,
            'voucher_number' => 0,
            'client_name' => $validated['client_name'],
            'client_cuit' => $validated['client_cuit'],
            'client_address' => $validated['client_address'],
            'client_tax_condition' => $validated['client_tax_condition'],
            'invoice_date' => $validated['invoice_date'],
            'status' => 'draft',
        ]);

        foreach ($validated['items'] as $item) {
            $invoice->items()->create($item);
        }

        $invoice->calculateTotals();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Factura creada correctamente');
    }

    /**
     * Mostrar factura
     */
    public function show(Invoice $invoice)
    {
        if ($invoice->company_id !== Auth::id()) {
            abort(403);
        }

        $invoice->load(['client', 'items', 'order']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Editar factura
     */
    public function edit(Invoice $invoice)
    {
        if ($invoice->company_id !== Auth::id()) {
            abort(403);
        }

        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Solo se pueden editar facturas en borrador');
        }

        $clients = Client::where('user_id', Auth::id())->get();

        return view('invoices.edit', compact('invoice', 'clients'));
    }

    /**
     * Actualizar factura
     */
    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->company_id !== Auth::id()) {
            abort(403);
        }

        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Solo se pueden editar facturas en borrador');
        }

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_cuit' => 'nullable|string|size:13',
            'client_address' => 'nullable|string',
            'invoice_date' => 'required|date',
        ]);

        $invoice->update($validated);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Factura actualizada correctamente');
    }

    /**
     * Eliminar factura
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->company_id !== Auth::id()) {
            abort(403);
        }

        if ($invoice->status === 'approved') {
            return redirect()->route('invoices.index')
                ->with('error', 'No se pueden eliminar facturas aprobadas por ARCA');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Factura eliminada correctamente');
    }

    /**
     * Enviar factura a ARCA
     */
    public function sendToArca(Invoice $invoice)
    {
        if ($invoice->company_id !== Auth::id()) {
            abort(403);
        }

        if ($invoice->status === 'approved') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Esta factura ya fue aprobada por ARCA');
        }

        try {
            $config = ArcaConfiguration::where('company_id', Auth::id())->first();

            if (!$config || !$config->isConfigured()) {
                return redirect()->route('invoices.configuration')
                    ->with('error', 'Debe configurar ARCA antes de enviar facturas');
            }

            // Activar configuración si no está activa
            if (!$config->is_active) {
                $config->is_active = true;
                $config->save();
            }

            // Crear servicio ARCA
            $arcaService = new ArcaService($config);

            // Cambiar estado a pendiente
            $invoice->status = 'pending';
            $invoice->save();

            // Enviar a ARCA
            $result = $arcaService->sendInvoice($invoice);

            // Generar PDF si fue aprobada
            if ($result['success']) {
                $pdfService = new InvoicePdfService();
                $pdfService->generatePdf($invoice);
            }

            return redirect()->route('invoices.show', $invoice)
                ->with('success', $result['message'] . ' - CAE: ' . $result['cae']);
        } catch (\Exception $e) {
            // Volver a estado draft en caso de error
            $invoice->status = 'draft';
            $invoice->save();

            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Error al enviar a ARCA: ' . $e->getMessage());
        }
    }

    /**
     * Descargar PDF de factura
     */
    public function downloadPdf(Invoice $invoice)
    {
        if ($invoice->company_id !== Auth::id()) {
            abort(403);
        }

        // Si no tiene PDF, generarlo
        if (!$invoice->pdf_path || !Storage::disk('public')->exists($invoice->pdf_path)) {
            try {
                $pdfService = new InvoicePdfService();
                $pdfService->generatePdf($invoice);
            } catch (\Exception $e) {
                return redirect()->route('invoices.show', $invoice)
                    ->with('error', 'Error generando PDF: ' . $e->getMessage());
            }
        }

        return Storage::disk('public')->download($invoice->pdf_path);
    }

    /**
     * Regenerar PDF de factura
     */
    public function regeneratePdf(Invoice $invoice)
    {
        if ($invoice->company_id !== Auth::id()) {
            abort(403);
        }

        try {
            $pdfService = new InvoicePdfService();
            $pdfService->generatePdf($invoice);

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'PDF regenerado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Error regenerando PDF: ' . $e->getMessage());
        }
    }
}
