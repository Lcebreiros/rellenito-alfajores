<div wire:poll.visible.60s class="h-full flex flex-col
                                   bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800
                                   rounded-2xl shadow-sm overflow-hidden">
  {{-- Header --}}
  <div class="px-4 sm:px-5 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Gastos Totales</h3>
    <a href="{{ route('expenses.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Ver detalle</a>
  </div>

  {{-- Content --}}
  <div class="flex-1 flex flex-col justify-between p-4 sm:p-5">
    {{-- Total principal --}}
    <div class="mb-4">
      <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mb-1">Total anualizado</div>
      <div class="text-3xl font-bold text-neutral-900 dark:text-white tabular-nums">
        ${{ number_format($total, 0, ',', '.') }}
      </div>
      <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
        {{ $totalItems }} gastos activos
      </div>
    </div>

    {{-- Mini barra de distribución --}}
    <div class="space-y-3">
      <div class="w-full h-3 bg-neutral-100 dark:bg-neutral-800 rounded-full overflow-hidden flex">
        @foreach($distribution as $item)
          @if($item['percent'] > 0)
            <div class="{{ $item['color'] }} h-full transition-all"
                 style="width: {{ $item['percent'] }}%"
                 title="{{ $item['name'] }}: ${{ number_format($item['value'], 0, ',', '.') }} ({{ number_format($item['percent'], 1) }}%)">
            </div>
          @endif
        @endforeach
      </div>

      {{-- Leyenda compacta --}}
      <div class="grid grid-cols-2 gap-2 text-xs">
        @foreach($distribution->take(4) as $item)
          @if($item['value'] > 0)
            <div class="flex items-center gap-1.5">
              <div class="w-2 h-2 rounded-full {{ $item['color'] }}"></div>
              <span class="text-neutral-600 dark:text-neutral-400 truncate">{{ $item['name'] }}</span>
            </div>
          @endif
        @endforeach
      </div>
    </div>
  </div>
</div>
