<div wire:poll.visible.30s class="h-full flex flex-col rounded-2xl overflow-hidden
     bg-violet-50/50 dark:bg-neutral-900/65 backdrop-blur-sm
     shadow-[0_4px_20px_-2px_rgba(109,40,217,0.07),0_1px_6px_-1px_rgba(109,40,217,0.03)]
     dark:shadow-[0_4px_20px_-2px_rgba(0,0,0,0.45),0_1px_6px_-1px_rgba(0,0,0,0.25)]">

  {{-- Header --}}
  <div class="px-4 sm:px-5 py-3.5 flex-shrink-0
              border-b border-neutral-100 dark:border-neutral-800/60">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="w-1.5 h-4 rounded-full bg-emerald-500/80 dark:bg-emerald-400/70"></div>
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">{{ __('dashboard.revenue_vs_costs_title') }}</h3>
      </div>
      <span class="text-[11px] font-medium text-neutral-400 dark:text-neutral-500">{{ $days }}d</span>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="px-4 sm:px-5 pt-3.5 pb-2 grid grid-cols-3 gap-2 flex-shrink-0">
    <div class="bg-emerald-50/60 dark:bg-emerald-900/10 rounded-xl p-2.5 text-center">
      <div class="text-[10px] uppercase tracking-wide text-emerald-700 dark:text-emerald-400/70 mb-0.5">{{ __('dashboard.revenue_label') }}</div>
      <div class="text-base font-bold text-emerald-600 dark:text-emerald-400 tabular-nums leading-tight">
        ${{ number_format($revenue, 0, ',', '.') }}
      </div>
    </div>
    <div class="bg-rose-50/60 dark:bg-rose-900/10 rounded-xl p-2.5 text-center">
      <div class="text-[10px] uppercase tracking-wide text-rose-700 dark:text-rose-400/70 mb-0.5">{{ __('dashboard.costs_label') }}</div>
      <div class="text-base font-bold text-rose-500 dark:text-rose-400 tabular-nums leading-tight">
        ${{ number_format($cost, 0, ',', '.') }}
      </div>
    </div>
    <div class="bg-neutral-50/70 dark:bg-neutral-800/30 rounded-xl p-2.5 text-center">
      <div class="text-[10px] uppercase tracking-wide text-neutral-400 dark:text-neutral-500 mb-0.5">{{ __('dashboard.profit_label') }}</div>
      <div class="text-base font-bold tabular-nums leading-tight
                  {{ $profit >= 0 ? 'text-neutral-800 dark:text-white' : 'text-rose-500 dark:text-rose-400' }}">
        ${{ number_format($profit, 0, ',', '.') }}
      </div>
    </div>
  </div>

  {{-- Chart --}}
  <div class="flex-1 px-4 sm:px-5 pb-3 flex flex-col" style="min-height: 120px;">
    <div class="flex-1 relative" style="min-height: 90px;">
      {{-- Líneas de referencia --}}
      <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
        @for($i = 0; $i < 4; $i++)
          <div class="flex items-center">
            <span class="text-[9px] text-neutral-300 dark:text-neutral-600 w-10 text-right pr-1.5 tabular-nums">
              @if($i === 0) ${{ number_format($maxVal, 0, ',', '.') }}
              @elseif($i === 3) $0 @endif
            </span>
            <div class="flex-1 border-t border-neutral-100 dark:border-neutral-800/50 opacity-60"></div>
          </div>
        @endfor
      </div>

      {{-- Barras --}}
      <div class="absolute inset-0 pl-11 pr-0.5 pt-2 pb-5 flex items-end gap-0.5">
        @for($i = 0; $i < $days; $i++)
          @php
            $rev  = $series['revenue'][$i] ?? 0;
            $cst  = $series['cost'][$i] ?? 0;
            $date = \Carbon\Carbon::parse($labels[$i]);
            $rh   = $maxVal > 0 ? max(3, round(($rev / $maxVal) * 100)) : 0;
            $ch   = $maxVal > 0 ? max(3, round(($cst / $maxVal) * 100)) : 0;
          @endphp
          <div class="flex-1 flex gap-px items-end justify-center group relative h-full">
            {{-- Tooltip --}}
            <div class="absolute bottom-full mb-1.5 left-1/2 -translate-x-1/2 hidden group-hover:block z-10 pointer-events-none">
              <div class="bg-neutral-900/95 dark:bg-neutral-100 text-white dark:text-neutral-900 text-[10px] rounded-lg px-2 py-1.5 shadow-xl whitespace-nowrap ring-1 ring-black/10">
                <div class="font-semibold text-[11px] mb-0.5">{{ $date->format('d/m') }}</div>
                <div class="text-emerald-300 dark:text-emerald-600">{{ __('dashboard.rev_tooltip_prefix') }}{{ number_format($rev, 0, ',', '.') }}</div>
                <div class="text-rose-300 dark:text-rose-600">{{ __('dashboard.cos_tooltip_prefix') }}{{ number_format($cst, 0, ',', '.') }}</div>
              </div>
            </div>

            @if($rev > 0)
              <div class="bg-emerald-500/80 dark:bg-emerald-500 rounded-t transition-all group-hover:bg-emerald-500 dark:group-hover:bg-emerald-400"
                   style="height: {{ $rh }}%; width: 45%;"></div>
            @endif
            @if($cst > 0)
              <div class="bg-rose-400/70 dark:bg-rose-500 rounded-t transition-all group-hover:bg-rose-500 dark:group-hover:bg-rose-400"
                   style="height: {{ $ch }}%; width: 45%;"></div>
            @endif
          </div>
        @endfor
      </div>
    </div>

    {{-- Eje X --}}
    <div class="flex items-center pl-9 pt-1">
      <div class="flex-1 flex justify-between text-[9px] text-neutral-300 dark:text-neutral-600 tabular-nums">
        @php $showEvery = $days > 14 ? 7 : ($days > 7 ? 3 : 1); @endphp
        @for($i = 0; $i < $days; $i++)
          @if($i % $showEvery === 0 || $i === $days - 1)
            <span>{{ \Carbon\Carbon::parse($labels[$i])->format('d/m') }}</span>
          @else
            <span class="opacity-0">·</span>
          @endif
        @endfor
      </div>
    </div>

    {{-- Leyenda --}}
    <div class="flex items-center justify-center gap-4 mt-1.5 text-[11px]">
      <div class="flex items-center gap-1.5">
        <div class="w-2.5 h-2 rounded-sm bg-emerald-500/80"></div>
        <span class="text-neutral-500 dark:text-neutral-400">{{ __('dashboard.revenue_label') }}</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-2.5 h-2 rounded-sm bg-rose-400/70 dark:bg-rose-500"></div>
        <span class="text-neutral-500 dark:text-neutral-400">{{ __('dashboard.costs_label') }}</span>
      </div>
    </div>
  </div>
</div>
