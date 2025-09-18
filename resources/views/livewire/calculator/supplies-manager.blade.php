<div class="space-y-6">
  {{-- Alta rápida (crea una compra y recalcula el insumo) --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 overflow-hidden transition-colors">
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-4 sm:px-6 py-4 rounded-t-xl">
      <h2 class="text-lg font-semibold text-white">
        Agregar Insumo (Compra)
        <span class="ml-2 text-sm opacity-90">(Total: {{ $this->count }})</span>
      </h2>
    </div>

    <form wire:submit.prevent="quickStore" class="p-4 sm:p-6 space-y-4">
      @if (session('ok'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-3 py-2 text-sm
                    dark:border-emerald-700/60 dark:bg-emerald-900/25 dark:text-emerald-200 dark:ring-1 dark:ring-emerald-500/10">
          {{ session('ok') }}
        </div>
      @endif

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-200 mb-1">Nombre</label>
          <input wire:model.defer="name" required
                 class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-700
                        bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                        placeholder-gray-400 dark:placeholder-neutral-400
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                        transition-colors">
          @error('name') <div class="text-red-600 dark:text-rose-300 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-200 mb-1">Cantidad comprada</label>
          <input type="number" step="0.001" min="0" wire:model.defer="qty" required
                 class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-700
                        bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                        placeholder-gray-400 dark:placeholder-neutral-400
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                        transition-colors">
          @error('qty') <div class="text-red-600 dark:text-rose-300 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-200 mb-1">Unidad</label>
          <select wire:model.defer="unit" required
                  class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-700
                         bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                         placeholder-gray-400 dark:placeholder-neutral-400
                         focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                         transition-colors [color-scheme:light] dark:[color-scheme:dark]">
            <optgroup label="Masa">
              <option value="g">g</option>
              <option value="kg">kg</option>
            </optgroup>
            <optgroup label="Volumen">
              <option value="ml">ml</option>
              <option value="l">l</option>
              <option value="cm3">cm3</option>
            </optgroup>
            <optgroup label="Unidad">
              <option value="u">u</option>
            </optgroup>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-200 mb-1">Precio total ($)</label>
          <input type="number" step="0.01" min="0" wire:model.defer="total_cost" required
                 class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-700
                        bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                        placeholder-gray-400 dark:placeholder-neutral-400
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                        transition-colors">
          @error('total_cost') <div class="text-red-600 dark:text-rose-300 text-xs mt-1">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="flex justify-end">
        <button class="button-primary" wire:loading.attr="disabled">
          <span wire:loading.remove>Registrar compra</span>
          <span wire:loading>Guardando…</span>
        </button>
      </div>

      <p class="text-xs text-gray-500 dark:text-neutral-400">
        Tip: el stock y el costo promedio se recalculan automáticamente a partir de todas las compras registradas.
      </p>
    </form>
  </div>

{{-- Filtro y listado de insumos --}}
<div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 overflow-hidden transition-colors">
  <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-3">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100 flex-1">Insumos</h3>
    <input wire:model.debounce.400ms="search" placeholder="Buscar…"
           class="w-full sm:w-72 rounded-lg px-3 py-2 text-sm
                  border border-gray-300 dark:border-neutral-700
                  bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                  placeholder-gray-400 dark:placeholder-neutral-400
                  focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                  transition-colors">
  </div>

  <div class="p-4 sm:p-6">
    <div class="grid gap-4">
      @forelse($supplies as $s)
        @php
          // Si en el componente hiciste ->with(['purchases' => fn($q)=>$q->latest()->limit(1)])
          $last = $s->purchases->first();
        @endphp

        <div class="border border-gray-200 dark:border-neutral-700 rounded-lg p-4 bg-white dark:bg-neutral-900 dark:hover:bg-neutral-800/70 dark:ring-1 dark:ring-indigo-500/10 transition-colors"
             wire:key="supply-{{ $s->id }}">

          {{-- Encabezado insumo + acciones (un solo botón Editar) --}}
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="min-w-0">
              <h4 class="font-medium text-gray-900 dark:text-neutral-100 truncate">{{ $s->name }}</h4>
              <p class="text-sm text-gray-600 dark:text-neutral-300">
                Stock: {{ number_format((float)($s->stock_base_qty ?? 0), 2, ',', '.') }} {{ $s->base_unit }}
                • $/base: {{ number_format((float)($s->avg_cost_per_base ?? 0), 6, ',', '.') }}
              </p>
              @if($last)
                <p class="text-xs text-gray-500 dark:text-neutral-400">
                  Última compra: {{ rtrim(rtrim(number_format((float)$last->qty, 6, ',', '.'), '0'), ',') }} {{ $last->unit }}
                  — Total: $ {{ number_format((float)$last->total_cost, 2, ',', '.') }}
                </p>
              @else
                <p class="text-xs text-gray-500 dark:text-neutral-400">Aún sin compras registradas.</p>
              @endif
            </div>

            <div class="flex gap-2 shrink-0">
              <button wire:click="startEditBoth({{ $s->id }})" class="button-primary-sm">
                Editar
              </button>
              <button wire:click="delete({{ $s->id }})"
                      class="px-3 py-2 rounded-lg text-sm font-medium bg-red-600 hover:bg-red-700 text-white">
                Eliminar
              </button>
            </div>
          </div>

          {{-- Editor único (Nombre + precio total de la última compra si existe) --}}
          @if($editingId === $s->id)
            <div class="mt-3 grid grid-cols-1 sm:grid-cols-5 gap-3 items-start">
              {{-- Nombre --}}
              <div class="sm:col-span-3">
                <label class="text-xs text-gray-600 dark:text-neutral-300">Nombre</label>
                <input wire:model.defer="e_name"
                       class="w-full rounded-lg px-3 py-2 text-sm border border-gray-300 dark:border-neutral-700
                              bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80">
                @error('e_name') <p class="text-xs mt-1 text-red-600 dark:text-rose-300">{{ $message }}</p> @enderror
              </div>

              {{-- Precio total de la última compra (solo si existe una compra) --}}
              @if($editingPurchaseId)
                <div class="sm:col-span-2">
                  <label class="text-xs text-gray-600 dark:text-neutral-300">Precio total última compra ($)</label>
                  <input type="number" step="0.01" min="0" wire:model.defer="ep_total_cost"
                         class="w-full rounded-lg px-3 py-2 text-sm border border-gray-300 dark:border-neutral-700
                                bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80">
                  @error('ep_total_cost') <p class="text-xs mt-1 text-red-600 dark:text-rose-300">{{ $message }}</p> @enderror
                </div>
              @endif

              <div class="sm:col-span-5 flex gap-2 items-end">
                <button wire:click="saveBoth" wire:loading.attr="disabled" class="button-primary-sm">
                  <span wire:loading.remove wire:target="saveBoth">Guardar</span>
                  <span wire:loading wire:target="saveBoth">Guardando…</span>
                </button>
                <button type="button" wire:click="cancelEditBoth"
                        class="px-4 py-2 rounded-lg text-sm font-medium
                               bg-gray-200 hover:bg-gray-300 dark:bg-neutral-800 dark:hover:bg-neutral-700
                               text-gray-800 dark:text-neutral-200">
                  Cancelar
                </button>
              </div>
            </div>
          @endif
        </div>
      @empty
        <div class="text-center py-10 text-gray-500 dark:text-neutral-400">No hay insumos cargados.</div>
      @endforelse
    </div>
  </div>
</div>


    <div class="mt-6">
      {{ $supplies->links() }}
    </div>
  </div>
</div>

@push('styles')
<style>
  .button-primary{
    @apply inline-flex items-center justify-center px-5 py-2.5 rounded-lg font-medium text-white
           bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800
           dark:bg-indigo-600 dark:hover:bg-indigo-500 dark:active:bg-indigo-700
           dark:ring-1 dark:ring-indigo-400/30
           transition-colors disabled:opacity-60 disabled:cursor-not-allowed;
  }
  .button-primary-sm{
    @apply inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-white
           bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800
           dark:bg-indigo-600 dark:hover:bg-indigo-500 dark:active:bg-indigo-700
           dark:ring-1 dark:ring-indigo-400/30
           transition-colors disabled:opacity-60 disabled:cursor-not-allowed;
  }
</style>
@endpush