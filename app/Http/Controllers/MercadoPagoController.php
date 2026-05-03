<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MercadoPagoCredential;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class MercadoPagoController extends Controller
{
    public function __construct(private readonly MercadoPagoService $mp)
    {
        $this->middleware('auth');
    }

    /**
     * Paso 1: genera el state CSRF, lo guarda en sesión y redirige a MP.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $state = Str::random(40);
        $request->session()->put('mp_oauth_state', $state);

        return redirect($this->mp->getAuthorizationUrl($state));
    }

    /**
     * Paso 2: MP redirige aquí con code + state.
     * Valida el state, intercambia el código, obtiene info de cuenta y persiste.
     */
    public function callback(Request $request): RedirectResponse
    {
        // Validar state CSRF
        $sessionState = $request->session()->pull('mp_oauth_state');
        if (! $sessionState || ! hash_equals($sessionState, (string) $request->query('state', ''))) {
            return redirect()->route('payment-methods.index')
                ->with('error', __('mp.oauth_invalid_state'));
        }

        if ($request->filled('error')) {
            return redirect()->route('payment-methods.index')
                ->with('error', __('mp.oauth_denied'));
        }

        $code = $request->query('code');
        if (! $code) {
            return redirect()->route('payment-methods.index')
                ->with('error', __('mp.oauth_no_code'));
        }

        try {
            $tokenData = $this->mp->exchangeCode((string) $code);

            // Obtener datos de la cuenta MP para mostrar en UI
            $accountInfo = $this->mp->getAccountInfo($tokenData['access_token']);

            $company = auth()->user()->rootCompany() ?? auth()->user();

            MercadoPagoCredential::updateOrCreate(
                ['user_id' => $company->id],
                array_merge(
                    $this->mp->tokenDataToAttributes($tokenData),
                    [
                        'mp_email'    => $accountInfo['email'] ?? null,
                        'mp_nickname' => $accountInfo['nickname'] ?? null,
                        'mp_user_id'  => (string) ($accountInfo['id'] ?? $tokenData['user_id'] ?? ''),
                    ],
                ),
            );

        } catch (RuntimeException $e) {
            return redirect()->route('payment-methods.index')
                ->with('error', __('mp.oauth_exchange_failed') . ': ' . $e->getMessage());
        }

        return redirect()->route('payment-methods.index')
            ->with('ok', __('mp.oauth_connected'));
    }

    /**
     * Elimina las credenciales de la empresa (desconecta la cuenta MP).
     */
    public function disconnect(Request $request): RedirectResponse
    {
        $company = auth()->user()->rootCompany() ?? auth()->user();

        MercadoPagoCredential::where('user_id', $company->id)->delete();

        return redirect()->route('payment-methods.index')
            ->with('ok', __('mp.oauth_disconnected'));
    }

    /**
     * Devuelve los dispositivos Point disponibles para la empresa (JSON).
     */
    public function devices(Request $request): JsonResponse
    {
        $company = auth()->user()->rootCompany() ?? auth()->user();

        $credential = MercadoPagoCredential::where('user_id', $company->id)->first();

        if (! $credential) {
            return response()->json(['error' => 'not_connected'], 422);
        }

        try {
            $devices = $this->mp->getDevices($credential);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        return response()->json(['devices' => $devices]);
    }

    /**
     * Guarda el device_id seleccionado por la empresa para cobros con Point.
     */
    public function selectDevice(Request $request): JsonResponse
    {
        $request->validate(['device_id' => ['required', 'string', 'max:100']]);

        $company = auth()->user()->rootCompany() ?? auth()->user();

        $updated = MercadoPagoCredential::where('user_id', $company->id)
            ->update(['selected_device_id' => $request->input('device_id')]);

        if (! $updated) {
            return response()->json(['error' => 'not_connected'], 422);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Crea un payment intent en el Point de la empresa y lo devuelve al frontend.
     * Llamado desde el POS cuando el método seleccionado es Mercado Pago.
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'amount'             => ['required', 'numeric', 'min:1'],
            'description'        => ['nullable', 'string', 'max:200'],
            'external_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $company = auth()->user()->rootCompany() ?? auth()->user();
        $credential = MercadoPagoCredential::where('user_id', $company->id)->first();

        if (! $credential) {
            return response()->json(['error' => __('mp.not_connected')], 422);
        }

        if (! $credential->selected_device_id) {
            return response()->json(['error' => __('mp.no_device_selected')], 422);
        }

        try {
            $intent = $this->mp->createPaymentIntent(
                $credential,
                $credential->selected_device_id,
                [
                    'amount'             => (float) $request->input('amount'),
                    'description'        => $request->input('description', 'Venta'),
                    'external_reference' => $request->input('external_reference'),
                ],
            );
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        return response()->json($intent);
    }

    /**
     * Consulta el estado actual de un payment intent (para polling).
     */
    public function paymentIntentStatus(Request $request, string $intentId): JsonResponse
    {
        $company = auth()->user()->rootCompany() ?? auth()->user();
        $credential = MercadoPagoCredential::where('user_id', $company->id)->first();

        if (! $credential) {
            return response()->json(['error' => __('mp.not_connected')], 422);
        }

        try {
            $status = $this->mp->getPaymentIntentStatus($credential, $intentId);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        return response()->json($status);
    }

    /**
     * Cancela un payment intent activo en el Point.
     */
    public function cancelPaymentIntent(Request $request, string $intentId): JsonResponse
    {
        $company = auth()->user()->rootCompany() ?? auth()->user();
        $credential = MercadoPagoCredential::where('user_id', $company->id)->first();

        if (! $credential || ! $credential->selected_device_id) {
            return response()->json(['error' => __('mp.not_connected')], 422);
        }

        try {
            $result = $this->mp->cancelPaymentIntent(
                $credential,
                $credential->selected_device_id,
                $intentId,
            );
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        return response()->json($result);
    }
}
