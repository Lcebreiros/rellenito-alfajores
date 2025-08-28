{{-- resources/views/livewire/add-to-order-button.blade.php --}}
<div class="flex items-center gap-2">
  <div class="flex items-center rounded-lg border border-gray-300 overflow-hidden">
    <button type="button" wire:click="$set('qty', max(1, $wire.qty-1))"
            class="px-2 py-1 hover:bg-gray-50">−</button>
    <input type="number" min="1" wire:model.live="qty"
           class="w-14 border-0 text-center focus:ring-0"
           aria-label="Cantidad">
    <button type="button" wire:click="$set('qty', $wire.qty+1)"
            class="px-2 py-1 hover:bg-gray-50">+</button>
  </div>

  <button wire:click="add" wire:loading.attr="disabled"
          class="rounded-lg bg-indigo-600 px-3 py-2 text-white text-sm hover:bg-indigo-700 disabled:opacity-60">
    <span wire:loading.remove>Agregar</span>
    <span wire:loading>Agregando…</span>
  </button>
</div>
