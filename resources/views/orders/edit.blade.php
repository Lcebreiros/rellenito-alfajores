{{-- resources/views/orders/edit.blade.php --}}
@extends('layouts.app')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
    {{-- Información del pedido --}}
    <div>
        <h1 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">
            Editar Pedido #{{ $order->id }}
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
            {{ $order->created_at?->format('d/m/Y H:i') }}
        </p>
    </div>

    {{-- Botones --}}
    <div class="flex gap-2">
        <a href="{{ route('orders.show', $order) }}"
           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-neutral-200 dark:border-neutral-600
                  text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors text-sm">
            Volver
        </a>

        <form method="POST" action="{{ route('orders.destroy', $order) }}" 
              onsubmit="return confirm('¿Seguro querés eliminar este pedido?');">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-rose-600
                           text-white bg-rose-600 hover:bg-rose-700 transition-colors text-sm">
                Eliminar Pedido
            </button>
        </form>
    </div>
</div>
@endsection

@section('content')
@php
  $client = $order->client ?? null;
  $userOwner = $order->user ?? null;

  $customerName  = $client->name ?? $order->customer_name ?? ($userOwner->name ?? '');
  $customerEmail = $client->email ?? $order->customer_email ?? '';
  $customerPhone = $client->phone ?? $order->customer_phone ?? '';
  $shippingAddr  = $order->shipping_address ?? ($client->address ?? null);
  $notes         = $order->note ?? $order->notes ?? '';
@endphp

<div class="space-y-6 lg:space-y-8" 
     x-data="orderEdit({
        items: {{ $order->items->map(fn($i)=>[
            'id'=>$i->product->id ?? null,
            'name'=>$i->product->name ?? $i->name,
            'quantity'=>$i->quantity,
            'unit_price'=>$i->unit_price,
        ])->toJson() }},
        products: {{ $products->map(fn($p)=>[
            'id'=>$p->id,
            'name'=>$p->name,
            'price'=>$p->price,
        ])->toJson() }}
     })">

  <form method="POST" action="{{ route('orders.update', $order) }}" x-ref="orderForm" @submit.prevent="submitForm()">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

      {{-- Columna izquierda: productos --}}
      <div class="lg:col-span-7 xl:col-span-8">
        <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm">
          
          <div class="px-5 sm:px-6 py-5 border-b border-neutral-100 dark:border-neutral-800/60 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Productos</h2>
          </div>

{{-- Lista de items --}}
<div class="px-5 sm:px-6 py-4 lg:max-h-[48vh] lg:overflow-y-auto custom-scroll space-y-3">
  <template x-for="(item, index) in items" :key="item.id">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-xl bg-neutral-50 dark:bg-neutral-800 shadow-sm hover:shadow-md transition-shadow">
      
      {{-- Nombre y precio unitario --}}
      <div class="flex-1 min-w-0">
        <div class="font-medium text-neutral-800 dark:text-neutral-100 truncate" x-text="item.name"></div>
        <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5" x-text="'$' + (item.unit_price ?? 0).toFixed(2) + ' · Unidad'"></div>
      </div>

      {{-- Cantidad --}}
      <div class="flex-shrink-0">
        <input type="number" min="1" x-model.number="item.quantity"
               class="w-20 text-center border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
      </div>

      {{-- Subtotal --}}
      <div class="flex-shrink-0 text-right font-semibold text-neutral-900 dark:text-neutral-100 min-w-[80px]" 
           x-text="'$' + (item.quantity * item.unit_price).toFixed(2)"></div>

      {{-- Botón eliminar --}}
      <div class="flex-shrink-0">
        <button type="button" @click="items.splice(index,1)"
                class="px-3 py-1 text-white bg-rose-600 rounded-lg hover:bg-rose-700 dark:hover:bg-rose-500 transition-colors shadow-sm hover:shadow-md">
          Eliminar
        </button>
      </div>

    </div>
  </template>

  {{-- Mensaje cuando no hay productos --}}
  <template x-if="items.length === 0">
    <div class="py-14 text-center text-neutral-500 dark:text-neutral-400">
      No hay productos en este pedido
    </div>
  </template>
</div>


          {{-- Agregar productos disponibles --}}
          <div class="px-5 sm:px-6 py-4 border-t border-neutral-100 dark:border-neutral-800/60">
            <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Agregar productos</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
              <template x-for="prod in products" :key="prod.id">
                <button type="button" @click="addProduct(prod)"
                        class="px-2 py-1 border rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-sm">
                  <span x-text="prod.name"></span>
                </button>
              </template>
            </div>
          </div>

          {{-- Totales --}}
          <div class="px-5 sm:px-6 py-5 bg-neutral-50 dark:bg-neutral-950/40 border-t border-neutral-100 dark:border-neutral-800/60">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <div class="flex items-center justify-between">
                <span class="text-neutral-600 dark:text-neutral-300">Subtotal</span>
                <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="'$'+subtotal().toFixed(2)"></span>
              </div>
              <div class="sm:col-span-2 flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-neutral-800/60">
                <span class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Total</span>
                <span class="text-lg sm:text-xl font-bold text-emerald-600" x-text="'$'+subtotal().toFixed(2)"></span>
              </div>
            </div>
          </div>

        </div>
      </div>

{{-- Columna derecha: cliente --}}
<div class="lg:col-span-5 xl:col-span-4 space-y-6">
  <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm">
    <div class="px-5 sm:px-6 py-5 border-b border-neutral-100 dark:border-neutral-800/60 flex items-center justify-between">
      <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Cliente</h3>
    </div>
    <div class="px-5 sm:px-6 py-5 space-y-4">

      <div>
        <label class="text-xs text-neutral-500 dark:text-neutral-400 mb-1 block">Nombre</label>
        <input type="text" name="name" value="{{ $customerName }}"
               class="w-full border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
      </div>

      <div>
        <label class="text-xs text-neutral-500 dark:text-neutral-400 mb-1 block">Email</label>
        <input type="email" name="email" value="{{ $customerEmail }}"
               class="w-full border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
      </div>

      <div>
        <label class="text-xs text-neutral-500 dark:text-neutral-400 mb-1 block">Teléfono</label>
        <input type="text" name="phone" value="{{ $customerPhone }}"
               class="w-full border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
      </div>

      <div>
        <label class="text-xs text-neutral-500 dark:text-neutral-400 mb-1 block">Dirección</label>
        <input type="text" name="address" value="{{ $shippingAddr }}"
               class="w-full border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
      </div>

      {{-- Botón guardar --}}
      <div class="pt-2">
        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 dark:hover:bg-indigo-500 transition-colors">
          Guardar cambios
        </button>
      </div>

    </div>
  </div>

  {{-- Agendamiento --}}
  <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm"
       x-data="{ isScheduled: {{ $order->is_scheduled ? 'true' : 'false' }} }">
    <div class="px-5 sm:px-6 py-5 border-b border-neutral-100 dark:border-neutral-800/60">
      <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Agendamiento</h3>
    </div>
    <div class="px-5 sm:px-6 py-5 space-y-4">

      {{-- Toggle agendar --}}
      <div class="flex items-center justify-between">
        <label class="text-sm text-neutral-700 dark:text-neutral-300">Agendar pedido</label>
        <button type="button" @click="isScheduled = !isScheduled"
                :class="isScheduled ? 'bg-indigo-600' : 'bg-neutral-300 dark:bg-neutral-700'"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
          <span :class="isScheduled ? 'translate-x-6' : 'translate-x-1'"
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
        </button>
        <input type="hidden" name="is_scheduled" :value="isScheduled ? '1' : '0'">
      </div>

      {{-- Campo de fecha/hora --}}
      <div x-show="isScheduled" x-transition class="space-y-2">
        <label class="text-xs text-neutral-500 dark:text-neutral-400 block">Fecha y hora</label>
        <input type="datetime-local" name="scheduled_for"
               value="{{ $order->scheduled_for?->format('Y-m-d\TH:i') }}"
               class="w-full border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
      </div>

    </div>
  </div>
</div>


    </div>

    {{-- Hidden input para enviar items --}}
    <input type="hidden" name="items_json" x-model="JSON.stringify(items)">
  </form>
</div>

@push('scripts')
<script>
function orderEdit({ items: initialItems, products }) {
    return {
        items: initialItems,
        products,
        addProduct(prod) {
            let exists = this.items.find(i => i.id === prod.id);
            if (exists) exists.quantity++;
            else this.items.push({ id: prod.id, name: prod.name, quantity: 1, unit_price: prod.price ?? 0 });
        },
        subtotal() {
            return this.items.reduce((sum, i) => sum + (i.unit_price * i.quantity), 0);
        },
        submitForm() {
            this.$refs.orderForm.querySelector('input[name="items_json"]').value = JSON.stringify(this.items);
            this.$refs.orderForm.submit();
        }
    }
}
</script>
@endpush

@push('head')
<style>
.custom-scroll::-webkit-scrollbar{ width:6px; }
.custom-scroll::-webkit-scrollbar-track{ background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb{ background:#d4d4d4; border-radius:3px; }
.dark .custom-scroll::-webkit-scrollbar-thumb{ background:#52525b; }
</style>
@endpush

@endsection
