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
            ]);

            return redirect()->route('dashboard')
                ->with('success', '¡Conectado exitosamente con Google Calendar!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
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

        return redirect()->route('dashboard')
            ->with('success', 'Desconectado de Google Calendar.');
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
