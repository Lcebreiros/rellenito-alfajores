<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    {{-- Botón de campana --}}
    <button
        @click="open = !open"
        class="relative p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
        aria-label="Notificaciones"
    >
        <x-heroicon-o-bell class="w-6 h-6 text-neutral-700 dark:text-neutral-300" />

        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full min-w-[20px]">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown de notificaciones --}}
    <div
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-neutral-900 rounded-lg shadow-lg border border-neutral-200 dark:border-neutral-800 z-50"
        style="display: none;"
    >
        <div class="p-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
            <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">Notificaciones</h3>

            @if($unreadCount > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-medium"
                >
                    Marcar todas
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div
                    wire:click="markAsRead({{ $notification->id }})"
                    class="p-4 border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer transition-colors"
                >
                    <div class="flex items-start gap-3">
                        {{-- Icono según tipo --}}
                        <div class="shrink-0 mt-1">
                            @if($notification->type === 'order')
                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                    <x-heroicon-o-shopping-bag class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                </div>
                            @elseif($notification->type === 'chat')
                                <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                    <x-heroicon-o-chat-bubble-left class="w-4 h-4 text-green-600 dark:text-green-400" />
                                </div>
                            @elseif($notification->type === 'test')
                                <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                    <x-heroicon-o-beaker class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <x-heroicon-o-bell class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                {{ $notification->title }}
                            </p>
                            @if($notification->message)
                                <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-0.5 line-clamp-2">
                                    {{ $notification->message }}
                                </p>
                            @endif
                            <p class="text-xs text-neutral-500 dark:text-neutral-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <x-heroicon-o-bell-slash class="w-12 h-12 mx-auto text-neutral-300 dark:text-neutral-700 mb-2" />
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        No tienes notificaciones
                    </p>
                </div>
            @endforelse
        </div>

        @if($notifications->isNotEmpty())
            <div class="p-3 border-t border-neutral-200 dark:border-neutral-800 text-center">
                <a
                    href="#"
                    class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-medium"
                >
                    Ver todas las notificaciones
                </a>
            </div>
        @endif
    </div>

    {{-- Script para notificaciones del navegador --}}
    @script
    <script>
        // Solicitar permisos para notificaciones del navegador
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Escuchar evento para mostrar notificación del navegador
        $wire.on('show-browser-notification', (event) => {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(event.title, {
                    body: event.message,
                    icon: '/favicon.ico',
                });
            }
        });
    </script>
    @endscript
</div>
