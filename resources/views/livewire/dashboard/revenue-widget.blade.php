<div wire:poll.visible.30s class="h-full flex flex-col
                                   bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800
                                   rounded-2xl shadow-sm overflow-hidden">
  {{-- Header --}}
  <div class="px-4 sm:px-5 py-3 border-b border-neutral-200 dark:border-neutral-800">
    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Últimos {{ $days }} días</h3>
  </div>

  {{-- KPIs Compactos --}}
  <div class="px-4 sm:px-5 pt-4 pb-2 grid grid-cols-3 gap-3">
    <div class="text-center">
      <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mb-1">Ingresos</div>
      <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
        ${{ number_format($revenue, 0, ',', '.') }}
      </div>
    </div>
    <div class="text-center">
      <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mb-1">Costos</div>
      <div class="text-lg font-bold text-rose-600 dark:text-rose-400 tabular-nums">
        ${{ number_format($cost, 0, ',', '.') }}
      </div>
    </div>
    <div class="text-center">
      <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mb-1">Ganancia</div>
      <div class="text-lg font-bold tabular-nums {{ $profit >= 0 ? 'text-neutral-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
        ${{ number_format($profit, 0, ',', '.') }}
      </div>
    </div>
  </div>

  {{-- Chart - ocupa el espacio restante --}}
  <div class="flex-1 px-4 sm:px-5 pb-3 flex flex-col" style="min-height: 140px;">
    {{-- Área del gráfico con líneas de referencia --}}
    <div class="flex-1 relative" style="min-height: 100px;">
      {{-- Líneas de referencia horizontales --}}
      <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
        @for($i = 0; $i < 4; $i++)
          <div class="flex items-center">
            <span class="text-[9px] text-neutral-400 dark:text-neutral-500 w-10 text-right pr-1.5">
              @if($i === 0)
                ${{ number_format($maxVal, 0, ',', '.') }}
              @elseif($i === 3)
                $0
              @endif
            </span>
            <div class="flex-1 border-t border-neutral-200 dark:border-neutral-700 opacity-30"></div>
          </div>
        @endfor
      </div>

      {{-- Barras del gráfico --}}
      <div class="absolute inset-0 pl-11 pr-0.5 pt-2 pb-5 flex items-end gap-0.5">
        @for($i=0;$i<$days;$i++)
          @php
            $rev = $series['revenue'][$i] ?? 0;
            $cst = $series['cost'][$i] ?? 0;
            $date = \Carbon\Carbon::parse($labels[$i]);
            $rh  = $maxVal > 0 ? max(4, round(($rev / $maxVal) * 100)) : 0;
            $ch  = $maxVal > 0 ? max(4, round(($cst / $maxVal) * 100)) : 0;
          @endphp
          <div class="flex-1 flex gap-0.5 items-end justify-center group relative h-full">
            {{-- Tooltip on hover --}}
            <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 hidden group-hover:block z-10 pointer-events-none">
              <div class="bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 text-xs rounded-lg px-2 py-1.5 shadow-lg whitespace-nowrap">
                <div class="font-semibold">{{ $date->format('d/m/Y') }}</div>
                <div class="text-emerald-300 dark:text-emerald-600">Ing: ${{ number_format($rev,0,',','.') }}</div>
                <div class="text-rose-300 dark:text-rose-600">Cos: ${{ number_format($cst,0,',','.') }}</div>
                <div class="border-t border-neutral-700 dark:border-neutral-300 mt-1 pt-1">
                  Gan: ${{ number_format($rev - $cst,0,',','.') }}
                </div>
              </div>
            </div>

            {{-- Barra de ingresos --}}
            @if($rev > 0)
              <div class="bg-emerald-500 dark:bg-emerald-400 rounded-t group-hover:bg-emerald-600 dark:group-hover:bg-emerald-500 transition-all"
                   style="height: {{ $rh }}%; width: 45%;">
              </div>
            @endif

            {{-- Barra de costos --}}
            @if($cst > 0)
              <div class="bg-rose-500 dark:bg-rose-400 rounded-t group-hover:bg-rose-600 dark:group-hover:bg-rose-500 transition-all"
                   style="height: {{ $ch }}%; width: 45%;">
              </div>
            @endif
          </div>
        @endfor
      </div>
    </div>

    {{-- Eje X con fechas --}}
    <div class="flex items-center pl-9 pt-1">
      <div class="flex-1 flex justify-between text-[9px] text-neutral-500 dark:text-neutral-400">
        @php
          $showEvery = $days > 14 ? 7 : ($days > 7 ? 3 : 1);
        @endphp
        @for($i=0;$i<$days;$i++)
          @if($i % $showEvery === 0 || $i === $days - 1)
            <span>{{ \Carbon\Carbon::parse($labels[$i])->format('d/m') }}</span>
          @else
            <span class="opacity-0">·</span>
          @endif
        @endfor
      </div>
    </div>

    {{-- Leyenda --}}
    <div class="flex items-center justify-center gap-4 mt-2 text-xs">
      <div class="flex items-center gap-1.5">
        <div class="w-2 h-3 rounded-sm bg-emerald-500 dark:bg-emerald-400"></div>
        <span class="text-neutral-600 dark:text-neutral-400">Ingresos</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-2 h-3 rounded-sm bg-rose-500 dark:bg-rose-400"></div>
        <span class="text-neutral-600 dark:text-neutral-400">Costos</span>
      </div>
    </div>
  </div>
</div>
