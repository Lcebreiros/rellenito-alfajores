{{-- Container para toasts que se apilan en la esquina superior derecha --}}
<div x-data="toastManager()"
     @toast.window="addToast($event.detail)"
     class="fixed top-4 right-4 z-50 flex flex-col gap-3 pointer-events-none"
     style="max-width: calc(100vw - 2rem);">

    <template x-for="(toast, index) in toasts" :key="toast.id">
        <div x-data="{
                show: false,
                init() {
                    this.$nextTick(() => {
                        this.show = true;
                        setTimeout(() => {
                            this.show = false;
                            setTimeout(() => $dispatch('remove-toast', { id: toast.id }), 200);
                        }, toast.duration || 5000);
                    });
                }
             }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-full"
             class="max-w-sm w-full pointer-events-auto"
             style="display: none;">

            <div class="flex items-start gap-3 p-4 rounded-lg border shadow-lg"
                 :class="{
                     'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800': toast.type === 'success',
                     'bg-rose-50 dark:bg-rose-900/20 border-rose-200 dark:border-rose-800': toast.type === 'error',
                     'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800': toast.type === 'warning',
                     'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800': toast.type === 'info',
                 }">

                {{-- Ícono --}}
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full"
                         :class="{
                             'text-emerald-600 dark:text-emerald-400': toast.type === 'success',
                             'text-rose-600 dark:text-rose-400': toast.type === 'error',
                             'text-yellow-600 dark:text-yellow-400': toast.type === 'warning',
                             'text-indigo-600 dark:text-indigo-400': toast.type === 'info',
                         }">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <template x-if="toast.type === 'success'">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </template>
                            <template x-if="toast.type === 'error'">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </template>
                            <template x-if="toast.type === 'warning'">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </template>
                            <template x-if="toast.type === 'info'">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                            </template>
                        </svg>
                    </div>
                </div>

                {{-- Contenido --}}
                <div class="flex-1 min-w-0">
                    <p x-show="toast.title" x-text="toast.title"
                       class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-1"></p>
                    <p x-show="toast.message" x-text="toast.message"
                       class="text-sm text-neutral-700 dark:text-neutral-300"></p>
                </div>

                {{-- Botón cerrar --}}
                <button @click="show = false"
                        class="flex-shrink-0 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>

<script>
function toastManager() {
    return {
        toasts: [],
        addToast(toast) {
            const id = Date.now();
            this.toasts.push({ id, ...toast });
        },
        init() {
            this.$watch('$data', () => {
                window.addEventListener('remove-toast', (e) => {
                    this.toasts = this.toasts.filter(t => t.id !== e.detail.id);
                });
            });
        }
    };
}

// Función helper global para mostrar toasts
window.showToast = function(type, message, title = '', duration = 5000) {
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { type, message, title, duration }
    }));
};
</script>
