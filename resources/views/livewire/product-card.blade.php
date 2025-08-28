{{-- resources/views/livewire/product-card.blade.php --}}
@php
  $p = $product;
@endphp

<button type="button"
        wire:click="add"
        wire:loading.attr="disabled"
        @disabled(!$currentStock || !$isActive)
        class="w-full text-left rounded-lg border border-slate-200/70 bg-white p-3
               hover:shadow transition-all duration-150
               {{ ($currentStock && $isActive) ? '' : 'opacity-50 pointer-events-none' }}
               disabled:opacity-60"
        title="{{ $currentStock && $isActive ? 'Agregar al pedido' : 'Sin stock' }}">

  <div class="font-medium truncate text-slate-800">{{ $p->name }}</div>
  <div class="text-[11px] text-slate-500 truncate">SKU: {{ $p->sku }}</div>
  <div class="mt-1 text-sm text-slate-700">$ {{ number_format($p->price, 2, ',', '.') }}</div>

  <div class="mt-2 text-[11px]">
    <span class="px-2 py-0.5 rounded-full transition-colors
      {{ $currentStock ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
      <span wire:loading.remove>Stock: {{ $currentStock }}</span>
      <span wire:loading class="animate-pulse">Actualizando...</span>
    </span>
    
    {{-- Indicador de estado activo/inactivo --}}
    @if(!$isActive)
      <span class="ml-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
        Inactivo
      </span>
    @endif
  </div>

  <div class="mt-2 text-[11px] text-slate-500">
    <span wire:loading.remove>
      @if($currentStock && $isActive)
        Click para sumar +1
      @elseif(!$isActive)
        Producto inactivo
      @else
        Sin stock disponible
      @endif
    </span>
    <span wire:loading>Agregando al pedido...</span>
  </div>
</button>