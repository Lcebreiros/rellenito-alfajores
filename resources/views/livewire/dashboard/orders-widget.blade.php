@php
  $title    = $title ?? 'Ventas';
  $hasDelta = isset($delta);
  $isUp     = $hasDelta && $delta > 0;
  $isDown   = $hasDelta && $delta < 0;
  $deltaAbs = $hasDelta ? number_format(abs($delta), 1, ',', '.') : null;
  $minWidthPx = $minWidthPx ?? null;
@endphp

<div wire:poll.visible.15s aria-live="polite"
     @style($minWidthPx ? "min-width: {$minWidthPx}px" : '')
     class="h-full flex flex-col justify-center rounded-2xl overflow-hidden
            bg-white/85 dark:bg-neutral-900/60 backdrop-blur-sm
            shadow-[0_4px_20px_-2px_rgba(0,0,0,0.08),0_1px_6px_-1px_rgba(0,0,0,0.04)]
            dark:shadow-[0_4px_20px_-2px_rgba(0,0,0,0.45),0_1px_6px_-1px_rgba(0,0,0,0.25)]
            ring-1 ring-black/[0.04] dark:ring-white/[0.05]
            px-5 py-5">

  {{-- Label --}}
  <div class="text-[10px] uppercase tracking-widest font-semibold text-neutral-400 dark:text-neutral-500">
    {{ $title }}
  </div>

  {{-- Valor principal --}}
  <div class="mt-1.5 font-extrabold tracking-tight tabular-nums
              text-neutral-900 dark:text-white leading-none
              [font-size:clamp(1.6rem,5.5vw,2.25rem)]">
    {{ number_format($total, 0, ',', '.') }}
  </div>

  {{-- Delta --}}
  @if($hasDelta)
    <div class="mt-2.5 flex items-center gap-2 flex-wrap">
      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold shrink-0
                   {{ $isUp   ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/25'
                    : ($isDown ? 'text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-900/25'
                               : 'text-neutral-600 dark:text-neutral-300 bg-neutral-100/80 dark:bg-neutral-800/50') }}">
        @if($isUp)
          <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M3 12a1 1 0 001.707.707L9 8.414V17a1 1 0 102 0V8.414l4.293 4.293A1 1 0 0017 12l-6-6-6 6z"/>
          </svg>
          +{{ $deltaAbs }}%
        @elseif($isDown)
          <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M17 8a1 1 0 00-1.707-.707L11 10.586V3a1 1 0 10-2 0v7.586L4.707 7.293A1 1 0 003 8l6 6 6-6z"/>
          </svg>
          -{{ $deltaAbs }}%
        @else
          <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M4 9h12v2H4z"/>
          </svg>
          {{ $deltaAbs }}%
        @endif
      </span>
      <span class="text-[11px] text-neutral-400 dark:text-neutral-500">vs. mes anterior</span>
    </div>
  @endif
</div>
