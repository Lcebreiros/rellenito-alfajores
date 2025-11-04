<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request): View
    {
        $user = auth()->user();

        $paymentMethods = PaymentMethod::query()
            ->forUser($user)
            ->ordered()
            ->get();

        return view('payment-methods.index', compact('paymentMethods'));
    }

    public function create(): View
    {
        return view('payment-methods.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePaymentMethod($request);

        // Asignar el user_id (company_id para el scope)
        $user = auth()->user();
        $data['user_id'] = $user->isCompany() ? $user->id : \App\Models\Order::findRootCompanyId($user);

        PaymentMethod::create($data);

        return redirect()->route('payment-methods.index')->with('ok', 'Método de pago creado exitosamente.');
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        // Verificar que el usuario puede editar este método
        $user = auth()->user();
        if (!$user->isMaster() && $paymentMethod->user_id !== $user->id) {
            abort(403, 'No tienes permisos para editar este método de pago.');
        }

        return view('payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        // Verificar permisos
        $user = auth()->user();
        if (!$user->isMaster() && $paymentMethod->user_id !== $user->id) {
            abort(403, 'No tienes permisos para editar este método de pago.');
        }

        $data = $this->validatePaymentMethod($request, $paymentMethod->id);
        $paymentMethod->update($data);

        return redirect()->route('payment-methods.index')->with('ok', 'Método de pago actualizado exitosamente.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        // Verificar permisos
        $user = auth()->user();
        if (!$user->isMaster() && $paymentMethod->user_id !== $user->id) {
            abort(403, 'No tienes permisos para eliminar este método de pago.');
        }

        // Verificar si tiene órdenes asociadas
        if ($paymentMethod->orders()->count() > 0) {
            return redirect()
                ->route('payment-methods.index')
                ->with('error', 'No se puede eliminar este método de pago porque tiene órdenes asociadas. Puedes desactivarlo en su lugar.');
        }

        $paymentMethod->delete();

        return redirect()->route('payment-methods.index')->with('ok', 'Método de pago eliminado exitosamente.');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(PaymentMethod $paymentMethod): RedirectResponse
    {
        // Verificar permisos
        $user = auth()->user();
        if (!$user->isMaster() && $paymentMethod->user_id !== $user->id) {
            abort(403, 'No tienes permisos para modificar este método de pago.');
        }

        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);

        $status = $paymentMethod->is_active ? 'activado' : 'desactivado';
        return redirect()->route('payment-methods.index')->with('ok', "Método de pago {$status} exitosamente.");
    }

    protected function validatePaymentMethod(Request $request, ?int $id = null): array
    {
        $userId = auth()->user()->isCompany() ? auth()->id() : \App\Models\Order::findRootCompanyId(auth()->user());

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('payment_methods', 'slug')
                    ->where('user_id', $userId)
                    ->ignore($id)
            ],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'requires_gateway' => ['boolean'],
            'gateway_provider' => ['nullable', 'string', 'max:50'],
            'gateway_config' => ['nullable', 'array'],
            'sort_order' => ['integer', 'min:0'],
        ]);
    }
}
