<div class="space-y-6">
  {{-- Alta rápida (crea una compra y recalcula el insumo) --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 overflow-hidden transition-colors">
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-4 sm:px-6 py-4 rounded-t-xl">
      <h2 class="text-lg font-semibold text-white">
        Agregar Insumo (Compra)
        <span class="ml-2 text-sm opacity-90">(Total: {{ $this->count }})</span>
      </h2>
    </div>

    <form wire:submit="quickStore" class="p-4 sm:p-6 space-y-4">
      @if (session('ok'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-3 py-2 text-sm
                    dark:border-emerald-700/60 dark:bg-emerald-900/25 dark:text-emerald-200 dark:ring-1 dark:ring-emerald-500/10">
          {{ session('ok') }}
        </div>
      @endif

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-200 mb-1">Nombre</label>
          <input wire:model.defer="name"
                 class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-700
                        bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                        placeholder-gray-400 dark:placeholder-neutral-400
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                        transition-colors" required>
          @error('name') <div class="text-red-600 dark:text-rose-300 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-200 mb-1">Cantidad comprada</label>
          <input type="number" step="0.001" min="0" wire:model.defer="qty"
                 class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-700
                        bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                        placeholder-gray-400 dark:placeholder-neutral-400
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                        transition-colors" required>
          @error('qty') <div class="text-red-600 dark:text-rose-300 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-200 mb-1">Unidad</label>
          <select wire:model.defer="unit"
                  class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-700
                         bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                         placeholder-gray-400 dark:placeholder-neutral-400
                         focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                         transition-colors [color-scheme:light] dark:[color-scheme:dark]" required>
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
          <input type="number" step="0.01" min="0" wire:model.defer="total_cost"
                 class="w-full rounded-lg px-3 py-2 border border-gray-300 dark:border-neutral-700
                        bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                        placeholder-gray-400 dark:placeholder-neutral-400
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                        transition-colors" required>
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
          <div class="border border-gray-200 dark:border-neutral-700 rounded-lg p-4 bg-white dark:bg-neutral-900 dark:hover:bg-neutral-800/70 dark:ring-1 dark:ring-indigo-500/10 transition-colors">
            @if($editingId === $s->id)
              {{-- Edición: solo nombre (stock y $/base son derivados) --}}
              <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-start">
                <div class="sm:col-span-3">
                  <label class="text-xs text-gray-600 dark:text-neutral-300">Nombre</label>
                  <input wire:model.defer="e_name"
                         class="w-full rounded-lg px-3 py-2 text-sm
                                border border-gray-300 dark:border-neutral-700
                                bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100
                                placeholder-gray-400 dark:placeholder-neutral-400
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400/80
                                transition-colors">
                </div>

                <div class="text-xs sm:col-span-2">
                  <div class="text-gray-600 dark:text-neutral-300 mb-1">Valores derivados</div>
                  <div class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800/50 dark:ring-1 dark:ring-indigo-500/10 px-3 py-2">
                    <div>Stock: <span class="font-medium text-gray-900 dark:text-neutral-100">{{ number_format($s->stock_base_qty,2) }} {{ $s->base_unit }}</span></div>
                    <div>$ por {{ $s->base_unit }}: <span class="font-medium text-gray-900 dark:text-neutral-100">{{ number_format($s->avg_cost_per_base,6) }}</span></div>
                  </div>
                </div>

                <div class="flex gap-2 sm:col-span-5">
                  <button wire:click="saveEdit" class="button-primary-sm">Guardar</button>
                  <button wire:click="cancelEdit" type="button" class="button-primary-sm bg-gray-500 hover:bg-gray-600 dark:bg-neutral-700 dark:hover:bg-neutral-600">Cancelar</button>
                </div>
              </div>
            @else
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="min-w-0">
                  <h4 class="font-medium text-gray-900 dark:text-neutral-100 truncate">{{ $s->name }}</h4>
                  <p class="text-sm text-gray-600 dark:text-neutral-300">
                    Stock: {{ number_format($s->stock_base_qty,2) }} {{ $s->base_unit }}
                    • $/base: {{ number_format($s->avg_cost_per_base,6) }}
                  </p>
                </div>
                <div class="flex gap-3 shrink-0">
                  <button wire:click="startEdit({{ $s->id }})"
                          class="font-medium text-sm text-blue-600 hover:text-blue-800 dark:text-indigo-300 dark:hover:text-indigo-200">
                    Editar
                  </button>
                  <button wire:click="delete({{ $s->id }})"
                          class="font-medium text-sm text-red-600 hover:text-red-800 dark:text-rose-300 dark:hover:text-rose-200">
                    Eliminar
                  </button>
                </div>
              </div>
            @endif
          </div>
        @empty
          <div class="text-center py-10 text-gray-500 dark:text-neutral-400">No hay insumos cargados.</div>
        @endforelse
      </div>

      <div class="mt-6">
        {{ $supplies->links() }}
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  /* Botones primarios: mantener INDIGO también en oscuro (acentos se respetan) */
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
