import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo y Pusher para broadcasting en tiempo real
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY || 'a58d27031ee6993506cc';
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER || 'sa1';
const pusherHost = import.meta.env.VITE_PUSHER_HOST || null;
const pusherPort = import.meta.env.VITE_PUSHER_PORT || null;
const pusherEnabled = (import.meta.env.VITE_PUSHER_ENABLED ?? 'true') !== 'false';

window.Echo = null;
if (pusherEnabled && pusherKey) {
    try {
        window.Pusher = Pusher;
        window.Pusher.logToConsole = false;

        const echoConfig = {
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: pusherCluster,
            forceTLS: true,
            encrypted: true,
            enabledTransports: ['wss'], // evita ws inseguro en prod y reduce fallos
            disableStats: true, // evita health-check extra que genera warnings
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            },
        };

        // Permitir host/puerto custom si se setean en .env
        if (pusherHost) {
            echoConfig.wsHost = pusherHost;
            echoConfig.wssHost = pusherHost;
        }
        if (pusherPort) {
            echoConfig.wsPort = pusherPort;
            echoConfig.wssPort = pusherPort;
        }

        window.Echo = new Echo(echoConfig);

        // Si la conexión falla, desconectar y evitar spam de errores en consola
        const connection = window.Echo.connector?.pusher?.connection;
        if (connection) {
            let warned = false;
            connection.bind('error', (err) => {
                if (warned) return;
                warned = true;
                console.warn('Tiempo real deshabilitado: no se pudo conectar a Pusher.', err?.error?.message || err);
                try { window.Echo.disconnect(); } catch (_) {}
            });
        }
    } catch (error) {
        console.warn('No se pudo inicializar Pusher/Echo. Continuando sin tiempo real.', error);
        window.Echo = null;
    }
}
