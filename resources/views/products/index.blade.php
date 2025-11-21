{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Productos</h1>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6">

  {{-- Mensajes --}}
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm
                dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm
                dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  {{-- Barra de acciones --}}
  <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center gap-2 text-sm">
      <span class="inline-flex items-center rounded-lg border border-neutral-200 bg-white px-2.5 py-1.5 text-neutral-600
                    dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300">
        Mostrando {{ $products->firstItem() }}–{{ $products->lastItem() }} de {{ $products->total() }}
      </span>
    </div>

    <div class="flex items-center gap-2">
      {{-- Componente de escaneo/ingreso por código --}}
      <x-barcode-scanner />

      {{-- (Opcional) Buscador rápido --}}
      <form method="GET" class="hidden sm:flex items-center gap-2">
        <div class="relative">
          <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
            <path d="M21 21l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <input name="q" value="{{ request('q') }}" placeholder="Buscar producto / SKU…"
                 class="w-60 rounded-lg border border-neutral-300 bg-white pl-9 pr-3 py-2 text-sm text-neutral-700
                        placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:placeholder:text-neutral-400">
        </div>
        @if(isset($authUser) && method_exists($authUser,'isMaster') && $authUser->isMaster())
          <input type="number" name="user_id" value="{{ request('user_id') }}" min="1" placeholder="User ID"
                 class="w-32 rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-700
                        placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200" />
        @endif
        <button class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
          Buscar
        </button>
      </form>

      

      <a href="{{ route('products.create') }}"
         class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Nuevo producto
      </a>
    </div>
  </div>


  @if($products->count())
    <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      @foreach($products as $product)
        @php
          $image = null;
          if (!empty($product->image)) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)) {
              $image = \Illuminate\Support\Facades\Storage::url($product->image);
            }
          }
          $priceLabel = '$ ' . number_format((float) $product->price, 2, ',', '.');
          $isActive = (bool)($product->is_active ?? true);
          // Replicar criterio de ProductCard: usar siempre products.stock
          $stock = (int)($product->stock ?? 0);
        @endphp


        <div class="group overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm
                    hover:shadow-md hover:border-indigo-200 transition
                    dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-indigo-500/50">

          {{-- Imagen --}}
          <div class="relative aspect-[4/3] bg-neutral-100 dark:bg-neutral-800">
            @if($image)
              <img src="{{ $image }}" alt="Foto de {{ $product->name }}"
                   class="h-full w-full object-cover">
            @else
              <div class="absolute inset-0 grid place-items-center">
                <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-300" viewBox="0 0 24 24" fill="none">
                  <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
                  <path d="M7 15l3-3 3 3 4-4 2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            @endif

            {{-- Badge estado sobre imagen --}}
            <div class="absolute left-3 top-3 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium
                        {{ $isActive 
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' 
                            : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
              {{ $isActive ? 'Activo' : 'Inactivo' }}
            </div>
          </div>

          {{-- Info --}}
          <div class="p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h2 class="text-sm sm:text-base font-medium text-neutral-900 dark:text-neutral-100 line-clamp-2">
                  {{ $product->name }}
                </h2>
                <div class="mt-0.5 text-[11px] text-neutral-500 dark:text-neutral-400">SKU: {{ $product->sku }}</div>
                @if(isset($authUser) && method_exists($authUser,'isMaster') && $authUser->isMaster())
                  @php
                    $owner = $product->user;
                    $companyName = $product->company?->name;
                    $chain = null;
                    if ($owner && $owner->representable_type === \App\Models\Branch::class) {
                        // Admin de sucursal: empresa -> sucursal
                        $branchName = optional($owner->representable)->name;
                        $chain = trim(($companyName ?: 'Empresa') . ' → ' . ($branchName ?: 'Sucursal'));
                    } elseif ($owner && method_exists($owner,'isCompany') && $owner->isCompany()) {
                        $chain = $owner->name;
                    } else {
                        // Usuario regular o datos incompletos: mostrar empresa si está
                        $chain = $companyName ?: ($owner?->name ?? 'N/D');
                    }
                  @endphp
                  <div class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">
                    Usuario: #{{ $product->user_id }} — {{ $product->user?->name ?? 'N/D' }}
                    @if(!empty($chain))
                      <span class="ml-1 text-neutral-400">({{ $chain }})</span>
                    @endif
                  </div>
                @endif
              </div>

              {{-- Stock --}}
              <span class="shrink-0 text-[11px] sm:text-xs rounded-full px-2 py-0.5 font-medium
                           {{ $stock > 0 
                              ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' 
                              : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' }}">
                {{ $stock > 0 ? "Stock: {$stock}" : 'Sin stock' }}
              </span>
            </div>

<div class="mt-3 flex flex-wrap items-center justify-between gap-2">
    <span class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
        {{ $priceLabel }}
    </span>

    <div class="flex flex-wrap items-center gap-2 mt-2 sm:mt-0">
        {{-- Botón Editar --}}
        <a href="{{ route('products.edit', $product) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-1.5 text-sm font-medium text-neutral-700 hover:bg-neutral-50
                  dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition-colors whitespace-nowrap">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
            <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Editar
        </a>

        {{-- Botón Detalles --}}
        <a href="{{ route('products.show', $product) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-1.5 text-sm font-medium text-neutral-700 hover:bg-neutral-50
                  dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition-colors whitespace-nowrap">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
            <path d="M12 8v4l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Detalles
        </a>
    </div>
</div>

          </div>
        </div>
      @endforeach
    </div>

    {{-- Paginación --}}
    <div class="mt-6">
      {{ $products->withQueryString()->links() }}
    </div>
  @else
    <x-empty-state
      icon="box"
      title="No hay productos aún"
      description="Comienza agregando tu primer producto al inventario para gestionar tus ventas."
      :action-url="route('products.create')"
      action-text="Crear primer producto"
      action-icon="plus"
    />
  @endif
</div>
@endsection
