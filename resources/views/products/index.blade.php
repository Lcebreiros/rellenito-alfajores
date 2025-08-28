{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')

@section('header')
  <h1 class="text-xl font-semibold text-gray-800">Productos</h1>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6">

  {{-- Mensajes --}}
  @if(session('ok'))
    <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-3 py-2 text-sm">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 text-red-800 px-3 py-2 text-sm">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  {{-- Barra de acciones (buscar opcional) --}}
  <div class="mb-4 flex items-center justify-between gap-3">
    <div class="text-sm text-gray-600">
      Mostrando {{ $products->firstItem() }}–{{ $products->lastItem() }} de {{ $products->total() }}
    </div>
    <a href="{{ route('products.create') }}"
       class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white text-sm hover:bg-indigo-700">
      + Nuevo producto
    </a>
  </div>

  @if($products->count())
    <div class="grid gap-4 sm:gap-6 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4">
      @foreach($products as $product)
        @php
          // Si más adelante agregás "photo_path" en la tabla, esto lo tomará.
          $photo = isset($product->photo_path) && $product->photo_path
            ? \Illuminate\Support\Facades\Storage::url($product->photo_path)
            : null;

          $priceLabel = '$ ' . number_format((float) $product->price, 2, ',', '.');
        @endphp

        <div class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition">
          {{-- Imagen (opcional) --}}
          <div class="aspect-[4/3] bg-gray-50 flex items-center justify-center overflow-hidden">
            @if($photo)
              <img src="{{ $photo }}" alt="Foto de {{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
            @else
              <svg class="w-16 h-16 text-gray-300" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14l-4.5-3.5L14 18l-4-3-5 4V5Z"
                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            @endif
          </div>

          {{-- Info --}}
          <div class="p-3 sm:p-4">
            <div class="flex items-start justify-between gap-2">
              <div>
                <h2 class="text-sm sm:text-base font-medium text-gray-900 line-clamp-2">
                  {{ $product->name }}
                </h2>
                <div class="mt-0.5 text-[11px] text-gray-500">SKU: {{ $product->sku }}</div>
              </div>

              {{-- Estado + Stock --}}
              <div class="flex flex-col items-end gap-1">
                <span class="text-[11px] sm:text-xs rounded-full px-2 py-0.5
                  {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                  {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                </span>
                <span class="text-[11px] sm:text-xs rounded-full px-2 py-0.5
                  {{ $product->stock > 0 ? 'bg-blue-50 text-blue-700' : 'bg-rose-50 text-rose-700' }}">
                  {{ $product->stock > 0 ? "Stock: {$product->stock}" : 'Sin stock' }}
                </span>
              </div>
            </div>

            <div class="mt-2 sm:mt-3 flex items-center justify-between">
              <span class="text-base sm:text-lg font-semibold text-gray-900">{{ $priceLabel }}</span>

              <div class="flex items-center gap-2">
                <a href="{{ route('products.edit', $product) }}"
                   class="text-xs sm:text-sm rounded-lg border px-3 py-1.5 border-gray-300 text-gray-700 hover:bg-gray-50">
                  Editar
                </a>
                {{-- Si tenés ruta para actualizar stock rápido, podés agregar un botón/modal aquí --}}
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Paginación --}}
    <div class="mt-6">
      {{ $products->links() }}
    </div>
  @else
    <div class="text-center py-16">
      <p class="text-gray-600">Aún no hay productos.</p>
    </div>
  @endif
</div>
@endsection
