{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl sm:text-2xl font-semibold text-slate-800">Pedido #{{ $order->id }}</h1>
      <p class="text-sm text-slate-500">{{ $order->created_at?->format('d/m/Y H:i') }}</p>
    </div>
    <div class="flex items-center gap-3">
      @php
        $map = [
          'draft'     => ['label' => 'Borrador',  'classes' => 'bg-slate-100 text-slate-700', 'icon' => '○'],
          'pending'   => ['label' => 'Pendiente', 'classes' => 'bg-amber-100 text-amber-800', 'icon' => '⏱'],
          'paid'      => ['label' => 'Pagado',    'classes' => 'bg-emerald-100 text-emerald-700', 'icon' => '✓'],
          'canceled'  => ['label' => 'Cancelado', 'classes' => 'bg-rose-100 text-rose-700', 'icon' => '✕'],
          'fulfilled' => ['label' => 'Entregado', 'classes' => 'bg-indigo-100 text-indigo-700', 'icon' => '📦'],
        ];
        $status = $map[$order->status] ?? ['label' => ucfirst($order->status ?? '—'), 'classes' => 'bg-slate-100 text-slate-700', 'icon' => '○'];
      @endphp
      <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium {{ $status['classes'] }}">
        <span class="mr-1.5">{{ $status['icon'] }}</span>
        {{ $status['label'] }}
      </span>
    </div>
  </div>
@endsection

@section('content')
@php
  // helpers seguros
  $customerName  = $order->customer_name ?? $order->user->name ?? 'Cliente';
  $customerEmail = $order->customer_email ?? $order->user->email ?? '';
  $customerPhone = $order->customer_phone ?? '';
  $shippingAddr  = $order->shipping_address ?? '';
  $notes         = $order->notes ?? null;

  $currency = $order->currency ?? 'ARS';
  $fmt = fn($n) => '$'.number_format((float)$n, 2, ',', '.');

  $items = $order->items ?? collect();
  $subtotalCalc = $items->sum(fn($it)=> (float)($it->unit_price ?? 0) * (int)($it->quantity ?? 0));
  $discount = (float)($order->discount_total ?? 0);
  $shipping = (float)($order->shipping_total ?? 0);
  $tax      = (float)($order->tax_total ?? 0);
  $subtotal = (float)($order->subtotal ?? $subtotalCalc);
  $total    = (float)($order->total ?? ($subtotal - $discount + $shipping + $tax));
  $totalItems = $items->sum('quantity');
@endphp

<div class="h-[calc(100vh-10rem)] overflow-hidden">
  <div class="grid grid-cols-12 gap-6 h-full">
    
    {{-- Columna Izquierda: Información Principal --}}
    <div class="col-span-8">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm h-full flex flex-col">
        
        {{-- Header Compacto --}}
        <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-6 border-b border-slate-100 flex-shrink-0">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-slate-800 mb-1">Pedido #{{ $order->id }}</h2>
              <p class="text-slate-500 text-sm">{{ $order->created_at?->format('d \d\e F, Y • H:i') }}</p>
            </div>
            <div class="text-right">
              <div class="text-3xl font-bold text-emerald-600 mb-1">{{ $fmt($total) }}</div>
              <div class="text-sm text-slate-500">{{ $totalItems }} artículos</div>
            </div>
          </div>
        </div>

        {{-- Lista de Productos con Scroll Controlado --}}
        <div class="flex-1 px-6 py-4 overflow-hidden">
          <h3 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
              <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Productos
          </h3>
          
          <div class="h-full overflow-y-auto pr-2 space-y-3" style="max-height: calc(100% - 2rem);">
            @forelse ($items as $item)
              @php
                $name = $item->product->name ?? $item->name ?? 'Producto';
                $unit = (float)($item->unit_price ?? 0);
                $qty  = (int)($item->quantity ?? 0);
                $itemTotal = $unit * $qty;
              @endphp
              <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-slate-50 transition-colors">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                  {{-- Imagen o placeholder más pequeña --}}
                  @if (!empty($item->product?->image_url))
                    <img class="w-10 h-10 rounded-lg object-cover border border-slate-200" src="{{ $item->product->image_url }}" alt="">
                  @else
                    <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400">
                      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
                      </svg>
                    </div>
                  @endif
                  
                  <div class="min-w-0 flex-1">
                    <h4 class="font-medium text-slate-800 truncate">{{ $name }}</h4>
                    <div class="text-sm text-slate-500">{{ $fmt($unit) }} × {{ $qty }}</div>
                  </div>
                </div>
                
                <div class="font-semibold text-slate-800 ml-4">{{ $fmt($itemTotal) }}</div>
              </div>
            @empty
              <div class="flex flex-col items-center justify-center h-full text-slate-500">
                <svg class="w-16 h-16 mb-3 text-slate-300" viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                  <path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                <p>No hay productos en este pedido</p>
              </div>
            @endforelse
          </div>
        </div>

        {{-- Totales Compactos --}}
        @if($items->count() > 0)
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex-shrink-0">
          <div class="flex justify-between items-center text-sm">
            <div class="flex gap-6">
              <div><span class="text-slate-600">Subtotal:</span> <span class="font-medium">{{ $fmt($subtotal) }}</span></div>
              @if($discount > 0)<div class="text-emerald-600"><span>Desc:</span> <span class="font-medium">-{{ $fmt($discount) }}</span></div>@endif
              @if($shipping > 0)<div><span class="text-slate-600">Envío:</span> <span class="font-medium">{{ $fmt($shipping) }}</span></div>@endif
              @if($tax > 0)<div><span class="text-slate-600">IVA:</span> <span class="font-medium">{{ $fmt($tax) }}</span></div>@endif
            </div>
            <div class="text-lg font-bold text-emerald-600">Total: {{ $fmt($total) }}</div>
          </div>
        </div>
        @endif
      </div>
    </div>

    {{-- Columna Derecha: Cliente e Info --}}
    <div class="col-span-4">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm h-full flex flex-col">
        
        {{-- Información del Cliente --}}
        <div class="px-5 py-5 border-b border-slate-100 flex-1">
          <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
              <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
            </svg>
            Cliente
          </h3>
          
          <div class="space-y-4">
            <div>
              <div class="text-xs text-slate-500 mb-1">Nombre</div>
              <div class="font-medium text-slate-800 text-lg">{{ $customerName }}</div>
            </div>
            
            @if($customerEmail)
            <div>
              <div class="text-xs text-slate-500 mb-1">Email</div>
              <div class="font-medium text-slate-700 text-sm break-all">{{ $customerEmail }}</div>
            </div>
            @endif
            
            @if($customerPhone)
            <div>
              <div class="text-xs text-slate-500 mb-1">Teléfono</div>
              <div class="font-medium text-slate-700">{{ $customerPhone }}</div>
            </div>
            @endif
            
            @if($shippingAddr)
            <div>
              <div class="text-xs text-slate-500 mb-1">Dirección</div>
              <div class="font-medium text-slate-700 text-sm leading-relaxed">{{ $shippingAddr }}</div>
            </div>
            @endif
            
            @if($notes)
            <div>
              <div class="text-xs text-slate-500 mb-2">Notas</div>
              <div class="text-slate-700 bg-slate-50 rounded-lg p-3 text-sm leading-relaxed">{{ Str::limit($notes, 150) }}</div>
            </div>
            @endif
            
            <div class="pt-4 border-t border-slate-100">
              <div class="text-xs text-slate-500">Actualizado</div>
              <div class="text-sm text-slate-700">{{ $order->updated_at?->format('d/m/Y H:i') }}</div>
            </div>
          </div>
        </div>

        {{-- Acciones --}}
        <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex-shrink-0">
          <div class="flex flex-col gap-2">
            <a href="{{ route('orders.index') }}"
               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-white transition-colors text-sm">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                <path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Volver a Lista
            </a>
            
            <div class="flex gap-2">
              @can('update', $order)
                <a href="{{ route('orders.edit', $order) }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors text-sm">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L8 18l-4 1 1-4 11.5-11.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Editar
                </a>
              @endcan
              
              <button onclick="window.print()"
                      class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-white transition-colors text-sm">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                  <polyline points="6,9 6,2 18,2 18,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <rect x="6" y="14" width="12" height="8" rx="1" stroke="currentColor" stroke-width="2"/>
                </svg>
                Imprimir
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('head')
<style>
/* Scrollbar personalizado para la lista de productos */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 2px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

@media print {
  .h-\[calc\(100vh-10rem\)\] { height: auto; }
  .overflow-hidden { overflow: visible; }
  .overflow-y-auto { overflow: visible; }
  .shadow-sm { box-shadow: none; }
  .border { border: 1px solid #e2e8f0 !important; }
  .grid { display: block; }
  .col-span-8, .col-span-4 { width: 100%; margin-bottom: 1rem; }
}
</style>
@endpush

@endsection