@extends('layouts.app')

@section('header')
  <h1 class="text-xl font-semibold text-gray-800">Crear pedido</h1>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">

  {{-- Mensajes --}}
@if(session('ok'))
  <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-3 py-2 text-sm">
    {!! session('ok') !!} {{-- importante: escapar si no confiás en el contenido --}}
  </div>
@endif

  @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 text-red-800 px-3 py-2 text-sm">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  {{-- Dos columnas: IZQ productos + DER pedido --}}
  <div class="flex justify-start items-start gap-6">

    {{-- IZQUIERDA: Productos --}}
    <section class="shrink-0" style="width:640px; min-width:640px;">
      <div class="rounded-xl border border-slate-200/70 bg-white p-3"
           style="min-height: calc(100svh - 9rem);">
        @if($products->isEmpty())
          <div class="h-40 grid place-items-center text-sm text-slate-500">
            Aún no hay productos. Crea uno para empezar.
          </div>
        @else
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
  @foreach($products as $p)
    @livewire('product-card', ['orderId' => $order->id, 'productId' => $p->id], key('prod-'.$p->id))
  @endforeach
</div>


          {{-- Paginación --}}
          <div class="mt-4">
            {{ $products->links() }}
          </div>
        @endif
      </div>
    </section>

    {{-- DERECHA: Pedido en curso --}}
    <aside class="shrink-0 self-start" style="width:520px; min-width:520px;">
      <div class="sticky" style="top:6rem;">
@livewire('order-sidebar', ['orderId' => $order->id], key('sidebar-'.$order->id))
      </div>
    </aside>

  </div>
</div>
@endsection
