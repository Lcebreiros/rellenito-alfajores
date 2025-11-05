@php
  $user = auth()->user();
  // Usar la tabla user_notifications para notificaciones en tiempo real
  $unread = \App\Models\UserNotification::forUser($user?->id)->unread()->count();
  $latest = \App\Models\UserNotification::forUser($user?->id)->latest()->take(10)->get();
@endphp

<div x-data="{
  open: false,
  unreadCount: {{ $unread }},
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
      window.location.reload();
    });
  }
}"
@notification-received.window="unreadCount++"
class="relative">
  <button @click="open = !open" @keydown.escape.window="open=false"
          class="relative inline-flex items-center justify-center w-10 h-10 rounded-full border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
    <svg class="w-5 h-5 text-neutral-700 dark:text-neutral-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
      <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M9 17a3 3 0 0 0 6 0" stroke-linecap="round"/>
    </svg>
    <template x-if="unreadCount > 0">
      <span class="absolute top-0 right-0 translate-x-1/3 -translate-y-1/3 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[10px] flex items-center justify-center ring-2 ring-white dark:ring-neutral-900"
            x-text="unreadCount > 99 ? '99+' : unreadCount">
      </span>
    </template>
    <span class="sr-only">Notificaciones</span>
  </button>

  <div x-cloak x-show="open" @click.outside="open=false"
       class="absolute right-0 mt-2 w-80 max-w-[85vw] rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-lg z-50 overflow-hidden">
    <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between bg-neutral-50 dark:bg-neutral-900/60">
      <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Notificaciones</div>
      <span class="text-xs text-neutral-600 dark:text-neutral-400" x-text="unreadCount + ' nuevas'"></span>
    </div>
    <div class="max-h-96 overflow-auto divide-y divide-neutral-100 dark:divide-neutral-800">
      @forelse($latest as $n)
        <div class="px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
          <div class="flex items-start gap-3">
            <div class="mt-0.5">
              <span class="inline-block w-2 h-2 rounded-full {{ $n->is_read ? 'bg-neutral-300 dark:bg-neutral-700' : 'bg-indigo-500' }}"></span>
            </div>
            <div class="min-w-0 w-full">
              {{-- Notificaciones nuevas de user_notifications --}}
              @if($n->type === 'order')
                <a href="{{ $n->data['url'] ?? route('orders.show', $n->data['order_id'] ?? 0) }}"
                   @click.prevent="markAsRead('{{ $n->id }}'); setTimeout(() => window.location.href = '{{ $n->data['url'] ?? '#' }}', 100)"
                   class="block">
                  <div class="flex items-start gap-2">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                      <i class="fas fa-shopping-bag text-blue-600 dark:text-blue-400 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                        {{ $n->title }}
                      </div>
                      @if($n->message)
                        <div class="text-xs text-neutral-600 dark:text-neutral-300">
                          {{ $n->message }}
                        </div>
                      @endif
                    </div>
                  </div>
                </a>

              @elseif($n->type === 'chat')
                <a href="{{ $n->data['url'] ?? route('support.chat', $n->data['chat_id'] ?? 0) }}"
                   @click.prevent="markAsRead('{{ $n->id }}'); setTimeout(() => window.location.href = '{{ $n->data['url'] ?? '#' }}', 100)"
                   class="block">
                  <div class="flex items-start gap-2">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                      <i class="fas fa-message text-green-600 dark:text-green-400 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                        {{ $n->title }}
                      </div>
                      @if($n->message)
                        <div class="text-xs text-neutral-600 dark:text-neutral-300 line-clamp-2">
                          {{ $n->message }}
                        </div>
                      @endif
                    </div>
                  </div>
                </a>

              @elseif($n->type === 'test')
                <div class="block">
                  <div class="flex items-start gap-2">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                      <i class="fas fa-flask text-purple-600 dark:text-purple-400 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                        {{ $n->title }}
                      </div>
                      @if($n->message)
                        <div class="text-xs text-neutral-600 dark:text-neutral-300">
                          {{ $n->message }}
                        </div>
                      @endif
                    </div>
                  </div>
                </div>

              @else
                {{-- Notificación genérica --}}
                <div class="block">
                  <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                    {{ $n->title }}
                  </div>
                  @if($n->message)
                    <div class="text-xs text-neutral-600 dark:text-neutral-300">
                      {{ $n->message }}
                    </div>
                  @endif
                </div>
              @endif

              <div class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">
                {{ $n->created_at?->diffForHumans() }}
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="px-4 py-6 text-center text-sm text-neutral-600 dark:text-neutral-300">Sin notificaciones</div>
      @endforelse
    </div>
    @if($latest->isNotEmpty())
      <div class="px-4 py-2 border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/60 text-right">
        <a href="{{ route('support.index') }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Ver todas</a>
      </div>
    @endif
  </div>

  {{-- Script para escuchar notificaciones en tiempo real --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (window.Echo) {
        // Escuchar notificaciones en tiempo real
        window.Echo.private('user.{{ auth()->id() }}')
          .listen('.notification.new', (data) => {
            console.log('🔔 Nueva notificación:', data);

            // Incrementar contador
            window.dispatchEvent(new CustomEvent('notification-received'));

            // Mostrar notificación del navegador
            if ('Notification' in window && Notification.permission === 'granted') {
              new Notification(data.title, {
                body: data.message,
                icon: '/favicon.ico',
              });
            }

            // Opcional: Recargar la lista después de unos segundos
            setTimeout(() => {
              window.location.reload();
            }, 2000);
          });
      }
    });
  </script>
</div>
