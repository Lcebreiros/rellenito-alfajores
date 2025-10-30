@php
  $user = auth()->user();
  $unread = $user?->unreadNotifications()->count() ?? 0;
  $latest = $user ? $user->notifications()->latest()->take(10)->get() : collect();
@endphp

<div x-data="{
  open: false,
  markAsRead(notificationId) {
    if (!notificationId) return;

    fetch(`/notifications/${notificationId}/mark-as-read`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }).then(() => {
      // Recargar la página para actualizar el contador
      window.location.reload();
    });
  }
}" class="relative">
  <button @click="open = !open" @keydown.escape.window="open=false"
          class="relative inline-flex items-center justify-center w-10 h-10 rounded-full border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
    <svg class="w-5 h-5 text-neutral-700 dark:text-neutral-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
      <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M9 17a3 3 0 0 0 6 0" stroke-linecap="round"/>
    </svg>
    @if($unread > 0)
      <span class="absolute top-0 right-0 translate-x-1/3 -translate-y-1/3 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[10px] flex items-center justify-center ring-2 ring-white dark:ring-neutral-900">
        {{ $unread > 99 ? '99+' : $unread }}
      </span>
    @endif
    <span class="sr-only">Notificaciones</span>
  </button>

  <div x-cloak x-show="open" @click.outside="open=false"
       class="absolute right-0 mt-2 w-80 max-w-[85vw] rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-lg z-50 overflow-hidden">
    <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between bg-neutral-50 dark:bg-neutral-900/60">
      <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Notificaciones</div>
      <span class="text-xs text-neutral-600 dark:text-neutral-400">{{ $unread }} nuevas</span>
    </div>
    <div class="max-h-96 overflow-auto divide-y divide-neutral-100 dark:divide-neutral-800">
      @forelse($latest as $n)
        @php $data = $n->data ?? []; @endphp
        <div class="px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
          <div class="flex items-start gap-3">
            <div class="mt-0.5">
              <span class="inline-block w-2 h-2 rounded-full {{ $n->read_at ? 'bg-neutral-300 dark:bg-neutral-700' : 'bg-indigo-500' }}"></span>
            </div>
            <div class="min-w-0 w-full">
              @php $ntype = $data['type'] ?? $n->type; @endphp
              @if($ntype === 'order_scheduled_today' || $n->type === \App\Notifications\ScheduledOrderDueToday::class)
                <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100 truncate">
                  Pedido agendado para hoy
                </div>
                <div class="text-xs text-neutral-600 dark:text-neutral-300">
                  {{ $data['message'] ?? ('¿Realizaste el pedido #' . ($data['order_number'] ?? $data['order_id'] ?? '')) }}
                </div>
                <div class="mt-2 flex items-center gap-2">
                  <form method="POST" action="{{ $data['confirm_route'] ?? '#' }}">
                    @csrf
                    <button class="px-2.5 py-1.5 text-xs rounded bg-emerald-600 text-white hover:bg-emerald-700">Confirmar</button>
                  </form>
                  <form method="POST" action="{{ $data['cancel_route'] ?? '#' }}">
                    @csrf
                    <button class="px-2.5 py-1.5 text-xs rounded border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-700/50">Cancelar</button>
                  </form>
                  <a href="{{ $data['url'] ?? '#' }}" class="ml-auto text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline">Ver</a>
                </div>
              @elseif($ntype === 'low_stock' || $n->type === \App\Notifications\LowStockAlert::class)
                <a href="{{ $data['url'] ?? '#' }}"
                   @click.prevent="markAsRead('{{ $n->id }}'); setTimeout(() => window.location.href = '{{ $data['url'] ?? '#' }}', 100)"
                   class="block">
                  <div class="flex items-start gap-2">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                      <i class="fas fa-triangle-exclamation text-amber-600 dark:text-amber-400 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                        Stock Bajo
                      </div>
                      <div class="text-xs text-neutral-600 dark:text-neutral-300 truncate">
                        {{ $data['product_name'] ?? 'Producto' }}
                      </div>
                      <div class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                        {{ $data['current_stock'] ?? 0 }} unidades (umbral: {{ $data['threshold'] ?? 5 }})
                      </div>
                    </div>
                  </div>
                </a>
                <div class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">{{ $n->created_at?->diffForHumans() }}</div>
              @elseif($ntype === 'out_of_stock' || $n->type === \App\Notifications\OutOfStockAlert::class)
                <a href="{{ $data['url'] ?? '#' }}"
                   @click.prevent="markAsRead('{{ $n->id }}'); setTimeout(() => window.location.href = '{{ $data['url'] ?? '#' }}', 100)"
                   class="block">
                  <div class="flex items-start gap-2">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                      <i class="fas fa-circle-xmark text-rose-600 dark:text-rose-400 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                        Sin Stock
                      </div>
                      <div class="text-xs text-neutral-600 dark:text-neutral-300 truncate">
                        {{ $data['product_name'] ?? 'Producto' }}
                      </div>
                      <div class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                        0 unidades disponibles
                      </div>
                    </div>
                  </div>
                </a>
                <div class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">{{ $n->created_at?->diffForHumans() }}</div>
              @else
                <a href="{{ $data['url'] ?? '#' }}"
                   @click.prevent="markAsRead('{{ $n->id }}'); setTimeout(() => window.location.href = '{{ $data['url'] ?? '#' }}', 100)"
                   class="block">
                  <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100 truncate">
                    @switch($ntype)
                      @case('support_replied')
                        Nueva respuesta en soporte
                        @break
                      @case('support_status_changed')
                        Estado de reclamo actualizado
                        @break
                      @default
                        Notificación
                    @endswitch
                  </div>
                  @if(!empty($data['subject']))
                    <div class="text-xs text-neutral-600 dark:text-neutral-300 truncate">{{ $data['subject'] }}</div>
                  @endif
                </a>
                <div class="mt-0.5 text-[11px] text-neutral-500 dark:text-neutral-400">{{ $n->created_at?->diffForHumans() }}</div>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="px-4 py-6 text-center text-sm text-neutral-600 dark:text-neutral-300">Sin notificaciones</div>
      @endforelse
    </div>
    <div class="px-4 py-2 border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/60 text-right">
      <a href="{{ route('support.index') }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Ver soporte</a>
    </div>
  </div>
</div>
