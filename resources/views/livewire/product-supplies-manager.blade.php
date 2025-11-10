<div class="rounded-lg border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900 p-6">
  <div class="mb-4">
    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Insumos del Producto</h3>
    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
      Asigna los insumos que se utilizan al vender este producto. Se descontarán automáticamente del stock.
    </p>
  </div>

  @if(session()->has('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm
                dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('success') }}
    </div>
  @endif

  <!-- Formulario para agregar insumo -->
  <form wire:submit.prevent="addSupply" class="mb-6 p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700">
    <h4 class="text-sm font-medium text-neutral-900 dark:text-neutral-100 mb-3">Agregar Insumo</h4>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
          Insumo <span class="text-rose-500">*</span>
        </label>
        <select wire:model="supply_id" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
          <option value="">Selecciona...</option>
          @foreach($supplies as $supply)
            <option value="{{ $supply['id'] }}">{{ $supply['name'] }} ({{ strtoupper($supply['base_unit']) }})</option>
          @endforeach
        </select>
        @error('supply_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
          Cantidad <span class="text-rose-500">*</span>
        </label>
        <input type="number" wire:model="qty" step="0.001" min="0.001"
               class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
               placeholder="0.000">
        @error('qty') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
          Unidad <span class="text-rose-500">*</span>
        </label>
        <select wire:model="unit" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
          <option value="g">Gramos (g)</option>
          <option value="kg">Kilogramos (kg)</option>
          <option value="ml">Mililitros (ml)</option>
          <option value="l">Litros (l)</option>
          <option value="cm3">Centímetros cúbicos (cm3)</option>
          <option value="u">Unidades (u)</option>
        </select>
        @error('unit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
          % Merma
        </label>
        <input type="number" wire:model="waste_pct" step="0.01" min="0" max="100"
               class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
               placeholder="0.00">
        @error('waste_pct') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>
    </div>

    <div class="mt-4 flex justify-end">
      <button type="submit"
              class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Agregar
      </button>
    </div>
  </form>

  <!-- Lista de insumos asignados -->
  @if(empty($recipes))
    <div class="text-center py-8 text-neutral-500 dark:text-neutral-400">
      <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
      <p class="mt-2 text-sm">No hay insumos asignados a este producto</p>
    </div>
  @else
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-800">
        <thead class="bg-neutral-50 dark:bg-neutral-800/50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
              Insumo
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
              Cantidad
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
              Unidad
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
              % Merma
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
              Stock Disponible
            </th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-neutral-900 divide-y divide-neutral-200 dark:divide-neutral-800">
          @foreach($recipes as $recipe)
            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-neutral-100">
                {{ $recipe['supply']['name'] ?? 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400 tabular-nums">
                {{ number_format($recipe['qty'], 3, ',', '.') }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400">
                {{ strtoupper($recipe['unit']) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400 tabular-nums">
                {{ number_format($recipe['waste_pct'], 2, ',', '.') }}%
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400 tabular-nums">
                {{ $recipe['supply']['formatted_stock'] ?? number_format($recipe['supply']['stock_base_qty'] ?? 0, 0, ',', '.') }} {{ strtoupper($recipe['supply']['base_unit'] ?? '') }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button wire:click="removeSupply({{ $recipe['id'] }})"
                        wire:confirm="¿Eliminar este insumo del producto?"
                        class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif

  <!-- Nota informativa -->
  @if(!empty($recipes))
    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20 p-3">
      <div class="flex items-start gap-2">
        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-xs text-amber-800 dark:text-amber-300">
          Al vender este producto, los insumos se descontarán automáticamente del stock según las cantidades configuradas más el porcentaje de merma.
        </p>
      </div>
    </div>
  @endif
</div>
