{{-- resources/views/livewire/product-card.blade.php --}}
<div class="product-card group"> {{-- ÚNICO elemento raíz --}}
  @if($displayMode === 'card')
    <button type="button"
            wire:click="add"
            wire:loading.attr="disabled"
            wire:target="add"
            @disabled($isAdding)
            class="w-full text-left rounded-lg border border-slate-200/70 bg-white p-3 hover:shadow transition
                   {{ ($currentStock && $isActive) ? '' : 'opacity-50 cursor-not-allowed' }}">
      <div class="font-medium truncate text-slate-800">{{ $product->name ?? 'Producto no encontrado' }}</div>
      <div class="text-[11px] text-slate-500 truncate">SKU: {{ $product->sku ?? 'N/A' }}</div>
      <div class="mt-1 text-sm text-slate-700">
        $ {{ $product ? number_format($product->price, 2, ',', '.') : '0,00' }}
      </div>

      <div class="mt-2 text-[11px]">
        <span class="px-2 py-0.5 rounded-full {{ $currentStock ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
          <span wire:loading.remove wire:target="add">Stock: {{ $currentStock }}</span>
          <span wire:loading wire:target="add" class="animate-pulse">Actualizando...</span>
        </span>
        @if(!$isActive)
          <span class="ml-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Inactivo</span>
        @endif
      </div>

      <div class="mt-2 text-[11px] text-slate-500">
        <span wire:loading.remove wire:target="add">
          @if($currentStock && $isActive) Click para sumar +1
          @elseif(!$isActive) Producto inactivo (se mostrará aviso)
          @else Sin stock (se mostrará aviso)
          @endif
        </span>
        <span wire:loading wire:target="add">Agregando al pedido...</span>
      </div>
    </button>

  @elseif($displayMode === 'button')
    <div class="flex items-center gap-2">
      <button type="button"
              wire:click="add"
              wire:loading.attr="disabled"
              wire:target="add"
              @disabled($isAdding)
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50 {{ $buttonClass }}">
        <span wire:loading.remove wire:target="add">
          {{ $buttonText }} @if($currentStock > 1 && $qty > 1) ({{ $qty }}) @endif
        </span>
        <span wire:loading wire:target="add">Agregando…</span>
      </button>
      <span class="text-xs {{ $isActive ? 'text-gray-500' : 'text-red-600' }}">
        {{ $isActive ? "Stock: $currentStock" : 'Inactivo' }}
      </span>
    </div>

  @else
    <button type="button"
            wire:click="addOne"
            wire:loading.attr="disabled"
            wire:target="addOne"
            @disabled($isAdding)
            class="px-3 py-1 text-sm font-medium text-white bg-green-600 rounded hover:bg-green-700 disabled:opacity-50 {{ $buttonClass }}">
      <span wire:loading.remove wire:target="addOne">{{ $buttonText }}</span>
      <span wire:loading wire:target="addOne">...</span>
    </button>
  @endif
</div>
