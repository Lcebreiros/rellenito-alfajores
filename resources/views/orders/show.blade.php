{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')

@section('header')
@php
$statusMap = [
    'draft'     => ['label'=>'Borrador',   'bg'=>'bg-amber-100 dark:bg-amber-900/40',   'text'=>'text-amber-700 dark:text-amber-300'],
    'pending'   => ['label'=>'Pendiente',  'bg'=>'bg-amber-100 dark:bg-amber-900/40',   'text'=>'text-amber-700 dark:text-amber-300'],
    'paid'      => ['label'=>'Pagado',     'bg'=>'bg-emerald-100 dark:bg-emerald-900/40','text'=>'text-emerald-700 dark:text-emerald-300'],
    'completed' => ['label'=>'Completado', 'bg'=>'bg-emerald-100 dark:bg-emerald-900/40','text'=>'text-emerald-700 dark:text-emerald-300'],
    'canceled'  => ['label'=>'Cancelado',  'bg'=>'bg-rose-100 dark:bg-rose-900/40',      'text'=>'text-rose-700 dark:text-rose-300'],
    'fulfilled' => ['label'=>'Entregado',  'bg'=>'bg-indigo-100 dark:bg-indigo-900/40',  'text'=>'text-indigo-700 dark:text-indigo-300'],
];

// Obtener la configuración del status actual (normalizando enum -> string)
if ($order->status instanceof \BackedEnum) {
    $statusKey = (string) $order->status->value;
} elseif ($order->status instanceof \UnitEnum) {
    $statusKey = (string) $order->status->name;
} else {
    $statusKey = is_string($order->status) ? $order->status : (string) ($order->status ?? '');
}
$s = $statusMap[$statusKey] ?? [
    'label' => ucfirst($statusKey ?: '—'),
    'bg'    => 'bg-neutral-100 dark:bg-neutral-800/60',
    'text'  => 'text-neutral-700 dark:text-neutral-200'
];
@endphp

<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
    <div>
        <h1 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">Venta #{{ $order->order_number ?? $order->id }}</h1>
        <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">{{ $order->created_at?->format('d/m/Y H:i') }}</p>
    </div>
    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium {{ $s['bg'] }} {{ $s['text'] }}">
        <svg class="w-2.5 h-2.5" viewBox="0 0 10 10" fill="currentColor" aria-hidden="true">
            <circle cx="5" cy="5" r="5"/>
        </svg>
        {{ $s['label'] }}
    </span>
</div>
@endsection


@section('content')
@php
  // ---------- Cliente / contacto ----------
  $client       = $order->client ?? null;
  $userOwner    = $order->user ?? null;

  $customerName  = $client->name
                    ?? $order->customer_name
                    ?? ($userOwner->name ?? 'Sin cliente');

  $customerEmail = $client->email
                    ?? $order->customer_email
                    ?? null;

  $customerPhone = $client->phone
                    ?? $order->customer_phone
                    ?? null;

  $shippingAddr  = $order->shipping_address
                    ?? ($client->address ?? null);

  // Soporta ambas convenciones: note / notes
  $notes         = $order->note ?? $order->notes ?? null;

  // ---------- Totales / items ----------
  $fmt = fn($n) => '$'.number_format((float)$n, 2, ',', '.');

  $items = $order->items ?? collect();
  $subtotalCalc = $items->sum(fn($it)=> (float)($it->unit_price ?? 0) * (int)($it->quantity ?? 0));
  $discount = (float)($order->discount_total ?? 0);
  $shipping = (float)($order->shipping_total ?? 0);
  $tax      = (float)($order->tax_total ?? 0);
  $subtotal = (float)($order->subtotal ?? $subtotalCalc);
  $total    = (float)($order->total ?? ($subtotal - $discount + $shipping + $tax));
  $totalItems = (int) ($items->sum('quantity') ?? 0);

  $voucherUrl = \Illuminate\Support\Facades\Route::has('orders.voucher') ? route('orders.voucher', $order) : null;
@endphp

<div class="space-y-6 lg:space-y-8">
{{-- Barra de acciones --}}
<div class="flex flex-wrap items-center justify-between gap-2 mb-6">

    {{-- Volver (izquierda) --}}
    <a href="{{ route('orders.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-neutral-200 dark:border-neutral-600
              text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-900 
              hover:bg-neutral-50 dark:hover:bg-neutral-800 transition text-sm">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
            <path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Volver
    </a>

    {{-- Acciones de estado (centrado) --}}
    @if($order->status === \App\Enums\OrderStatus::SCHEDULED)
      <div class="mx-auto flex items-center gap-2">
        <form action="{{ route('orders.confirm-scheduled', $order) }}" method="POST">
          @csrf
          <button type="submit"
                  class="flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-1 text-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Confirmar venta
          </button>
        </form>
        <form action="{{ route('orders.cancel-scheduled', $order) }}" method="POST">
          @csrf
          <button type="submit"
                  class="flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1 text-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Cancelar venta
          </button>
        </form>
      </div>
    @else
      <form action="{{ route('orders.cancel', $order) }}" method="POST" class="mx-auto">
          @csrf
          <button type="submit"
                  class="flex items-center gap-2 px-5 py-2 rounded-lg 
                         bg-red-600 text-white font-semibold tracking-wide
                         hover:bg-red-700 transition-colors duration-200 
                         focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                  <path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              Cancelar venta
          </button>
      </form>
    @endif

    {{-- Acciones secundarias (derecha) --}}
    <div class="flex flex-wrap gap-2 mt-2 sm:mt-0">

        @php
            $ticketUrl = Route::has('orders.ticket') ? route('orders.ticket', $order) : null;
        @endphp

        {{-- Ver comprobante --}}
        @if($ticketUrl)
        <a href="{{ $ticketUrl }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-indigo-200 dark:border-indigo-500/40
                  text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 
                  hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition text-sm">
            <img src="{{ asset('images/ticket.png') }}" alt="Ticket" class="w-4 h-4 dark:invert"/>
            Comprobante
        </a>
        @endif

        @php
            $canInvoice = Route::has('invoices.create');
        @endphp
        @if($canInvoice && empty($order->invoice_id))
        <a href="{{ route('invoices.create', ['order_id' => $order->id]) }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-emerald-200 dark:border-emerald-500/40
                  text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/10 
                  hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition text-sm"
        >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 4h11v16H4V4h5Z" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 9h6M9 13h6M9 17h3" stroke-linecap="round"/>
            </svg>
            Emitir factura
        </a>
        @endif

        {{-- Editar --}}
        @if(Route::has('orders.edit'))
        <a href="{{ route('orders.edit', $order) }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-indigo-200 dark:border-indigo-500/40
                  text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 
                  hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition text-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L8 18l-4 1 1-4 11.5-11.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Editar
        </a>
        @endif

        {{-- Imprimir --}}
        <button onclick="window.print()"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-indigo-200 dark:border-indigo-500/40
                       text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 
                       hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition text-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                <polyline points="6,9 6,2 18,2 18,9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <rect x="6" y="14" width="12" height="8" rx="1" stroke="currentColor" stroke-width="2"/>
            </svg>
            Imprimir
        </button>
    </div>
</div>

  {{-- Grid principal --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    {{-- Columna izquierda --}}
    <div class="lg:col-span-7 xl:col-span-8">
      <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm">
        {{-- Encabezado --}}
        <div class="px-5 sm:px-6 py-5 border-b border-neutral-100 dark:border-neutral-800/60 flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Detalle de la venta</h2>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $totalItems }} artículos</p>
          </div>
          <div class="text-right">
            <div class="text-2xl sm:text-3xl font-bold text-emerald-600">{{ $fmt($total) }}</div>
          </div>
        </div>

        {{-- Lista de productos --}}
        <div class="px-5 sm:px-6 py-4 lg:max-h-[48vh] lg:overflow-y-auto custom-scroll">
          @php
            $productItems = $items->filter(fn($i) => !is_null($i->product_id));
            $serviceItems = $items->filter(fn($i) => is_null($i->product_id) && !is_null($i->service_id));
          @endphp

          @if($productItems->count())
            <div class="text-xs uppercase tracking-wide text-neutral-500 dark:text-neutral-400 mb-2">Productos</div>
            @foreach ($productItems as $item)
              @php
                $name = $item->product->name ?? $item->name ?? 'Producto';
                $unit = (float)($item->unit_price ?? 0);
                $qty  = (int)($item->quantity ?? 0);
                $itemTotal = $unit * $qty;
              @endphp
              <div class="grid grid-cols-1 sm:grid-cols-12 items-center gap-3 py-3 border-b border-neutral-100 dark:border-neutral-800/60 last:border-0">
                <div class="sm:col-span-7 flex items-center gap-3 min-w-0">
                  @if (!empty($item->product?->image_url))
                    <img class="w-12 h-12 rounded-lg object-cover border border-neutral-200 dark:border-neutral-800" src="{{ $item->product->image_url }}" alt="">
                  @else
                    <div class="w-12 h-12 rounded-lg bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-800 grid place-items-center text-neutral-400">
                      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/></svg>
                    </div>
                  @endif
                  <div class="min-w-0">
                    <div class="font-medium text-neutral-800 dark:text-neutral-100 truncate">{{ $name }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $fmt($unit) }} · Unidad</div>
                  </div>
                </div>
                <div class="sm:col-span-2 text-neutral-600 dark:text-neutral-300 text-sm">x {{ $qty }}</div>
                <div class="sm:col-span-3 text-right font-semibold text-neutral-900 dark:text-neutral-100">{{ $fmt($itemTotal) }}</div>
              </div>
            @endforeach
          @endif

          @if($serviceItems->count())
            <div class="text-xs uppercase tracking-wide text-neutral-500 dark:text-neutral-400 mt-4 mb-2">Servicios</div>
            @foreach ($serviceItems as $item)
              @php
                $name = $item->service->name ?? $item->name ?? 'Servicio';
                $unit = (float)($item->unit_price ?? 0);
                $qty  = (int)($item->quantity ?? 0);
                $itemTotal = $unit * $qty;
              @endphp
              <div class="grid grid-cols-1 sm:grid-cols-12 items-center gap-3 py-3 border-b border-neutral-100 dark:border-neutral-800/60 last:border-0">
                <div class="sm:col-span-7 flex items-center gap-3 min-w-0">
                  <div class="w-12 h-12 rounded-lg bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-800 grid place-items-center text-neutral-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/></svg>
                  </div>
                  <div class="min-w-0">
                    <div class="font-medium text-neutral-800 dark:text-neutral-100 truncate">{{ $name }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $fmt($unit) }} · Servicio</div>
                  </div>
                </div>
                <div class="sm:col-span-2 text-neutral-600 dark:text-neutral-300 text-sm">x {{ $qty }}</div>
                <div class="sm:col-span-3 text-right font-semibold text-neutral-900 dark:text-neutral-100">{{ $fmt($itemTotal) }}</div>
              </div>
            @endforeach
          @endif

          @if($productItems->count() === 0 && $serviceItems->count() === 0)
            <div class="py-14 text-center">
              <svg class="w-12 h-12 mx-auto mb-3 text-neutral-300 dark:text-neutral-600" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                <path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="1.5"/>
              </svg>
              <p class="text-neutral-500 dark:text-neutral-400">No hay ítems en esta venta</p>
            </div>
          @endif
        </div>

        {{-- Totales --}}
        @if($items->count() > 0)
          <div class="px-5 sm:px-6 py-5 bg-neutral-50 dark:bg-neutral-950/40 border-t border-neutral-100 dark:border-neutral-800/60">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <div class="flex items-center justify-between">
                <span class="text-neutral-600 dark:text-neutral-300">Subtotal</span>
                <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $fmt($subtotal) }}</span>
              </div>
              @if($discount > 0)
                <div class="flex items-center justify-between">
                  <span class="text-emerald-600 dark:text-emerald-300">Descuento</span>
                  <span class="font-medium text-emerald-600 dark:text-emerald-300">-{{ $fmt($discount) }}</span>
                </div>
              @endif
              @if($shipping > 0)
                <div class="flex items-center justify-between">
                  <span class="text-neutral-600 dark:text-neutral-300">Envío</span>
                  <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $fmt($shipping) }}</span>
                </div>
              @endif
              @if($tax > 0)
                <div class="flex items-center justify-between relative group">
                  <span class="text-neutral-600 dark:text-neutral-300">IVA</span>
                  <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $fmt($tax) }}</span>
                  <div class="hidden group-hover:flex absolute right-0 top-full mt-2 z-30 px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white/95 dark:bg-neutral-900/95 shadow-xl shadow-black/10 backdrop-blur-md min-w-[220px] text-xs text-neutral-700 dark:text-neutral-200">
                    <div class="w-full">
                      <p class="font-semibold mb-1 text-neutral-900 dark:text-neutral-100">Detalle de impuestos</p>
                      <div class="flex items-center justify-between text-neutral-600 dark:text-neutral-300">
                        <span>IVA</span>
                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ $fmt($tax) }}</span>
                      </div>
                      @if($shipping > 0)
                      <div class="flex items-center justify-between text-neutral-600 dark:text-neutral-300">
                        <span>Base + envío</span>
                        <span>{{ $fmt($subtotal - $discount + $shipping) }}</span>
                      </div>
                      @endif
                    </div>
                  </div>
                </div>
              @endif
              <div class="sm:col-span-2 flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-neutral-800/60">
                <span class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Total</span>
                <span class="text-lg sm:text-xl font-bold text-emerald-600">{{ $fmt($total) }}</span>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>

    {{-- Columna derecha --}}
    <div class="lg:col-span-5 xl:col-span-4 space-y-6">
      {{-- Agendamiento --}}
      @if($order->is_scheduled && $order->scheduled_for)
      <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm">
        <div class="px-5 sm:px-6 py-5 border-b border-neutral-100 dark:border-neutral-800/60">
          <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Agendamiento</h3>
        </div>
        <div class="px-5 sm:px-6 py-5">
          <div class="flex items-start gap-3">
            <div class="shrink-0">
              <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <div class="flex-1">
              <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Agendado para</div>
              <div class="font-semibold text-neutral-900 dark:text-neutral-100">
                {{ $order->scheduled_for->format('d/m/Y') }}
              </div>
              <div class="text-sm text-neutral-600 dark:text-neutral-300 mt-0.5">
                {{ $order->scheduled_for->format('H:i') }} hs
              </div>
              <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                {{ $order->scheduled_for->diffForHumans() }}
              </div>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-800/60">
            <a href="{{ route('orders.edit', $order) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
              Cambiar fecha →
            </a>
          </div>
        </div>
      </div>
      @endif
      {{-- Cliente --}}
      <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm">
        <div class="px-5 sm:px-6 py-5 border-b border-neutral-100 dark:border-neutral-800/60 flex items-center justify-between">
          <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Cliente</h3>

          @if(!$order->client && \Illuminate\Support\Facades\Route::has('clients.index'))
            <a href="{{ route('clients.index') }}"
               class="text-xs px-2 py-1 rounded-md bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-200 dark:hover:bg-neutral-700">
              Asignar cliente
            </a>
          @endif
        </div>

        <div class="px-5 sm:px-6 py-5 space-y-4">
          <div>
            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Nombre</div>
            <div class="font-medium text-neutral-900 dark:text-neutral-100 text-[15px]">
              {{ $customerName ?: 'Sin cliente' }}
            </div>
          </div>

          @if($customerEmail)
            <div class="flex items-center justify-between gap-3">
              <div>
                <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Email</div>
                <div class="font-medium text-neutral-700 dark:text-neutral-200 text-sm break-all">{{ $customerEmail }}</div>
              </div>
              <a href="mailto:{{ $customerEmail }}" class="shrink-0 inline-flex items-center px-2 py-1.5 rounded-md bg-indigo-600 text-white text-xs hover:bg-indigo-700">
                Enviar
              </a>
            </div>
          @endif

          @if($customerPhone)
            <div class="flex items-center justify-between gap-3">
              <div>
                <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Teléfono</div>
                <div class="font-medium text-neutral-700 dark:text-neutral-200">{{ $customerPhone }}</div>
              </div>
              <a href="tel:{{ preg_replace('/\s+/', '', $customerPhone) }}" class="shrink-0 inline-flex items-center px-2 py-1.5 rounded-md bg-emerald-600 text-white text-xs hover:bg-emerald-700">
                Llamar
              </a>
            </div>
          @endif

          @if($shippingAddr)
            <div>
              <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Dirección</div>
              <div class="font-medium text-neutral-700 dark:text-neutral-200 text-sm leading-relaxed">{{ $shippingAddr }}</div>
            </div>
          @endif

          @if($notes)
            <div class="pt-2">
              <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Notas</div>
              <div class="text-neutral-700 dark:text-neutral-200 bg-neutral-50 dark:bg-neutral-950/40 border border-neutral-100 dark:border-neutral-800/60 rounded-lg p-3 text-sm leading-relaxed">
                {{ \Illuminate\Support\Str::limit($notes, 200) }}
              </div>
            </div>
          @endif

          <div class="pt-2 border-t border-neutral-100 dark:border-neutral-800/60">
            <div class="text-xs text-neutral-500 dark:text-neutral-400">Actualizado</div>
            <div class="text-sm text-neutral-700 dark:text-neutral-200">{{ $order->updated_at?->format('d/m/Y H:i') }}</div>
          </div>
        </div>
      </div>

      {{-- Métodos de pago --}}
      @if($order->paymentMethods && $order->paymentMethods->isNotEmpty())
        <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm">
          <div class="px-5 sm:px-6 py-5 border-b border-neutral-100 dark:border-neutral-800/60">
            <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Métodos de Pago</h3>
          </div>

          <div class="px-5 sm:px-6 py-5 space-y-3">
            @foreach($order->paymentMethods as $pm)
              <div class="flex items-center gap-3 p-3 rounded-lg bg-neutral-50 dark:bg-neutral-950/40 border border-neutral-100 dark:border-neutral-800/60">
                @if($pm->icon)
                  <x-dynamic-component :component="'heroicon-o-' . $pm->getIcon()" class="w-6 h-6 text-indigo-600 dark:text-indigo-400 shrink-0" />
                @endif
                <div class="flex-1 min-w-0">
                  <div class="font-medium text-neutral-900 dark:text-neutral-100 text-sm">{{ $pm->name }}</div>
                  @if($pm->pivot->amount > 0)
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Monto: {{ $fmt($pm->pivot->amount) }}</div>
                  @endif
                  @if($pm->pivot->reference)
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 font-mono">Ref: {{ $pm->pivot->reference }}</div>
                  @endif
                </div>
                @if($pm->requires_gateway)
                  <span class="shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                    <x-heroicon-s-link class="w-3 h-3" /> API
                  </span>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Resumen mini (mobile) --}}
      <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm lg:hidden">
        <div class="px-5 py-4 grid grid-cols-2 gap-3 text-sm">
          <div class="rounded-lg bg-neutral-50 dark:bg-neutral-950/40 border border-neutral-100 dark:border-neutral-800/60 p-3">
            <div class="text-neutral-500 dark:text-neutral-400 text-xs">Artículos</div>
            <div class="font-semibold text-neutral-900 dark:text-neutral-100 text-lg">{{ $totalItems }}</div>
          </div>
          <div class="rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 p-3">
            <div class="text-emerald-600 dark:text-emerald-300 text-xs">Total</div>
            <div class="font-bold text-emerald-600 text-lg">{{ $fmt($total) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('head')
<style>
/* Scrollbar sutil (light & dark) */
.custom-scroll::-webkit-scrollbar{ width:6px; }
.custom-scroll::-webkit-scrollbar-track{ background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb{ background:#d4d4d4; border-radius:3px; }        /* neutral-300 */
.dark .custom-scroll::-webkit-scrollbar-thumb{ background:#52525b; }                     /* zinc-600 */

/* Print: fondo claro y sin sombras */
@media print {
  header, nav, .print\:hidden { display:none !important; }
  body { background:#fff !important; color:#000 !important; }
  .shadow-sm, .shadow, .shadow-lg { box-shadow:none !important; }
  .border { border-color:#e5e5e5 !important; } /* neutral-200 */
}
</style>
@endpush
@push('scripts')
<script>
  // Mostrar/ocultar selector según switch
  document.addEventListener('change', function(e){
    if (e.target && e.target.name === 'is_scheduled') {
      const form = e.target.closest('form');
      const box  = form ? form.querySelector('[data-schedule-container]') : null;
      if (box) box.style.display = e.target.checked ? 'block' : 'none';
    }
  });
  // Si el navegador rehidrata con back/forward, forzar estado correcto
  window.addEventListener('pageshow', function(){
    document.querySelectorAll('form input[name="is_scheduled"]').forEach(function(chk){
      const box = chk.closest('form').querySelector('[data-schedule-container]');
      if (box) box.style.display = chk.checked ? 'block' : 'none';
    });
  });
</script>
@endpush
@endsection
