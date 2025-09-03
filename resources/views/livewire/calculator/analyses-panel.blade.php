<div class="space-y-6">
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 overflow-hidden transition-colors">

    {{-- Header (se mantiene el gradiente púrpura) --}}
    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-4 sm:px-6 py-4">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <h2 class="text-lg font-semibold text-white">Análisis Guardados</h2>

        <div class="w-full md:w-auto">
          <div class="bg-white/90 dark:bg-white/10 dark:border dark:border-neutral-700 rounded-lg px-2 py-1.5 backdrop-blur-sm">
            <select wire:model="filterProductId"
                    class="w-full md:w-[240px] bg-transparent dark:bg-transparent rounded-md px-2 py-1
                           text-gray-900 dark:text-neutral-100 placeholder-gray-400 dark:placeholder-neutral-400
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400
                           [color-scheme:light] dark:[color-scheme:dark]">
              <option value="">Todos los productos</option>
              @foreach($products as $p)
                <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

    {{-- Body --}}
    <div class="p-4 sm:p-6">
      @if (session('ok'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-3 py-2 text-sm mb-4
                    dark:border-green-700 dark:bg-green-900/20 dark:text-green-200 transition-colors">
          {{ session('ok') }}
        </div>
      @endif

      {{-- ===== Dashboard resumen ===== --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 transition-colors">
          <div class="text-sm text-gray-600 dark:text-neutral-300">Total de análisis</div>
          <div class="text-2xl font-bold text-gray-900 dark:text-neutral-100 mt-1">{{ number_format($totalAnalyses) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 transition-colors">
          <div class="text-sm text-gray-600 dark:text-neutral-300">Producción total</div>
          <div class="text-2xl font-bold text-gray-900 dark:text-neutral-100 mt-1">{{ number_format($totalProduction) }} unidades</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 transition-colors">
          <div class="text-sm text-gray-600 dark:text-neutral-300">Gasto total</div>
          <div class="text-2xl font-bold text-gray-900 dark:text-neutral-100 mt-1">${{ number_format($totalSpend, 2) }}</div>
        </div>
      </div>

      @if (empty($saved))
        <div class="text-center py-12 text-gray-500 dark:text-neutral-400 transition-colors">
          No hay análisis guardados.
        </div>
      @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          @foreach($saved as $s)
            <div class="border border-gray-200 dark:border-neutral-700 rounded-lg p-6 bg-white dark:bg-neutral-900 transition-colors">
              <div class="flex items-start justify-between mb-4 gap-3">
                <div class="min-w-0">
                  <h4 class="font-semibold text-gray-900 dark:text-neutral-100 truncate">
                    {{ $s['product_name'] ?? 'Sin producto' }}
                  </h4>
                  <p class="text-xs text-gray-600 dark:text-neutral-400">
                    {{ \Carbon\Carbon::parse($s['created_at'])->format('d M Y H:i') }}
                  </p>
                </div>
                @php $fromRecipe = ($s['source'] ?? '') === 'recipe'; @endphp
                <span class="px-2 py-0.5 text-xs rounded-full shrink-0
                             {{ $fromRecipe
                                  ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                  : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">
                  {{ $fromRecipe ? 'Receta' : 'Rápido' }}
                </span>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-700 dark:text-neutral-300">Costo por unidad</span>
                  <span class="font-medium text-gray-900 dark:text-neutral-100">${{ number_format($s['unit_total'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-700 dark:text-neutral-300">Costo por batch</span>
                  <span class="font-medium text-gray-900 dark:text-neutral-100">${{ number_format($s['batch_total'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-700 dark:text-neutral-300">Rendimiento</span>
                  <span class="font-medium text-gray-900 dark:text-neutral-100">{{ (int)($s['yield_units'] ?? 0) }} unidades</span>
                </div>
              </div>

              @php $lines = collect($s['lines'] ?? [])->take(3); @endphp
              @if($lines->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-neutral-700">
                  <div class="text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1">Ingredientes principales</div>
                  <div class="space-y-1">
                    @foreach($lines as $ln)
                      <div class="flex justify-between text-xs text-gray-600 dark:text-neutral-400">
                        <span class="truncate mr-2">{{ $ln['name'] ?? '—' }}</span>
                        <span>${{ number_format((float)($ln['per_unit_cost'] ?? 0), 2) }}</span>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endif

              <div class="mt-5 flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 dark:border-neutral-700 pt-4">
                <button wire:click="useSaved({{ (int)($s['id'] ?? 0) }})"
                        class="text-indigo-700 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-200 font-medium text-sm transition-colors">
                  Usar este análisis
                </button>

                <button x-data
                        x-on:click.prevent="confirm('¿Eliminar este análisis?') && $wire.delete({{ (int)($s['id'] ?? 0) }})"
                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium text-sm transition-colors">
                  Eliminar
                </button>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>
