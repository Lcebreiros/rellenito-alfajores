<div wire:poll.visible.30s class="h-full flex flex-col
                                   bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800
                                   rounded-2xl shadow-sm overflow-hidden">
  <div class="px-4 sm:px-5 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Últimos pedidos</h3>
    <a href="{{ route('orders.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Ver todos</a>
  </div>
  <div class="flex-1 divide-y divide-neutral-200 dark:divide-neutral-800 overflow-y-auto">
    @forelse($orders as $o)
      <div class="px-4 sm:px-5 py-3 flex items-center justify-between gap-3">
        <div class="min-w-0">
          <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">#{{ $o->order_number ?? $o->id }} · {{ optional($o->client)->name ?? 'Sin cliente' }}</div>
          <div class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ $o->created_at?->format('d/m H:i') }}</div>
        </div>
        <div class="shrink-0 text-sm font-semibold tabular-nums text-neutral-900 dark:text-neutral-100">${{ number_format((float)$o->total,2,',','.') }}</div>
      </div>
    @empty
      <div class="px-4 sm:px-5 py-6 text-sm text-neutral-500 dark:text-neutral-400">Sin pedidos recientes.</div>
    @endforelse
  </div>
</div>
