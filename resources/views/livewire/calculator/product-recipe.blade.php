<div class="space-y-6" x-data x-on:scroll-top.window="window.scrollTo({top:0, behavior:'smooth'})">

  {{-- Configurar producto --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 overflow-hidden transition-colors">
    <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 sm:px-6 py-4 rounded-t-xl">
      <h2 class="text-lg font-semibold text-white">Configurar Producto</h2>
    </div>

    <div class="p-4 sm:p-6">
      @if (session('ok'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-3 py-2 text-sm mb-4
                    dark:border-emerald-700/60 dark:bg-emerald-900/25 dark:text-emerald-200 dark:ring-1 dark:ring-emerald-500/10">
          {{ session('ok') }}
        </div>
      @endif

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Producto --}}
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1 block">Producto</label>
          <select wire:model="productId"
                  class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-700
                         bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                         placeholder-gray-400 dark:placeholder-neutral-400
                         focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                         transition-colors [color-scheme:light] dark:[color-scheme:dark]">
            <option value="">— Seleccionar producto —</option>
            @foreach($products as $p)
              <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
            @endforeach
          </select>
          @error('productId') <div class="text-red-600 dark:text-rose-300 text-xs mt-1">{{ $message }}</div> @enderror

          @if($productName)
            <p class="text-xs text-gray-500 dark:text-neutral-400 mt-2">
              Seleccionado: <span class="font-medium text-gray-900 dark:text-neutral-100">{{ $productName }}</span>
            </p>
          @endif
        </div>

        {{-- Rendimiento --}}
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1 block">
            Rendimiento (unidades por batch)
          </label>
          <input type="number" min="1" wire:model.live="yieldUnits" wire:change="recalc"
                 class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-700
                        bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                        placeholder-gray-400 dark:placeholder-neutral-400
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                        transition-colors" />
          <p class="text-xs text-gray-500 dark:text-neutral-400 mt-2">Ajusta la receta en filas para recalcular.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Builder de receta --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 overflow-hidden transition-colors">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-4 sm:px-6 py-4 rounded-t-xl">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-lg font-semibold text-white">Receta</h2>

        {{-- Botón: Agregar ingrediente (sólido) --}}
        <button type="button"
                class="btn-solid btn-solid-blue"
                wire:click="addRow"
                wire:loading.attr="disabled"
                wire:target="addRow">
          <span wire:loading.remove wire:target="addRow">+ Agregar ingrediente</span>
          <span wire:loading wire:target="addRow">Añadiendo…</span>
        </button>
      </div>
    </div>

    <div class="p-4 sm:p-6 space-y-6">
      {{-- Filas --}}
      <div class="space-y-3">
        @foreach($rows as $i => $row)
          <div class="grid grid-cols-12 gap-3 p-4 bg-gray-50 dark:bg-neutral-800/50 rounded-lg border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 transition-colors">

            {{-- Insumo --}}
            <div class="col-span-12 md:col-span-6">
              <select wire:model.live="rows.{{ $i }}.supply_id" wire:change="onSupplyChange({{ $i }})"
                      class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-600
                             bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                             placeholder-gray-400 dark:placeholder-neutral-400
                             focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                             transition-colors">
                <option value="">— Elegir insumo —</option>
                @foreach($supplies as $s)
                  <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                @endforeach
              </select>

              @php
                $supply    = collect($supplies)->firstWhere('id', (int)($row['supply_id'] ?? 0));
                $baseUnit  = $row['base_unit'] ?? ($supply['base_unit'] ?? '');
                $priceBase = (float)($row['cost_base'] ?? ($supply['avg_cost_per_base'] ?? 0));
              @endphp

              @if(($row['supply_id'] ?? null))
                <p class="text-xs text-gray-600 dark:text-neutral-300 mt-1">
                  Base: {{ $baseUnit }} · $/base: {{ number_format($priceBase,4) }}
                </p>
              @endif
            </div>

            {{-- Cantidad --}}
            <div class="col-span-6 md:col-span-3">
              <input type="number" step="0.001" min="0"
                     wire:model.live="rows.{{ $i }}.qty" wire:change="recalc"
                     class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-600
                            bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                            placeholder-gray-400 dark:placeholder-neutral-400
                            focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                            transition-colors text-right"
                     placeholder="Cantidad">
            </div>

            {{-- Unidad --}}
            <div class="col-span-6 md:col-span-2">
              <select wire:model.live="rows.{{ $i }}.unit" wire:change="recalc"
                      class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-600
                             bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                             placeholder-gray-400 dark:placeholder-neutral-400
                             focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                             transition-colors">
                @if(($row['base_unit'] ?? '') === 'g')
                  <option value="g">g</option><option value="kg">kg</option>
                @elseif(($row['base_unit'] ?? '') === 'ml')
                  <option value="ml">ml</option><option value="l">l</option><option value="cm3">cm3</option>
                @elseif(($row['base_unit'] ?? '') === 'u')
                  <option value="u">u</option>
                @else
                  <option value="">—</option>
                @endif
              </select>
            </div>

            {{-- Costo línea --}}
            <div class="col-span-10 md:col-span-1 md:col-start-auto flex items-center md:justify-end">
              <div class="w-full text-right font-semibold text-gray-900 dark:text-neutral-100">
                @php
                  $qtyRaw   = $row['qty'] ?? 0;
                  $unit     = $row['unit'] ?? '';
                  $costBase = (float)($row['cost_base'] ?? 0);
                  $qtyNorm  = is_string($qtyRaw) ? str_replace(',', '.', $qtyRaw) : $qtyRaw;
                  $qty      = is_numeric($qtyNorm) ? (float)$qtyNorm : 0.0;
                  $factor   = match ($unit) { 'kg'=>1000, 'l'=>1000, 'cm3'=>1, 'g','ml','u',''=>1, default=>1 };
                  $lineCost = ($qty * $factor) * $costBase;
                @endphp
                ${{ number_format($lineCost, 2) }}
              </div>
            </div>

            {{-- Eliminar --}}
            <div class="col-span-2 md:col-span-0 flex items-center justify-end">
              <button type="button" wire:click="removeRow({{ $i }})"
                      class="text-red-600 hover:text-red-800 dark:text-rose-300 dark:hover:text-rose-200 p-1">
                ✕
              </button>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Resumen --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div class="rounded-lg p-4 border border-blue-200 dark:border-blue-900/40 bg-blue-50 dark:bg-blue-500/10">
          <div class="text-blue-700 dark:text-blue-300 text-sm font-medium mb-1">Costo por Batch</div>
          <div class="text-2xl font-bold text-blue-900 dark:text-blue-200">${{ number_format($totalBatch,2) }}</div>
        </div>
        <div class="rounded-lg p-4 border border-green-200 dark:border-green-900/40 bg-green-50 dark:bg-green-500/10">
          <div class="text-green-700 dark:text-green-300 text-sm font-medium mb-1">Rendimiento</div>
          <div class="text-2xl font-bold text-green-900 dark:text-green-200">{{ $yieldUnits }} unidades</div>
        </div>
        <div class="rounded-lg p-4 border border-purple-200 dark:border-purple-900/40 bg-purple-50 dark:bg-purple-500/10">
          <div class="text-purple-700 dark:text-purple-300 text-sm font-medium mb-1">Costo por Unidad</div>
          <div class="text-2xl font-bold text-purple-900 dark:text-purple-200">${{ number_format($totalPerUnit,2) }}</div>
        </div>
      </div>

      {{-- Guardar análisis --}}
      <div class="flex flex-col sm:flex-row sm:justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-neutral-700">
        <button type="button"
                class="btn-solid btn-solid-blue"
                wire:click="saveAnalysis"
                wire:loading.attr="disabled"
                wire:target="saveAnalysis">
          <span wire:loading.remove wire:target="saveAnalysis">Guardar análisis</span>
          <span wire:loading wire:target="saveAnalysis">Guardando…</span>
        </button>
      </div>

      @error('rows')
        <p class="text-red-600 dark:text-rose-300 text-sm">{{ $message }}</p>
      @enderror
    </div>
  </div>
</div>

@push('styles')
<style>
  /* Botón sólido (mantiene azul también en modo oscuro) */
  .btn-solid {
    @apply inline-flex items-center justify-center px-6 py-2 rounded-lg font-medium text-white
           transition-colors duration-200 disabled:opacity-60 disabled:cursor-not-allowed;
  }
  .btn-solid-blue {
    @apply bg-blue-500 hover:bg-blue-600 active:bg-blue-700
           dark:bg-blue-600 dark:hover:bg-blue-500 dark:active:bg-blue-700
           dark:ring-1 dark:ring-blue-400/30;
  }
</style>
@endpush
