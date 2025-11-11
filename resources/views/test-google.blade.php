<!DOCTYPE html>
<html>
<head>
    <title>Test Google Calendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-4">Test Google Calendar - User {{ auth()->user()->id }}</h1>

        <div class="space-y-4">
            <div>
                <strong>google_access_token:</strong>
                <span class="text-{{ auth()->user()->google_access_token ? 'green' : 'red' }}-600">
                    {{ auth()->user()->google_access_token ? 'SET' : 'NULL' }}
                </span>
            </div>

            <div>
                <strong>google_refresh_token:</strong>
                <span class="text-{{ auth()->user()->google_refresh_token ? 'green' : 'red' }}-600">
                    {{ auth()->user()->google_refresh_token ? 'SET' : 'NULL' }}
                </span>
            </div>

            <div>
                <strong>google_email:</strong>
                <span class="text-{{ auth()->user()->google_email ? 'green' : 'red' }}-600">
                    {{ auth()->user()->google_email ?? 'NULL' }}
                </span>
            </div>

            <div>
                <strong>google_calendar_sync_enabled:</strong>
                <span class="text-{{ auth()->user()->google_calendar_sync_enabled ? 'green' : 'red' }}-600">
                    {{ auth()->user()->google_calendar_sync_enabled ? 'TRUE' : 'FALSE' }}
                </span>
            </div>

            <div>
                <strong>Condición: (google_access_token && google_refresh_token):</strong>
                <span class="text-{{ (auth()->user()->google_access_token && auth()->user()->google_refresh_token) ? 'green' : 'red' }}-600">
                    {{ (auth()->user()->google_access_token && auth()->user()->google_refresh_token) ? 'CONECTADO' : 'NO CONECTADO' }}
                </span>
            </div>
        </div>

        <hr class="my-6">

        <h2 class="text-xl font-bold mb-4">Probá la tarjeta de Google Calendar:</h2>

        {{-- CARD: Google Calendar --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-100">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5C3.9 4 3 4.9 3 6v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Google Calendar</h2>
                    <p class="text-xs text-gray-500">Sincronización automática de eventos</p>
                </div>
            </div>

            @if(auth()->user()->google_access_token && auth()->user()->google_refresh_token)
                <div class="p-4 bg-green-50 rounded">
                    <p class="font-semibold text-green-900">✅ CONECTADO</p>
                    <p class="text-sm text-green-700">Email: {{ auth()->user()->google_email ?? 'No disponible' }}</p>
                </div>
            @else
                <div class="p-4 bg-red-50 rounded">
                    <p class="font-semibold text-red-900">❌ NO CONECTADO</p>
                    <a href="{{ route('google.connect') }}" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded">
                        Conectar con Google Calendar
                    </a>
                </div>
            @endif
        </div>

        <div class="mt-6">
            <a href="/settings" class="text-blue-600 hover:underline">← Volver a Settings</a>
        </div>
    </div>
</body>
</html>
