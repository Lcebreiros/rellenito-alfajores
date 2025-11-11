<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleCalendarController extends Controller
{
    protected GoogleCalendarService $googleCalendar;

    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->middleware('auth');
        $this->googleCalendar = $googleCalendar;
    }

    /**
     * Redirect user to Google OAuth page
     */
    public function redirect()
    {
        $authUrl = $this->googleCalendar->getAuthUrl();
        return redirect($authUrl);
    }

    /**
     * Handle callback from Google OAuth
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('dashboard')
                ->with('error', 'Error al conectar con Google Calendar: ' . $request->get('error'));
        }

        if (!$request->has('code')) {
            return redirect()->route('dashboard')
                ->with('error', 'No se recibió el código de autorización.');
        }

        try {
            $tokenData = $this->googleCalendar->handleCallback($request->get('code'));

            Auth::user()->update([
                'google_access_token' => $tokenData['access_token'],
                'google_refresh_token' => $tokenData['refresh_token'],
                'google_token_expires_at' => $tokenData['expires_at'],
                'google_email' => $tokenData['email'],
                'google_calendar_sync_enabled' => true,
            ]);

            return redirect()->route('settings')
                ->with('success', '¡Cuenta de Google Calendar conectada exitosamente! La sincronización automática está activa.');
        } catch (\Exception $e) {
            return redirect()->route('settings')
                ->with('error', 'Error al conectar: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect from Google Calendar
     */
    public function disconnect()
    {
        $this->googleCalendar
            ->forUser(Auth::user())
            ->disconnect();

        return redirect()->route('settings')
            ->with('success', 'Desconectado de Google Calendar exitosamente.');
    }

    /**
     * Toggle sync enabled/disabled
     */
    public function toggleSync(Request $request)
    {
        $user = Auth::user();
        $syncEnabled = $request->boolean('sync_enabled');

        $user->update([
            'google_calendar_sync_enabled' => $syncEnabled,
        ]);

        $message = $syncEnabled
            ? 'Sincronización activada. Los nuevos pedidos se agregarán automáticamente a tu Google Calendar.'
            : 'Sincronización desactivada. Los pedidos solo se guardarán en Gestior.';

        return redirect()->route('settings')->with('success', $message);
    }

    /**
     * Get connection status
     */
    public function status()
    {
        $isConnected = $this->googleCalendar
            ->forUser(Auth::user())
            ->isConnected();

        return response()->json([
            'connected' => $isConnected,
        ]);
    }
}
