<div wire:poll.visible.15s class="h-full flex flex-col rounded-2xl overflow-hidden
     bg-violet-50/50 dark:bg-neutral-900/65 backdrop-blur-sm
     shadow-[0_4px_20px_-2px_rgba(109,40,217,0.07),0_1px_6px_-1px_rgba(109,40,217,0.03)]
     dark:shadow-[0_4px_20px_-2px_rgba(0,0,0,0.45),0_1px_6px_-1px_rgba(0,0,0,0.25)]">

  {{-- Header --}}
  <div class="px-4 sm:px-5 py-3.5 flex items-center justify-between flex-shrink-0
              border-b border-neutral-100 dark:border-neutral-800/60">
    <div class="flex items-center gap-2.5 min-w-0">
      <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center shrink-0">
        <svg viewBox="0 0 24 24" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor">
          <path d="M21 8.5l-9-5-9 5V17l9 5 9-5V8.5zM12 5.15L18.74 9 12 12.85 5.26 9 12 5.15zM6 10.73l5 2.89v5.65l-5-2.78v-5.76zm12 0v5.76l-5 2.78v-5.65l5-2.89z"/>
        </svg>
      </div>
      <div class="min-w-0">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100 truncate">Stock</h3>
        <p class="text-[11px] text-neutral-400 dark:text-neutral-500 truncate">
          {{ $hasMin ? __('dashboard.stock_vs_min') : __('dashboard.stock_no_min') }}
        </p>
      </div>
    </div>

    @if(!$this->isSmall())
      <div class="hidden sm:flex items-center gap-4">
        <div class="text-right">
          <div class="text-[10px] uppercase tracking-wide text-neutral-400 dark:text-neutral-500">{{ __('dashboard.total_products_label') }}</div>
          <div class="text-sm font-bold text-neutral-800 dark:text-neutral-100 tabular-nums">
            {{ number_format($totals->total_products ?? 0) }}
          </div>
        </div>
        <div class="text-right">
          <div class="text-[10px] uppercase tracking-wide text-neutral-400 dark:text-neutral-500">{{ __('dashboard.low_stock_label') }}</div>
          <div class="text-sm font-bold tabular-nums
                      {{ ($totals->low_count ?? 0) > 0 ? 'text-rose-500 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
            {{ number_format($totals->low_count ?? 0) }}
          </div>
        </div>
      </div>
    @endif
  </div>

  {{-- Body --}}
  <div class="px-4 sm:px-5 py-4 flex-1 overflow-hidden flex flex-col">

    {{-- Compacto --}}
    @if($this->isSmall())
      <div class="flex-1 flex flex-col items-center justify-center gap-1">
        <div class="text-4xl font-extrabold tracking-tight text-neutral-900 dark:text-white tabular-nums">
          {{ number_format($totals->total_units ?? 0) }}
        </div>
        <div class="text-sm text-neutral-400 dark:text-neutral-500">{{ __('dashboard.total_units_label') }}</div>
        @if(($totals->low_count ?? 0) > 0)
          <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                      bg-rose-50 dark:bg-rose-900/20 text-xs font-semibold text-rose-600 dark:text-rose-400">
            <div class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></div>
            {{ $totals->low_count }} {{ __('dashboard.below_min_label') }}
          </div>
        @endif
      </div>
    @endif

    {{-- Medio --}}
    @if($this->isMedium())
      <div class="grid grid-cols-3 gap-2.5 mb-3">
        <div class="col-span-3">
          <div class="text-[10px] uppercase tracking-wide text-neutral-400 dark:text-neutral-500 mb-0.5">{{ __('dashboard.total_units_title') }}</div>
          <div class="text-3xl font-bold text-neutral-900 dark:text-white tabular-nums">
            {{ number_format($totals->total_units ?? 0) }}
          </div>
        </div>
        <div class="bg-neutral-50/70 dark:bg-neutral-800/30 rounded-xl p-2.5">
          <div class="text-[10px] text-neutral-400 dark:text-neutral-500 mb-0.5">{{ __('dashboard.total_products_label') }}</div>
          <div class="text-lg font-bold text-neutral-800 dark:text-white tabular-nums">
            {{ number_format($totals->total_products ?? 0) }}
          </div>
        </div>
        <div class="bg-neutral-50/70 dark:bg-neutral-800/30 rounded-xl p-2.5 col-span-2">
          <div class="text-[10px] text-neutral-400 dark:text-neutral-500 mb-0.5">{{ __('dashboard.with_low_stock') }}</div>
          <div class="text-lg font-bold tabular-nums
                      {{ ($totals->low_count ?? 0) > 0 ? 'text-rose-500 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
            {{ number_format($totals->low_count ?? 0) }}
          </div>
        </div>
      </div>
    @endif

    {{-- Grande: lista con barras --}}
    @if($this->isLarge())
      <div class="mb-3">
        <div class="text-[10px] uppercase tracking-wide text-neutral-400 dark:text-neutral-500 mb-0.5">{{ __('dashboard.total_units_title') }}</div>
        <div class="text-3xl font-bold text-neutral-900 dark:text-white tabular-nums">
          {{ number_format($totals->total_units ?? 0) }}
        </div>
      </div>

      <div class="flex-1 space-y-2.5 overflow-y-auto dashboard-widget-scroll">
        @forelse($items as $p)
          @php
            $min   = $hasMin ? (int)($p->min_stock ?? 0) : 0;
            $isLow = $hasMin ? ((int)$p->stock <= $min) : ((int)$p->stock <= 0);
            $ratio = $hasMin
                ? max(0, min(100, $min > 0 ? round(($p->stock / max(1,$min)) * 100) : 100))
                : ((int)$p->stock > 0 ? 100 : 0);
          @endphp
          <div class="flex items-center gap-3">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1.5 min-w-0 mb-1">
                @if($isLow)
                  <div class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0 animate-pulse"></div>
                @endif
                <span class="truncate text-[13px] font-medium text-neutral-800 dark:text-neutral-100">{{ $p->name }}</span>
              </div>
              <div class="h-1.5 bg-neutral-100 dark:bg-neutral-800/60 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all
                            {{ $isLow ? 'bg-rose-500 dark:bg-rose-400' : 'bg-emerald-500 dark:bg-emerald-400' }}"
                     style="width: {{ $ratio }}%"></div>
              </div>
            </div>
            <div class="text-right w-16 shrink-0">
              <div class="text-sm font-bold tabular-nums
                          {{ $isLow ? 'text-rose-500 dark:text-rose-400' : 'text-neutral-800 dark:text-neutral-100' }}">
                {{ (int)$p->stock }}
              </div>
              @if($hasMin)
                <div class="text-[10px] text-neutral-400 dark:text-neutral-500">{{ __('dashboard.min_prefix') }}{{ (int)$p->min_stock }}</div>
              @endif
            </div>
          </div>
        @empty
          <p class="text-sm text-neutral-400 dark:text-neutral-500">{{ __('dashboard.no_products') }}</p>
        @endforelse
      </div>
    @endif
  </div>
</div>
