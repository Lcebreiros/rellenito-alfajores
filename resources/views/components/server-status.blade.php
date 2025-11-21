{{-- Indicador de estado del servidor --}}
<div x-data="serverStatus()" class="inline-flex items-center gap-2 text-xs">
    <span class="relative flex h-2 w-2">
        <span x-show="status === 'online'"
              class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2 w-2"
              :class="{
                  'bg-emerald-500': status === 'online',
                  'bg-yellow-500': status === 'slow',
                  'bg-rose-500': status === 'offline',
              }"></span>
    </span>
    <span class="text-neutral-500 dark:text-neutral-400"
          x-text="{
              'online': 'Sistema operativo',
              'slow': 'Conexión lenta',
              'offline': 'Sin conexión',
          }[status]"></span>
</div>

<script>
function serverStatus() {
    return {
        status: 'online', // 'online', 'slow', 'offline'
        checkInterval: null,
        lastCheck: Date.now(),

        init() {
            this.checkStatus();
            // Check every 30 seconds
            this.checkInterval = setInterval(() => this.checkStatus(), 30000);
        },

        async checkStatus() {
            const startTime = Date.now();

            try {
                const response = await fetch('/api/health', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: AbortSignal.timeout(5000), // 5 second timeout
                });

                const responseTime = Date.now() - startTime;

                if (response.ok) {
                    this.status = responseTime > 1000 ? 'slow' : 'online';
                } else {
                    this.status = 'offline';
                }

                this.lastCheck = Date.now();
            } catch (error) {
                this.status = 'offline';
                console.error('Health check failed:', error);
            }
        },

        destroy() {
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
            }
        }
    };
}
</script>
