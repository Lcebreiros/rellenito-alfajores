<div class="max-w-2xl mx-auto p-6">
    <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-lg p-6 border border-neutral-200 dark:border-neutral-800">
        <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
            🚀 Prueba de Pusher en Tiempo Real
        </h2>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-200">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="mb-6">
            <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                Abre esta página en <strong>dos pestañas diferentes</strong> y haz clic en el botón.
                Verás la notificación aparecer en tiempo real en ambas pestañas! 🎉
            </p>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>💡 Tip:</strong> Abre la consola del navegador (F12 → Console) para ver los eventos de Pusher en acción.
                </p>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Mensaje de prueba (opcional)
                </label>
                <input
                    type="text"
                    wire:model="testMessage"
                    placeholder="Escribe un mensaje personalizado..."
                    class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:ring-indigo-600 focus:border-indigo-600"
                />
            </div>

            <button
                wire:click="sendTestNotification"
                wire:loading.attr="disabled"
                class="w-full px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50"
            >
                <span wire:loading.remove>📤 Enviar Notificación de Prueba</span>
                <span wire:loading>⏳ Enviando...</span>
            </button>
        </div>

        <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-800">
            <h3 class="font-semibold text-neutral-900 dark:text-neutral-100 mb-2">
                Estado de la conexión:
            </h3>
            <div id="pusher-status" class="text-sm">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200">
                    <span class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></span>
                    Conectando...
                </span>
            </div>
        </div>
    </div>

    {{-- Script para mostrar estado de Pusher --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusDiv = document.getElementById('pusher-status');

            if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
                const pusher = window.Echo.connector.pusher;

                pusher.connection.bind('connected', function() {
                    statusDiv.innerHTML = `
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            ✅ Conectado a Pusher
                        </span>
                    `;
                    console.log('✅ Pusher conectado correctamente!');
                });

                pusher.connection.bind('disconnected', function() {
                    statusDiv.innerHTML = `
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            ❌ Desconectado
                        </span>
                    `;
                });

                pusher.connection.bind('error', function(err) {
                    statusDiv.innerHTML = `
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            ❌ Error de conexión
                        </span>
                    `;
                    console.error('❌ Error de Pusher:', err);
                });

                // Escuchar notificaciones en tiempo real
                window.Echo.private('user.{{ auth()->id() }}')
                    .listen('.notification.new', (data) => {
                        console.log('🔔 Nueva notificación recibida:', data);

                        // Mostrar notificación del navegador
                        if ('Notification' in window && Notification.permission === 'granted') {
                            new Notification(data.title, {
                                body: data.message,
                                icon: '/favicon.ico',
                            });
                        }
                    });

                console.log('🎧 Escuchando notificaciones en canal: user.{{ auth()->id() }}');
            } else {
                statusDiv.innerHTML = `
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        ❌ Echo no inicializado
                    </span>
                `;
                console.error('❌ Echo no está disponible');
            }

            // Solicitar permisos para notificaciones del navegador
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        });
    </script>
</div>
