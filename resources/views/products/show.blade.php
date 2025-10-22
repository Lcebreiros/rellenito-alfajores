@extends('layouts.app')

@section('header')
<h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">
    Detalle de Producto
</h1>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-6">

    <div class="bg-white dark:bg-neutral-900 shadow rounded-2xl overflow-hidden">
        <div class="md:flex">
            {{-- Imagen --}}
            <div class="md:w-1/3 bg-neutral-100 dark:bg-neutral-800 p-4 flex items-center justify-center">
                @if($product->image)
                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="h-48 w-full object-cover rounded-lg">
                @else
                    <div class="h-48 w-full flex items-center justify-center bg-neutral-200 dark:bg-neutral-700 rounded-lg">
                        <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-300" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M7 15l3-3 3 3 4-4 2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Información --}}
            <div class="md:w-2/3 p-6 space-y-4">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $product->name }}</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">SKU: {{ $product->sku }}</p>
                <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Precio: ${{ number_format($product->price, 2, ',', '.') }}</p>
                <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ $product->description ?? '-' }}</p>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Categoría: {{ $product->category ?? '-' }}</p>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Estado: 
                    <span class="{{ $product->is_active ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </p>

                {{-- Stock general --}}
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Stock total: 
                        <span class="font-bold text-neutral-900 dark:text-neutral-100">{{ $totalStock }}</span>
                    </h3>
                </div>

                {{-- Stock por sucursal --}}
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Stock por sucursal:</h3>
                    @if($locations->count())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($locations as $loc)
                                <div class="flex justify-between p-2 bg-neutral-50 dark:bg-neutral-800 rounded-lg text-sm">
                                    <span>{{ $loc->branch->name ?? 'Sucursal ' . $loc->branch_id }}</span>
                                    <span class="{{ $loc->stock > 0 ? 'text-blue-600 dark:text-blue-300' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $loc->stock }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No hay stock en sucursales.</p>
                    @endif
                </div>

                {{-- Botones --}}
                <div class="mt-6 flex gap-2">
                    <a href="{{ route('products.index') }}" class="px-4 py-2 bg-neutral-200 dark:bg-neutral-800 rounded-lg text-sm text-neutral-700 dark:text-neutral-200 hover:bg-neutral-300 dark:hover:bg-neutral-700">
                        Volver
                    </a>
                    <a href="{{ route('products.edit', $product) }}" class="px-4 py-2 bg-indigo-600 rounded-lg text-sm text-white hover:bg-indigo-700">
                        Editar
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
