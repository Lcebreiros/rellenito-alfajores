<div wire:poll.visible.60s class="h-full flex flex-col rounded-2xl overflow-hidden
     bg-violet-50/50 dark:bg-neutral-900/65 backdrop-blur-sm
     shadow-[0_4px_20px_-2px_rgba(109,40,217,0.07),0_1px_6px_-1px_rgba(109,40,217,0.03)]
     dark:shadow-[0_4px_20px_-2px_rgba(0,0,0,0.45),0_1px_6px_-1px_rgba(0,0,0,0.25)]">

  {{-- Header --}}
  <div class="px-4 sm:px-5 py-3.5 flex items-center justify-between flex-shrink-0
              border-b border-neutral-100 dark:border-neutral-800/60">
    <div class="flex items-center gap-2">
      <div class="w-1.5 h-4 rounded-full bg-rose-500/80 dark:bg-rose-400/70"></div>
      <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Gastos totales</h3>
    </div>
    <a href="{{ route('expenses.index') }}"
       class="text-[11px] font-medium text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
      Ver detalle →
    </a>
  </div>

  {{-- Content --}}
  <div class="flex-1 flex flex-col justify-between p-4 sm:p-5">
    {{-- Total principal --}}
    <div class="mb-5">
      <div class="text-[10px] uppercase tracking-wide text-neutral-400 dark:text-neutral-500 mb-1">Total anualizado</div>
      <div class="text-3xl font-bold text-neutral-900 dark:text-white tabular-nums">
        ${{ number_format($total, 0, ',', '.') }}
      </div>
      <div class="text-[11px] text-neutral-400 dark:text-neutral-500 mt-1">
        {{ $totalItems }} gastos activos
      </div>
    </div>

    {{-- Barra de distribución --}}
    <div class="space-y-3">
      {{-- Barra apilada --}}
      <div class="w-full h-2.5 bg-neutral-100 dark:bg-neutral-800/60 rounded-full overflow-hidden flex gap-px">
        @foreach($distribution as $item)
          @if($item['percent'] > 0)
            <div class="{{ $item['color'] }} h-full transition-all first:rounded-l-full last:rounded-r-full"
                 style="width: {{ $item['percent'] }}%"
                 title="{{ $item['name'] }}: ${{ number_format($item['value'], 0, ',', '.') }} ({{ number_format($item['percent'], 1) }}%)">
            </div>
          @endif
        @endforeach
      </div>

      {{-- Leyenda --}}
      <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-[11px]">
        @foreach(array_slice($distribution, 0, 4) as $item)
          @if($item['value'] > 0)
            <div class="flex items-center gap-1.5">
              <div class="w-2 h-2 rounded-full {{ $item['color'] }} shrink-0"></div>
              <span class="text-neutral-500 dark:text-neutral-400 truncate">{{ $item['name'] }}</span>
            </div>
          @endif
        @endforeach
      </div>
    </div>
  </div>
</div>
