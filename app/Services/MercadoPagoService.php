<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MercadoPagoCredential;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Encapsula toda la comunicación con la API de Mercado Pago:
 *   - Flujo OAuth (autorización, intercambio de código, refresh de token)
 *   - API de Point (listar dispositivos, crear payment intent, consultar estado)
 *
 * No depende de Request ni de sesión — es un servicio puro.
 */
final class MercadoPagoService
{
    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
        private readonly string $authUrl,
        private readonly string $apiUrl,
    ) {}

    // ─── OAuth ────────────────────────────────────────────────────────────────

    /**
     * Genera la URL a la que redirigir al usuario para autorizar la app.
     *
     * @param  string $state Token CSRF almacenado en sesión por el controller.
     */
    public function getAuthorizationUrl(string $state): string
    {
        return $this->authUrl . '?' . http_build_query([
            'client_id'     => $this->clientId,
            'response_type' => 'code',
            'platform_id'   => 'mp',
            'state'         => $state,
            'redirect_uri'  => $this->redirectUri,
        ]);
    }

    /**
     * Intercambia el código de autorización por access_token + refresh_token.
     *
     * @throws RuntimeException si la respuesta de MP indica error.
     */
    public function exchangeCode(string $code): array
    {
        return $this->requestToken([
            'grant_type'   => 'authorization_code',
            'code'         => $code,
            'redirect_uri' => $this->redirectUri,
        ]);
    }

    /**
     * Renueva el access_token usando el refresh_token almacenado.
     * Persiste el resultado y devuelve la credencial actualizada.
     *
     * @throws RuntimeException si el refresh falla.
     */
    public function refreshToken(MercadoPagoCredential $credential): MercadoPagoCredential
    {
        if (! $credential->refresh_token) {
            throw new RuntimeException('No hay refresh_token disponible para esta credencial.');
        }

        $data = $this->requestToken([
            'grant_type'    => 'refresh_token',
            'refresh_token' => $credential->refresh_token,
        ]);

        $credential->update($this->tokenDataToAttributes($data));

        return $credential->refresh();
    }

    /**
     * Devuelve la credencial lista para usar: la refresca si está próxima a vencer.
     */
    public function ensureFreshToken(MercadoPagoCredential $credential): MercadoPagoCredential
    {
        if ($credential->needsRefresh()) {
            return $this->refreshToken($credential);
        }

        return $credential;
    }

    // ─── API de Point ─────────────────────────────────────────────────────────

    /**
     * Lista los dispositivos Point vinculados a la cuenta MP de la credencial.
     *
     * @return array<int, array{id: string, name: string, device_name: string, status: string}>
     */
    public function getDevices(MercadoPagoCredential $credential): array
    {
        $credential = $this->ensureFreshToken($credential);

        $response = $this->get('/point/integration-api/devices', $credential->access_token);

        return $response['devices'] ?? [];
    }

    /**
     * Cambia el modo de operación de un dispositivo Point.
     * $mode: 'PDV' | 'STANDALONE'
     */
    public function setDeviceOperatingMode(
        MercadoPagoCredential $credential,
        string $deviceId,
        string $mode = 'PDV',
    ): array {
        $credential = $this->ensureFreshToken($credential);

        return $this->patch(
            "/point/integration-api/devices/{$deviceId}",
            $credential->access_token,
            ['operating_mode' => $mode],
        );
    }

    /**
     * Crea un payment intent en el dispositivo Point indicado.
     *
     * @param  array{amount: float, description?: string, external_reference?: string} $payment
     */
    public function createPaymentIntent(
    MercadoPagoCredential $credential,
    string $deviceId,
    array $payment,
): array {
    $credential = $this->ensureFreshToken($credential);

    // MP Point API espera el amount como entero en centavos
    $amountInCents = (int) round((float) $payment['amount'] * 100);

    return $this->post(
        "/point/integration-api/devices/{$deviceId}/payment-intents",
        $credential->access_token,
        [
            'amount' => $amountInCents,
            'additional_info' => [
                // 'external_reference' DEBE SER STRING. 
                // Usamos el ID de la orden que viene de Helipso.
                'external_reference' => (string) ($payment['external_reference'] ?? ''),
                'print_on_terminal'  => true,
            ],
        ],
    );
}

    /**
     * Consulta el estado de un payment intent (para polling desde el frontend).
     */
    public function getPaymentIntentStatus(
        MercadoPagoCredential $credential,
        string $paymentIntentId,
    ): array {
        $credential = $this->ensureFreshToken($credential);

        return $this->get(
            "/point/integration-api/payment-intents/{$paymentIntentId}",
            $credential->access_token,
        );
    }

    /**
     * Cancela un payment intent pendiente en el dispositivo.
     */
    public function cancelPaymentIntent(
        MercadoPagoCredential $credential,
        string $deviceId,
        string $paymentIntentId,
    ): array {
        $credential = $this->ensureFreshToken($credential);

        return $this->delete(
            "/point/integration-api/devices/{$deviceId}/payment-intents/{$paymentIntentId}",
            $credential->access_token,
        );
    }

    // ─── Cuenta de usuario MP ─────────────────────────────────────────────────

    /**
     * Obtiene información básica de la cuenta MP (email, nickname, id).
     */
    public function getAccountInfo(string $accessToken): array
    {
        return $this->get('/users/me', $accessToken);
    }

    // ─── Helpers internos ─────────────────────────────────────────────────────

    private function requestToken(array $extra): array
    {
        $response = Http::asForm()->post(
            $this->apiUrl . '/oauth/token',
            array_merge([
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ], $extra),
        );

        if ($response->failed()) {
            $error = $response->json('error_description') ?? $response->json('error') ?? 'Error desconocido';
            throw new RuntimeException("MP OAuth error: {$error}");
        }

        return $response->json();
    }

    private function get(string $path, string $token): array
    {
        $response = Http::withToken($token)
            ->get($this->apiUrl . $path);

        if ($response->failed()) {
            throw new RuntimeException("MP API GET {$path} error: " . $response->body());
        }

        return $response->json();
    }

    private function post(string $path, string $token, array $body): array
    {
        $response = Http::withToken($token)
            ->post($this->apiUrl . $path, $body);

        if ($response->failed()) {
            throw new RuntimeException("MP API POST {$path} error: " . $response->body());
        }

        return $response->json();
    }

    private function patch(string $path, string $token, array $body): array
    {
        $response = Http::withToken($token)
            ->patch($this->apiUrl . $path, $body);

        if ($response->failed()) {
            throw new RuntimeException("MP API PATCH {$path} error: " . $response->body());
        }

        return $response->json() ?? [];
    }

    private function delete(string $path, string $token): array
    {
        $response = Http::withToken($token)
            ->delete($this->apiUrl . $path);

        if ($response->failed()) {
            throw new RuntimeException("MP API DELETE {$path} error: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Convierte la respuesta del endpoint de token en atributos del modelo.
     */
    public function tokenDataToAttributes(array $data): array
    {
        return [
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'token_type'    => $data['token_type'] ?? 'bearer',
            'scope'         => $data['scope'] ?? null,
            'mp_user_id'    => (string) ($data['user_id'] ?? ''),
            'expires_at'    => isset($data['expires_in'])
                ? now()->addSeconds((int) $data['expires_in'])
                : null,
        ];
    }
}
