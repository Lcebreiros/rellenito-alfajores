<div wire:poll.30s class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl shadow-sm overflow-hidden">
  <div class="px-4 sm:px-5 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Ingresos y ganancias ({{ $days }} días)</h3>
  </div>
  <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
    <div class="min-w-0">
      <div class="text-[11px] text-neutral-500 dark:text-neutral-400">Ingresos</div>
      <div class="text-2xl sm:text-2xl font-bold text-neutral-900 dark:text-white tabular-nums whitespace-nowrap">${{ number_format($revenue,2,',','.') }}</div>
    </div>
    <div class="min-w-0">
      <div class="text-[11px] text-neutral-500 dark:text-neutral-400">Costos</div>
      <div class="text-2xl sm:text-2xl font-bold text-rose-600 dark:text-rose-400 tabular-nums whitespace-nowrap">${{ number_format($cost,2,',','.') }}</div>
    </div>
    <div class="min-w-0">
      <div class="text-[11px] text-neutral-500 dark:text-neutral-400">Ganancia bruta</div>
      <div class="text-2xl sm:text-2xl font-bold {{ $profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} tabular-nums whitespace-nowrap">${{ number_format($profit,2,',','.') }}</div>
    </div>
  </div>

  {{-- Chart simple (barras por día) --}}
  <div class="px-4 sm:px-5 pb-5">
    {{-- Usamos grid con repeat dinámico por style para evitar clases dinámicas de Tailwind --}}
    <div class="h-36 sm:h-40 grid gap-1 items-end" style="grid-template-columns: repeat({{ (int)$days }}, minmax(0,1fr));">
      @for($i=0;$i<$days;$i++)
        @php
          $rev = $series['revenue'][$i] ?? 0; $cst = $series['cost'][$i] ?? 0;
          $rh  = $maxVal > 0 ? max(2, round(($rev / $maxVal) * 100)) : 0;
          $ch  = $maxVal > 0 ? max(2, round(($cst / $maxVal) * 100)) : 0;
        @endphp
        <div class="flex flex-col gap-1 items-stretch">
          <div class="w-full bg-emerald-500/70 dark:bg-emerald-400/80 rounded-t-md" style="height: {{ $rh }}%" title="Ingresos: ${{ number_format($rev,2,',','.') }}"></div>
          <div class="w-full bg-rose-500/70 dark:bg-rose-400/80 rounded-b-md" style="height: {{ $ch }}%" title="Costos: ${{ number_format($cst,2,',','.') }}"></div>
        </div>
      @endfor
    </div>
    <div class="mt-2 text-[10px] text-neutral-500 dark:text-neutral-400">Últimos {{ $days }} días</div>
  </div>
</div>
