<div class="space-y-6" x-data x-on:scroll-top.window="window.scrollTo({top:0, behavior:'smooth'})">

  {{-- Configurar producto --}}
  <div class="bg-white dark:bg-neutral-950 rounded-3xl shadow-xl shadow-slate-200/20 dark:shadow-black/40 ring-1 ring-slate-200/50 dark:ring-neutral-800/60 overflow-hidden backdrop-blur-sm transition-all duration-200">
    <div class="px-6 py-4 bg-gradient-to-r from-emerald-50 to-emerald-100/50 dark:from-emerald-900/80 dark:to-emerald-900/40 border-b border-slate-200/60 dark:border-neutral-800/60">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-neutral-50 flex items-center gap-3">
        <div class="flex-shrink-0 w-2 h-2 bg-gradient-to-r from-emerald-500 to-green-600 rounded-full animate-pulse"></div>
        Configurar Producto
      </h2>
    </div>

    <div class="p-6">
      @if (session('ok'))
        <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-green-50/80 dark:from-emerald-900/40 dark:to-green-900/20 
                    border border-emerald-200/60 dark:border-emerald-800/40 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-1.5 h-1.5 bg-gradient-to-r from-emerald-500 to-green-600 rounded-full"></div>
            <span class="text-sm text-emerald-800 dark:text-emerald-200">{{ session('ok') }}</span>
          </div>
        </div>
      @endif

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Producto --}}
        <div class="space-y-2">
          <label class="block text-xs font-medium text-slate-600 dark:text-neutral-300 mb-2">Producto</label>
          <select wire:model="productId"
                  class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                         px-4 py-3 text-sm text-slate-900 dark:text-neutral-100
                         focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                         transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600">
            <option value="">— Seleccionar producto —</option>
            @foreach($products as $p)
              <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
            @endforeach
          </select>
          @error('productId') 
            <div class="text-rose-600 dark:text-rose-300 text-xs mt-1 flex items-center gap-1">
              <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
              </svg>
              {{ $message }}
            </div> 
          @enderror

          @if($productName)
            <div class="mt-2 p-2 rounded-lg bg-slate-50 dark:bg-neutral-800/50 border border-slate-200/60 dark:border-neutral-800/60">
              <p class="text-xs text-slate-500 dark:text-neutral-400">
                Seleccionado: <span class="font-medium text-slate-900 dark:text-neutral-100">{{ $productName }}</span>
              </p>
            </div>
          @endif
        </div>

        {{-- Rendimiento --}}
        <div class="space-y-2">
          <label class="block text-xs font-medium text-slate-600 dark:text-neutral-300 mb-2">
            Rendimiento (unidades por receta)
          </label>
          <input type="number" min="1" wire:model.live="yieldUnits" wire:change="recalc"
                 class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                        px-4 py-3 text-sm text-slate-900 dark:text-neutral-100
                        focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                        transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600" />
          <p class="text-[11px] text-slate-500 dark:text-neutral-400 mt-1">Ajusta la receta en filas para recalcular.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Builder de receta --}}
  <div class="bg-white dark:bg-neutral-950 rounded-3xl shadow-xl shadow-slate-200/20 dark:shadow-black/40 ring-1 ring-slate-200/50 dark:ring-neutral-800/60 overflow-hidden backdrop-blur-sm transition-all duration-200">
    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50/50 dark:from-indigo-900/80 dark:to-purple-900/40 border-b border-slate-200/60 dark:border-neutral-800/60">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-neutral-50 flex items-center gap-3">
          <div class="flex-shrink-0 w-2 h-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full animate-pulse"></div>
          Receta
        </h2>

        {{-- Botón: Agregar ingrediente --}}
        <button type="button"
                class="group relative rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-300
                       bg-gradient-to-r from-indigo-600 to-purple-600 text-white hover:from-indigo-700 hover:to-purple-700 
                       hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                wire:click="addRow"
                wire:loading.attr="disabled"
                wire:target="addRow">
          <span wire:loading.remove wire:target="addRow" class="flex items-center gap-2">
            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12M6 12h12"/>
            </svg>
            Agregar ingrediente
          </span>
          <span wire:loading wire:target="addRow" class="flex items-center gap-2">
            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"/>
            </svg>
            Añadiendo…
          </span>
        </button>
      </div>
    </div>

    <div class="p-6 space-y-6">
      {{-- Filas --}}
      <div class="space-y-4">
        @foreach($rows as $i => $row)
          <div class="group p-4 rounded-2xl bg-white dark:bg-neutral-800/50 border border-slate-200/60 dark:border-neutral-800/60 
                      hover:shadow-md hover:shadow-slate-200/20 dark:hover:shadow-black/20 transition-all duration-200">
            <div class="grid grid-cols-12 gap-4">

              {{-- Insumo --}}
              <div class="col-span-12 md:col-span-6">
                <select wire:model.live="rows.{{ $i }}.supply_id" wire:change="onSupplyChange({{ $i }})"
                        class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                               px-3 py-2 text-sm text-slate-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                               transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600">
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
                  <div class="mt-2 px-3 py-1 rounded-lg bg-slate-50 dark:bg-neutral-800/50 border border-slate-200/60 dark:border-neutral-800/60">
                    <p class="text-xs text-slate-500 dark:text-neutral-400">
                      Base: <span class="font-medium">{{ $baseUnit }}</span> · 
                      $/base: <span class="font-medium">${{ number_format($priceBase, 4, ',', '.') }}</span>
                    </p>
                  </div>
                @endif
              </div>

              {{-- Cantidad --}}
              <div class="col-span-6 md:col-span-3">
                <input type="number" step="0.001" min="0"
                       wire:model.live="rows.{{ $i }}.qty" wire:change="recalc"
                       class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                              px-3 py-2 text-sm text-slate-900 dark:text-neutral-100 text-right
                              focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                              transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600"
                       placeholder="Cantidad">
              </div>

              {{-- Unidad --}}
              <div class="col-span-6 md:col-span-2">
                <select wire:model.live="rows.{{ $i }}.unit" wire:change="recalc"
                        class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                               px-3 py-2 text-sm text-slate-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                               transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600">
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
              <div class="col-span-10 md:col-span-1 flex items-center justify-end">
                <div class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-neutral-800 border border-slate-200/60 dark:border-neutral-700">
                  <div class="text-sm font-semibold text-slate-900 dark:text-neutral-100">
                    @php
                      $qtyRaw   = $row['qty'] ?? 0;
                      $unit     = $row['unit'] ?? '';
                      $costBase = (float)($row['cost_base'] ?? 0);
                      $qtyNorm  = is_string($qtyRaw) ? str_replace(',', '.', $qtyRaw) : $qtyRaw;
                      $qty      = is_numeric($qtyNorm) ? (float)$qtyNorm : 0.0;
                      $factor   = match ($unit) { 'kg'=>1000, 'l'=>1000, 'cm3'=>1, 'g','ml','u',''=>1, default=>1 };
                      $lineCost = ($qty * $factor) * $costBase;
                    @endphp
                    ${{ number_format($lineCost, 2, ',', '.') }}
                  </div>
                </div>
              </div>

              {{-- Eliminar --}}
              <div class="col-span-2 md:col-span-0 flex items-center justify-end">
                <button type="button" wire:click="removeRow({{ $i }})"
                        class="h-7 w-7 flex items-center justify-center rounded-lg bg-rose-50 hover:bg-rose-100 
                               dark:bg-rose-900/20 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-400 
                               transition-colors duration-200 opacity-60 group-hover:opacity-100"
                        title="Eliminar ingrediente">
                  <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Resumen --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        @php
          // Cálculo dinámico en tiempo real
          $dynamicBatch = 0;
          foreach($rows as $row) {
            if(!empty($row['supply_id']) && !empty($row['qty'])) {
              $qtyRaw = is_string($row['qty']) ? str_replace(',', '.', $row['qty']) : $row['qty'];
              $qty = is_numeric($qtyRaw) ? (float)$qtyRaw : 0;
              $unit = $row['unit'] ?? '';
              $factor = match($unit) { 'kg'=>1000, 'l'=>1000, 'cm3'=>1, default=>1 };
              $costBase = (float)($row['cost_base'] ?? 0);
              $dynamicBatch += ($qty * $factor) * $costBase;
            }
          }
          $dynamicPerUnit = ($yieldUnits && $yieldUnits > 0) ? $dynamicBatch / $yieldUnits : 0;
          $hasActiveData = $dynamicBatch > 0 || ($totalBatch ?? 0) > 0;
        @endphp

        <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-50 to-blue-100/80 dark:from-blue-900/40 dark:to-blue-800/20 
                    border border-blue-200/60 dark:border-blue-800/40 shadow-sm transition-all duration-300
                    {{ $dynamicBatch > 0 ? 'ring-2 ring-blue-300/50 dark:ring-blue-600/30' : '' }}">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-1.5 h-1.5 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full 
                        {{ $dynamicBatch > 0 ? 'animate-pulse' : '' }}"></div>
            <span class="text-sm font-semibold text-blue-700 dark:text-blue-200">Costo por receta</span>
            @if($dynamicBatch > 0 && $dynamicBatch != ($totalBatch ?? 0))
              <div class="ml-auto">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                  PREVIEW
                </span>
              </div>
            @endif
          </div>

          {{-- VALOR: uso color sólido para asegurar contraste --}}
          <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
            @if($dynamicBatch > 0)
              ${{ number_format($dynamicBatch, 2, ',', '.') }}
            @elseif(($totalBatch ?? 0) > 0)
              ${{ number_format($totalBatch, 2, ',', '.') }}
            @else
              <span class="text-blue-400 dark:text-blue-500">$0,00</span>
            @endif
          </div>
          @if($dynamicBatch == 0 && ($totalBatch ?? 0) == 0)
          @endif
        </div>
        
        <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-green-100/80 dark:from-emerald-900/40 dark:to-green-800/20 
                    border border-emerald-200/60 dark:border-emerald-800/40 shadow-sm transition-all duration-300
                    {{ ($yieldUnits ?? 0) > 0 ? 'ring-2 ring-emerald-300/50 dark:ring-emerald-600/30' : '' }}">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-1.5 h-1.5 bg-gradient-to-r from-emerald-500 to-green-600 rounded-full animate-pulse"></div>
            <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-200">Rendimiento</span>
          </div>

          {{-- VALOR: uso color sólido para asegurar contraste --}}
          <div class="text-2xl font-bold text-emerald-900 dark:text-emerald-100">
            @if(($yieldUnits ?? 0) > 0)
              {{ number_format($yieldUnits, 0, ',', '.') }} unidades
            @else
              <span class="text-emerald-400 dark:text-emerald-500">0 unidades</span>
            @endif
          </div>
          @if(($yieldUnits ?? 0) == 0)
            <p class="text-xs text-emerald-500 dark:text-emerald-400 mt-1">Define el rendimiento por receta</p>
          @endif
        </div>
        
        <div class="p-4 rounded-2xl bg-gradient-to-r from-purple-50 to-purple-100/80 dark:from-purple-900/40 dark:to-purple-800/20 
                    border border-purple-200/60 dark:border-purple-800/40 shadow-sm transition-all duration-300
                    {{ $dynamicPerUnit > 0 ? 'ring-2 ring-purple-300/50 dark:ring-purple-600/30' : '' }}">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-1.5 h-1.5 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full 
                        {{ $dynamicPerUnit > 0 ? 'animate-pulse' : '' }}"></div>
            <span class="text-sm font-semibold text-purple-700 dark:text-purple-200">Costo por Unidad</span>
            @if($dynamicPerUnit > 0 && $dynamicPerUnit != ($totalPerUnit ?? 0))
              <div class="ml-auto">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                  PREVIEW
                </span>
              </div>
            @endif
          </div>

          {{-- VALOR: uso color sólido para asegurar contraste --}}
          <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">
            @if($dynamicPerUnit > 0)
              ${{ number_format($dynamicPerUnit, 2, ',', '.') }}
            @elseif(($totalPerUnit ?? 0) > 0)
              ${{ number_format($totalPerUnit, 2, ',', '.') }}
            @else
              <span class="text-purple-400 dark:text-purple-500">$0,00</span>
            @endif
          </div>
          @if($dynamicPerUnit == 0 && ($totalPerUnit ?? 0) == 0)
          @endif
        </div>
      </div>

      {{-- Guardar análisis --}}
      <div class="flex flex-col sm:flex-row sm:justify-end gap-3 mt-6 pt-6 border-t border-slate-200/60 dark:border-neutral-800/60">
        <button type="button"
                class="group relative rounded-xl px-6 py-3 text-sm font-semibold transition-all duration-300
                       bg-gradient-to-r from-indigo-600 to-purple-600 text-white hover:from-indigo-700 hover:to-purple-700 
                       hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2
                       disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
                wire:click="saveAnalysis"
                wire:loading.attr="disabled"
                wire:target="saveAnalysis">
          <span wire:loading.remove wire:target="saveAnalysis" class="flex items-center justify-center gap-2">
            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Guardar análisis
          </span>
          <span wire:loading wire:target="saveAnalysis" class="flex items-center justify-center gap-2">
            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"/>
            </svg>
            Guardando…
          </span>
        </button>
      </div>

      @error('rows')
        <div class="p-4 rounded-2xl bg-gradient-to-r from-rose-50 to-red-50/80 dark:from-rose-900/40 dark:to-red-900/20 
                    border border-rose-200/60 dark:border-rose-800/40 shadow-sm">
          <div class="flex items-center gap-3">
            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="15" y1="9" x2="9" y2="15"/>
              <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <span class="text-sm text-rose-800 dark:text-rose-200">{{ $message }}</span>
          </div>
        </div>
      @enderror
    </div>
  </div>
</div>
