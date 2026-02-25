@php
  // Props opcionales
  $title    = $title ?? 'Ventas';
  $hasDelta = isset($delta);
  $isUp     = $hasDelta && $delta > 0;
  $isDown   = $hasDelta && $delta < 0;
  $deltaAbs = $hasDelta ? number_format(abs($delta), 1, ',', '.') : null;

  // Si querés forzar un mínimo concreto, pasá $minWidthPx (ej. 160)
  $minWidthPx = $minWidthPx ?? null;
@endphp

<div wire:poll.visible.15s aria-live="polite"
     @style($minWidthPx ? "min-width: {$minWidthPx}px" : '')
     class="h-full flex flex-col justify-center
            bg-white dark:bg-neutral-900 rounded-2xl shadow-sm
            border border-neutral-200 dark:border-neutral-800
            px-4 py-5 overflow-hidden">

  <!-- Título -->
  <div class="text-[11px] uppercase tracking-wide font-medium
              text-neutral-500 dark:text-neutral-400 whitespace-nowrap">
    {{ $title }}
  </div>

  <!-- Valor principal (tamaño fluido) -->
  <div class="mt-1 font-extrabold tracking-tight tabular-nums
              text-neutral-900 dark:text-white leading-none
              [font-size:clamp(1.6rem,5.5vw,2.25rem)]">
    {{ number_format($total, 0, ',', '.') }}
  </div>

  <!-- Delta (opcional) -->
  @if($hasDelta)
    <div class="mt-2 flex items-center gap-2 text-[13px] flex-wrap">
      <span
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold shrink-0
               {{ $isUp ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/20'
                        : ($isDown ? 'text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-900/20'
                                   : 'text-neutral-700 dark:text-neutral-300 bg-neutral-100/70 dark:bg-neutral-800/60') }}">
        @if($isUp)
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M3 12a1 1 0 001.707.707L9 8.414V17a1 1 0 102 0V8.414l4.293 4.293A1 1 0 0017 12l-6-6-6 6z"/>
          </svg>
          +{{ $deltaAbs }}%
        @elseif($isDown)
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M17 8a1 1 0 00-1.707-.707L11 10.586V3a1 1 0 10-2 0v7.586L4.707 7.293A1 1 0 003 8l6 6 6-6z"/>
          </svg>
          -{{ $deltaAbs }}%
        @else
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M4 9h12v2H4z"/>
          </svg>
          {{ $deltaAbs }}%
        @endif
      </span>

      <span class="text-neutral-500 dark:text-neutral-400">
        desde el mes pasado
      </span>
    </div>
  @endif
</div>
