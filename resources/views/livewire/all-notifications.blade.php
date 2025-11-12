{{-- resources/views/livewire/all-notifications.blade.php --}}
<div class="space-y-4">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Barra de acciones --}}
    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4">
        <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
            {{-- Filtros --}}
            <div class="flex items-center gap-2">
                <button
                    wire:click="setFilter('all')"
                    class="px-3 py-2 text-sm rounded-lg transition-colors {{ $filter === 'all' ? 'bg-indigo-600 text-white' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700' }}"
                >
                    Todas
                </button>
                <button
                    wire:click="setFilter('unread')"
                    class="px-3 py-2 text-sm rounded-lg transition-colors {{ $filter === 'unread' ? 'bg-indigo-600 text-white' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700' }}"
                >
                    No leídas ({{ $unreadCount }})
                </button>
                <button
                    wire:click="setFilter('read')"
                    class="px-3 py-2 text-sm rounded-lg transition-colors {{ $filter === 'read' ? 'bg-indigo-600 text-white' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700' }}"
                >
                    Leídas
                </button>
            </div>

            {{-- Acciones --}}
            <div class="flex items-center gap-2 flex-wrap">
                @if (count($selected) > 0)
                    <button
                        wire:click="markSelectedAsRead"
                        class="px-3 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors"
                    >
                        Marcar seleccionadas como leídas
                    </button>
                    <button
                        wire:click="deleteSelected"
                        wire:confirm="¿Está seguro de eliminar las notificaciones seleccionadas?"
                        class="px-3 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors"
                    >
                        Eliminar seleccionadas ({{ count($selected) }})
                    </button>
                @else
                    <button
                        wire:click="markAllAsRead"
                        wire:confirm="¿Marcar todas las notificaciones no leídas como leídas?"
                        class="px-3 py-2 text-sm rounded-lg bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300 transition-colors"
                    >
                        Marcar todas como leídas
                    </button>
                    <button
                        wire:click="deleteAll"
                        wire:confirm="¿Está seguro de eliminar todas las notificaciones?"
                        class="px-3 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors"
                    >
                        Eliminar todas
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Lista de notificaciones --}}
    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
        @if ($notifications->count() > 0)
            {{-- Checkbox seleccionar todas --}}
            <div class="border-b border-neutral-200 dark:border-neutral-800 p-4 bg-neutral-50 dark:bg-neutral-800/50">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="selectAll"
                        class="w-4 h-4 text-indigo-600 rounded border-neutral-300 dark:border-neutral-700 focus:ring-indigo-500 dark:bg-neutral-800"
                    >
                    <span class="text-sm text-neutral-700 dark:text-neutral-300">Seleccionar todas</span>
                </label>
            </div>

            {{-- Notificaciones --}}
            <div class="divide-y divide-neutral-200 dark:divide-neutral-800">
                @foreach ($notifications as $notification)
                    <div
                        class="p-4 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors {{ !$notification->is_read ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}"
                        wire:key="notification-{{ $notification->id }}"
                    >
                        <div class="flex items-start gap-3">
                            {{-- Checkbox --}}
                            <input
                                type="checkbox"
                                wire:click="toggleSelection({{ $notification->id }})"
                                @if(in_array($notification->id, $selected)) checked @endif
                                class="mt-1 w-4 h-4 text-indigo-600 rounded border-neutral-300 dark:border-neutral-700 focus:ring-indigo-500 dark:bg-neutral-800"
                            >

                            {{-- Icono de tipo --}}
                            <div class="flex-shrink-0 mt-1">
                                @if ($notification->type === 'stock_alert')
                                    <div class="w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                @elseif ($notification->type === 'order_scheduled')
                                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Contenido --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                            {{ $notification->title }}
                                            @if (!$notification->is_read)
                                                <span class="inline-block w-2 h-2 rounded-full bg-blue-600 ml-1"></span>
                                            @endif
                                        </h3>
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                            {{ $notification->message }}
                                        </p>
                                        <div class="flex items-center gap-3 mt-2">
                                            <span class="text-xs text-neutral-500 dark:text-neutral-500">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                            @if (!$notification->is_read)
                                                <button
                                                    wire:click="markAsRead({{ $notification->id }})"
                                                    class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                                >
                                                    Marcar como leída
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paginación --}}
            <div class="p-4 border-t border-neutral-200 dark:border-neutral-800">
                {{ $notifications->links() }}
            </div>
        @else
            {{-- Estado vacío --}}
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-neutral-400 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    No hay notificaciones
                </h3>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                    @if ($filter === 'unread')
                        No tienes notificaciones sin leer
                    @elseif ($filter === 'read')
                        No tienes notificaciones leídas
                    @else
                        No tienes ninguna notificación aún
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
