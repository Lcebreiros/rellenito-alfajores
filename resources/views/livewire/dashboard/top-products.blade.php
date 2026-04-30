<div wire:poll.visible.60s class="h-full flex flex-col rounded-2xl overflow-hidden
                                   bg-violet-50/50 dark:bg-neutral-900/65 backdrop-blur-sm
                                   shadow-[0_4px_20px_-2px_rgba(109,40,217,0.07),0_1px_6px_-1px_rgba(109,40,217,0.03)]
                                   dark:shadow-[0_4px_20px_-2px_rgba(0,0,0,0.45),0_1px_6px_-1px_rgba(0,0,0,0.25)]">

  {{-- Header --}}
  <div class="px-4 sm:px-5 py-3.5 flex items-center justify-between flex-shrink-0
              border-b border-neutral-100 dark:border-neutral-800/60">
    <div class="flex items-center gap-2">
      <div class="w-1.5 h-4 rounded-full bg-amber-500/80 dark:bg-amber-400/70"></div>
      <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">{{ __('dashboard.top_products_title') }}</h3>
    </div>
    <span class="text-[11px] font-medium text-neutral-400 dark:text-neutral-500">{{ $days }}d</span>
  </div>

  {{-- List --}}
  <div class="flex-1 p-4 sm:p-5 overflow-y-auto dashboard-widget-scroll space-y-1">
    @forelse($rows as $i => $r)
      @php $pct = $rows->first()?->qty > 0 ? round(($r->qty / $rows->first()->qty) * 100) : 0; @endphp
      <div class="flex items-center gap-3 py-1.5">
        {{-- Rank --}}
        <span class="text-[11px] font-bold tabular-nums w-4 shrink-0 text-center
                     {{ $i === 0 ? 'text-amber-500 dark:text-amber-400' : 'text-neutral-300 dark:text-neutral-600' }}">
          {{ $i + 1 }}
        </span>
        {{-- Name + bar --}}
        <div class="flex-1 min-w-0">
          <div class="text-[13px] font-medium text-neutral-800 dark:text-neutral-100 truncate">
            {{ $r->name ?? 'Producto' }}
          </div>
          <div class="mt-1 h-1 rounded-full bg-neutral-100 dark:bg-neutral-800/60 overflow-hidden">
            <div class="h-full rounded-full transition-all
                        {{ $i === 0 ? 'bg-amber-400 dark:bg-amber-500' : 'bg-neutral-200 dark:bg-neutral-700' }}"
                 style="width: {{ $pct }}%"></div>
          </div>
        </div>
        {{-- Units --}}
        <div class="shrink-0 text-[13px] font-bold tabular-nums text-neutral-700 dark:text-neutral-200">
          ×{{ number_format((int)$r->qty) }}
        </div>
      </div>
    @empty
      <div class="flex flex-col items-center justify-center h-full py-6 text-center">
        <div class="w-10 h-10 rounded-2xl bg-neutral-100 dark:bg-neutral-800/60 flex items-center justify-center mb-3">
          <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
          </svg>
        </div>
        <p class="text-sm text-neutral-400 dark:text-neutral-500">{{ __('dashboard.no_data') }}</p>
      </div>
    @endforelse
  </div>
</div>
