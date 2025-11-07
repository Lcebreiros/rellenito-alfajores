<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Lista de métodos de pago activos
     */
    public function index(Request $request)
    {
        $auth = $request->user();

        // Determinar el user_id apropiado
        if ($auth->isCompany()) {
            $userId = $auth->id;
        } else {
            $company = $auth->rootCompany();
            $userId = $company ? $company->id : $auth->id;
        }

        $query = PaymentMethod::where('user_id', $userId)
            ->when($request->filled('is_active'), function ($q) use ($request) {
                $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($request->filled('is_global'), function ($q) use ($request) {
                $q->where('is_global', filter_var($request->is_global, FILTER_VALIDATE_BOOLEAN));
            });

        $paymentMethods = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $paymentMethods,
        ], 200);
    }

    /**
     * Mostrar un método de pago específico
     */
    public function show(Request $request, PaymentMethod $paymentMethod)
    {
        $auth = $request->user();

        if (!$this->canAccessPaymentMethod($auth, $paymentMethod)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este método de pago',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $paymentMethod,
        ], 200);
    }

    /**
     * Crear un nuevo método de pago
     */
    public function store(Request $request)
    {
        $auth = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_global' => 'boolean',
            'requires_reference' => 'boolean',
            'icon' => 'nullable|string|max:50',
        ]);

        // Determinar el user_id apropiado
        if ($auth->isCompany()) {
            $validated['user_id'] = $auth->id;
        } else {
            $company = $auth->rootCompany();
            $validated['user_id'] = $company ? $company->id : $auth->id;
        }

        $paymentMethod = PaymentMethod::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Método de pago creado exitosamente',
            'data' => $paymentMethod,
        ], 201);
    }

    /**
     * Actualizar un método de pago existente
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $auth = $request->user();

        if (!$this->canManagePaymentMethod($auth, $paymentMethod)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este método de pago',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_global' => 'boolean',
            'requires_reference' => 'boolean',
            'icon' => 'nullable|string|max:50',
        ]);

        $paymentMethod->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Método de pago actualizado exitosamente',
            'data' => $paymentMethod,
        ], 200);
    }

    /**
     * Activar/desactivar un método de pago
     */
    public function toggleActive(Request $request, PaymentMethod $paymentMethod)
    {
        $auth = $request->user();

        if (!$this->canManagePaymentMethod($auth, $paymentMethod)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar este método de pago',
            ], 403);
        }

        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado exitosamente',
            'data' => $paymentMethod,
        ], 200);
    }

    /**
     * Eliminar un método de pago
     */
    public function destroy(Request $request, PaymentMethod $paymentMethod)
    {
        $auth = $request->user();

        if (!$this->canManagePaymentMethod($auth, $paymentMethod)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este método de pago',
            ], 403);
        }

        $paymentMethod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Método de pago eliminado exitosamente',
        ], 200);
    }

    /**
     * Verificar si el usuario puede acceder al método de pago
     */
    private function canAccessPaymentMethod($user, PaymentMethod $paymentMethod): bool
    {
        if ($user->isMaster()) {
            return true;
        }

        if ($user->isCompany()) {
            return $paymentMethod->user_id === $user->id;
        }

        $company = $user->rootCompany();
        return $paymentMethod->user_id === $company?->id;
    }

    /**
     * Verificar si el usuario puede gestionar el método de pago
     */
    private function canManagePaymentMethod($user, PaymentMethod $paymentMethod): bool
    {
        return $this->canAccessPaymentMethod($user, $paymentMethod);
    }
}
