<div wire:poll.30s class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl shadow-sm overflow-hidden">
  <div class="px-4 sm:px-5 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Más pedidos ({{ $days }} días)</h3>
  </div>
  <div class="p-4 sm:p-5 space-y-2">
    @forelse($rows as $r)
      <div class="flex items-center gap-3">
        <div class="flex-1 min-w-0">
          <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">{{ $r->name ?? 'Producto' }}</div>
        </div>
        <div class="shrink-0 text-sm font-semibold tabular-nums text-neutral-900 dark:text-neutral-100">x{{ (int)$r->qty }}</div>
      </div>
    @empty
      <div class="text-sm text-neutral-500 dark:text-neutral-400">Sin datos.</div>
    @endforelse
  </div>
</div>

