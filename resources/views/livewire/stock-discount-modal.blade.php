<div>
  {{-- Modal de Descuento de Stock --}}
  @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto"
         x-data="{ show: @entangle('showModal').live }"
         x-show="show"
         x-cloak
         @keydown.escape.window="$wire.closeModal()">

      <div class="relative w-full max-w-lg my-auto bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700
                  transform transition-all max-h-[calc(100vh-2rem)] flex flex-col"
           @click.away="$wire.closeModal()">

        {{-- Header del modal (fijo) --}}
        <div class="flex items-start justify-between p-5 border-b border-gray-200 dark:border-neutral-700 flex-shrink-0">
          <div class="flex-1">
            <h3 class="text-lg font-bold text-gray-900 dark:text-neutral-100 mb-1">
              <i class="fas fa-minus-circle text-rose-500 mr-2"></i>
              Descontar Stock
            </h3>
            <p class="text-sm text-gray-600 dark:text-neutral-400">
              {{ $productName }}
            </p>
          </div>
          <button type="button"
                  wire:click="closeModal"
                  class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-300 transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        {{-- Contenido del modal (con scroll) --}}
        <div class="overflow-y-auto flex-1">
          <form wire:submit.prevent="discount" class="p-5 space-y-4">

          {{-- Stock actual --}}
          <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                  <i class="fas fa-boxes-stacked text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                  <div class="text-xs text-blue-600 dark:text-blue-400 font-medium uppercase tracking-wide">
                    Stock Actual
                  </div>
                  <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                    {{ number_format($currentStock) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Cantidad a descontar --}}
          <div>
            <label for="quantity" class="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-2">
              <i class="fas fa-hashtag text-xs mr-1"></i>
              Cantidad a Descontar *
            </label>
            <input type="number"
                   id="quantity"
                   wire:model.defer="quantity"
                   min="1"
                   max="{{ $currentStock }}"
                   class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600
                          bg-white dark:bg-neutral-800
                          text-gray-900 dark:text-neutral-100
                          rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500
                          transition-colors"
                   placeholder="Ingrese la cantidad"
                   required>
            @error('quantity')
              <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
              </p>
            @enderror
            <p class="mt-2 text-xs text-gray-500 dark:text-neutral-400">
              Máximo: {{ number_format($currentStock) }} unidades
            </p>
          </div>

          {{-- Fecha del descuento --}}
          <div>
            <label for="adjustmentDate" class="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-2">
              <i class="fas fa-calendar text-xs mr-1"></i>
              Fecha del Descuento *
            </label>
            <input type="date"
                   id="adjustmentDate"
                   wire:model.defer="adjustmentDate"
                   max="{{ now()->format('Y-m-d') }}"
                   class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600
                          bg-white dark:bg-neutral-800
                          text-gray-900 dark:text-neutral-100
                          rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500
                          transition-colors"
                   required>
            @error('adjustmentDate')
              <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
              </p>
            @enderror
            <p class="mt-2 text-xs text-gray-500 dark:text-neutral-400">
              La fecha no puede ser futura
            </p>
          </div>

          {{-- Motivo del descuento --}}
          <div>
            <label for="notes" class="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-2">
              <i class="fas fa-note-sticky text-xs mr-1"></i>
              Motivo del Descuento *
            </label>
            <textarea id="notes"
                      wire:model.defer="notes"
                      rows="3"
                      maxlength="500"
                      class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600
                             bg-white dark:bg-neutral-800
                             text-gray-900 dark:text-neutral-100
                             rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500
                             transition-colors resize-none"
                      placeholder="Ejemplo: Producto dañado, vencido, usado para degustación, etc."
                      required></textarea>
            @error('notes')
              <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
              </p>
            @enderror
            <p class="mt-2 text-xs text-gray-500 dark:text-neutral-400">
              <span wire:ignore>
                <span x-data="{ count: $wire.entangle('notes').length || 0 }"
                      x-text="count + '/500 caracteres'"></span>
              </span>
            </p>
          </div>

          {{-- Vista previa del resultado --}}
          @if($quantity > 0 && $quantity <= $currentStock)
            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-xs text-amber-600 dark:text-amber-400 font-medium uppercase tracking-wide mb-1">
                    Stock Resultante
                  </div>
                  <div class="text-2xl font-bold text-amber-900 dark:text-amber-100">
                    {{ number_format($currentStock - $quantity) }}
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                    Se descontarán
                  </div>
                  <div class="text-lg font-bold text-rose-600 dark:text-rose-400">
                    -{{ number_format($quantity) }}
                  </div>
                </div>
              </div>
            </div>
          @endif
          </form>
        </div>

        {{-- Botones de acción (fijos al fondo) --}}
        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800/50 rounded-b-2xl flex-shrink-0">
          <button type="button"
                  wire:click="closeModal"
                  class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-neutral-300
                         bg-white dark:bg-neutral-800
                         border border-gray-300 dark:border-neutral-600
                         rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-700
                         focus:ring-2 focus:ring-gray-500 focus:ring-offset-2
                         transition-colors">
            <i class="fas fa-times mr-2"></i>
            Cancelar
          </button>
          <button type="button"
                  wire:click="discount"
                  class="px-5 py-2.5 text-sm font-semibold text-white
                         bg-gradient-to-r from-rose-600 to-rose-700
                         hover:from-rose-700 hover:to-rose-800
                         rounded-xl shadow-lg hover:shadow-xl
                         focus:ring-2 focus:ring-rose-500 focus:ring-offset-2
                         transition-all transform hover:-translate-y-0.5">
            <i class="fas fa-minus-circle mr-2"></i>
            Descontar Stock
          </button>
        </div>
      </div>
    </div>
  @endif
</div>
