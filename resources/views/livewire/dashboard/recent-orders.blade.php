<div wire:poll.visible.30s class="h-full flex flex-col rounded-2xl overflow-hidden
                                   bg-violet-50/50 dark:bg-neutral-900/65 backdrop-blur-sm
                                   shadow-[0_4px_20px_-2px_rgba(109,40,217,0.07),0_1px_6px_-1px_rgba(109,40,217,0.03)]
                                   dark:shadow-[0_4px_20px_-2px_rgba(0,0,0,0.45),0_1px_6px_-1px_rgba(0,0,0,0.25)]">

  {{-- Header --}}
  <div class="px-4 sm:px-5 py-3.5 flex items-center justify-between flex-shrink-0
              border-b border-neutral-100 dark:border-neutral-800/60">
    <div class="flex items-center gap-2">
      <div class="w-1.5 h-4 rounded-full bg-indigo-500/80 dark:bg-indigo-400/70"></div>
      <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Últimas ventas</h3>
    </div>
    <a href="{{ route('orders.index') }}"
       class="text-[11px] font-medium text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
      Ver todas →
    </a>
  </div>

  {{-- List --}}
  <div class="flex-1 overflow-y-auto dashboard-widget-scroll divide-y divide-neutral-100/80 dark:divide-neutral-800/50">
    @forelse($orders as $o)
      <div class="px-4 sm:px-5 py-2.5 flex items-center justify-between gap-3
                  hover:bg-neutral-50/60 dark:hover:bg-neutral-800/20 transition-colors">
        <div class="min-w-0 flex-1">
          <div class="text-[13px] font-medium text-neutral-800 dark:text-neutral-100 truncate">
            #{{ $o->order_number ?? $o->id }}
            @if(optional($o->client)->name)
              <span class="text-neutral-400 dark:text-neutral-500 font-normal"> · {{ $o->client->name }}</span>
            @endif
          </div>
          <div class="text-[11px] text-neutral-400 dark:text-neutral-500 mt-0.5">
            {{ $o->created_at?->format('d/m H:i') }}
          </div>
        </div>
        <div class="shrink-0 text-sm font-bold tabular-nums text-neutral-800 dark:text-neutral-100">
          ${{ number_format((float)$o->total, 0, ',', '.') }}
        </div>
      </div>
    @empty
      <div class="flex flex-col items-center justify-center h-full py-8 text-center px-4">
        <div class="w-10 h-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800/60 flex items-center justify-center mb-3">
          <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <p class="text-sm text-neutral-400 dark:text-neutral-500">Sin ventas recientes</p>
      </div>
    @endforelse
  </div>
</div>
