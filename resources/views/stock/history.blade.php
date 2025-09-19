{{-- resources/views/stock/history.blade.php --}}
@extends('layouts.app')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
  <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center">
    <i class="fas fa-clock-rotate-left text-indigo-600 dark:text-indigo-400 mr-3"></i> Historial de Stock
  </h1>

  <div class="flex gap-2 mt-3 sm:mt-0">
    <button onclick="window.print()"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors
                   bg-white border border-gray-300 text-gray-700 hover:bg-gray-50
                   dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700">
      <i class="fas fa-print"></i> <span>Imprimir</span>
    </button>

    {{-- Botón de Volver al Reporte --}}
<a href="{{ url()->previous() }}"
   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors
          bg-gray-100 hover:bg-gray-200 dark:bg-neutral-800 dark:hover:bg-neutral-700
          text-gray-800 dark:text-neutral-100">
    <!-- Icono de flecha izquierda -->
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
    </svg>
    <span>Volver</span>
</a>

  </div>
</div>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">

  {{-- Estadísticas del historial --}}
  @if(!$stockHistory->isEmpty())
    @php
      $totalMovements = $stockHistory->total();
      $increasesCount = $stockHistory->getCollection()->where('quantity_change', '>', 0)->count();
      $decreasesCount = $stockHistory->getCollection()->where('quantity_change', '<', 0)->count();
      $totalIncrease = $stockHistory->getCollection()->where('quantity_change', '>', 0)->sum('quantity_change');
      $totalDecrease = abs($stockHistory->getCollection()->where('quantity_change', '<', 0)->sum('quantity_change'));
    @endphp
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
      <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500 dark:text-neutral-400">Movimientos</p>
            <p class="text-xl font-bold text-gray-900 dark:text-neutral-100">{{ number_format($totalMovements) }}</p>
          </div>
          <div class="rounded-lg bg-indigo-50 dark:bg-indigo-500/10 p-2">
            <i class="fas fa-arrows-rotate text-indigo-600 dark:text-indigo-300"></i>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500 dark:text-neutral-400">Ingresos</p>
            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">+{{ number_format($totalIncrease) }}</p>
          </div>
          <div class="rounded-lg bg-emerald-50 dark:bg-emerald-500/10 p-2">
            <i class="fas fa-plus text-emerald-600 dark:text-emerald-300"></i>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500 dark:text-neutral-400">Salidas</p>
            <p class="text-xl font-bold text-rose-600 dark:text-rose-400">-{{ number_format($totalDecrease) }}</p>
          </div>
          <div class="rounded-lg bg-rose-50 dark:bg-rose-500/10 p-2">
            <i class="fas fa-minus text-rose-600 dark:text-rose-300"></i>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-gray-500 dark:text-neutral-400">Balance neto</p>
            @php $netBalance = $totalIncrease - $totalDecrease; @endphp
            <p class="text-xl font-bold {{ $netBalance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
              {{ $netBalance >= 0 ? '+' : '' }}{{ number_format($netBalance) }}
            </p>
          </div>
          <div class="rounded-lg {{ $netBalance >= 0 ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-rose-50 dark:bg-rose-500/10' }} p-2">
            <i class="fas {{ $netBalance >= 0 ? 'fa-trending-up text-emerald-600 dark:text-emerald-300' : 'fa-trending-down text-rose-600 dark:text-rose-300' }}"></i>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- Filtros y búsqueda --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm p-4 mb-6 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10">
    <div class="flex flex-wrap items-center gap-2 mb-3">
      <span class="text-sm font-medium text-gray-700 dark:text-neutral-300 flex items-center py-2">
        <i class="fas fa-filter text-gray-500 dark:text-neutral-400 mr-2"></i> Tipo:
      </span>

      @php $currentType = request('type',''); @endphp
      @foreach([''=>'Todos','increase'=>'Ingresos','decrease'=>'Salidas'] as $key=>$label)
        @php $is = $currentType===$key; @endphp
        <a href="{{ request()->fullUrlWithQuery(['type' => $key ?: null, 'page'=>null]) }}"
           class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium
                  {{ $is
                      ? 'bg-indigo-600 text-white'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700' }}">
          {{ $label }}
        </a>
      @endforeach

      <div class="ml-auto w-full sm:w-72">
        <form method="GET" class="flex">
          <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-neutral-500 text-sm"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar producto, SKU o razón…"
                   class="w-full pl-9 pr-3 py-2 rounded-lg text-sm
                          border-gray-300 focus:border-indigo-500 focus:ring-indigo-500
                          dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-100 dark:placeholder-neutral-400">
          </div>
          {{-- mantener filtros --}}
          <input type="hidden" name="type" value="{{ request('type') }}">
          <input type="hidden" name="order_by" value="{{ request('order_by','created_at') }}">
          <input type="hidden" name="dir" value="{{ request('dir','desc') }}">
          <button class="ml-2 px-3 py-2 rounded-lg text-sm transition-colors
                         bg-indigo-600 text-white hover:bg-indigo-700">
            Buscar
          </button>
        </form>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <span class="text-xs text-gray-500 dark:text-neutral-400">Ordenar:</span>

      @php
        $isFecha    = request('order_by','created_at')==='created_at';
        $isProducto = request('order_by')==='product_name';
        $isCantidad = request('order_by')==='quantity_change';
      @endphp

      <a href="{{ request()->fullUrlWithQuery(['order_by'=>'created_at','dir'=> ($isFecha && request('dir','desc')==='desc') ? 'asc':'desc','page'=>null]) }}"
         class="px-2 py-1.5 rounded-lg text-xs border
                {{ $isFecha
                    ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300'
                    : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700' }}">
        Fecha {{ $isFecha ? (request('dir','desc')==='desc'?'↓':'↑') : '' }}
      </a>

      <a href="{{ request()->fullUrlWithQuery(['order_by'=>'product_name','dir'=> ($isProducto && request('dir','desc')==='desc') ? 'asc':'desc','page'=>null]) }}"
         class="px-2 py-1.5 rounded-lg text-xs border
                {{ $isProducto
                    ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300'
                    : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700' }}">
        Producto {{ $isProducto ? (request('dir','desc')==='desc'?'↓':'↑') : '' }}
      </a>

      <a href="{{ request()->fullUrlWithQuery(['order_by'=>'quantity_change','dir'=> ($isCantidad && request('dir','desc')==='desc') ? 'asc':'desc','page'=>null]) }}"
         class="px-2 py-1.5 rounded-lg text-xs border
                {{ $isCantidad
                    ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300'
                    : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700' }}">
        Cantidad {{ $isCantidad ? (request('dir','desc')==='desc'?'↓':'↑') : '' }}
      </a>

      <a href="{{ route('stock.history') }}"
         class="ml-auto inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs
                bg-gray-100 text-gray-700 hover:bg-gray-200
                dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700">
        <i class="fas fa-eraser mr-1"></i> Limpiar
      </a>
    </div>
  </div>

  {{-- Lista de movimientos --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 p-4">
    @if($stockHistory->count() === 0)
      <div class="py-16 text-center">
        <i class="fas fa-clock text-gray-300 dark:text-neutral-600 text-5xl mb-3"></i>
        <div class="text-gray-600 dark:text-neutral-300">No hay movimientos de stock para mostrar.</div>
      </div>
    @else
      <div class="space-y-3">
        @foreach($stockHistory as $h)
          @php
            $change = $h->quantity_change;
            if ($change === 0) continue;

            $badgeColor = $change > 0
                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300';

            $icon = $change > 0 ? 'fa-plus' : 'fa-minus';
            $changeText = $change > 0 ? "+{$change}" : $change;
          @endphp

          <div class="rounded-xl border border-gray-200 dark:border-neutral-700 p-4 hover:shadow-sm transition-all bg-white dark:bg-neutral-900">
            <div class="flex items-start gap-4">
              
              {{-- Icono de movimiento --}}
              <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full {{ $badgeColor }}">
                <i class="fas {{ $icon }}"></i>
              </div>

              {{-- Información principal --}}
              <div class="flex-1 min-w-0">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2">
                  
                  {{-- Producto --}}
                  <div class="min-w-0 flex-1">
                    <div class="font-semibold text-gray-900 dark:text-neutral-100 truncate">
                      {{ $h->product->name }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-neutral-400 truncate">
                      @if($h->product->sku) SKU: {{ $h->product->sku }} @endif
                    </div>
                  </div>

                  {{-- Detalles del movimiento --}}
                  <div class="flex flex-wrap items-center gap-3 text-sm">
                    <div class="flex items-center gap-1">
                      <span class="text-gray-500 dark:text-neutral-400">Cambio:</span>
                      <span class="font-semibold {{ $change > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ $changeText }}
                      </span>
                    </div>
                    
                    <div class="flex items-center gap-1">
                      <span class="text-gray-500 dark:text-neutral-400">Stock final:</span>
                      <span class="font-semibold text-gray-900 dark:text-neutral-100">{{ $h->new_stock }}</span>
                    </div>

                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $badgeColor }}">
                      {{ $h->reason }}
                    </span>

                    <div class="flex items-center text-gray-500 dark:text-neutral-400 text-xs">
                      <i class="fas fa-calendar-alt mr-1"></i>
                      {{ $h->created_at->format('d/m/Y H:i') }}
                    </div>
                  </div>
                </div>

                {{-- Información adicional si existe --}}
                @if($h->notes)
                  <div class="mt-2 text-sm text-gray-600 dark:text-neutral-300 bg-gray-50 dark:bg-neutral-800/60 rounded-lg p-2">
                    <i class="fas fa-sticky-note mr-1 text-gray-400 dark:text-neutral-500"></i>
                    {{ $h->notes }}
                  </div>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Paginación --}}
  @if($stockHistory->hasPages())
    <div class="mt-6 print:hidden">
      {{ $stockHistory->withQueryString()->links() }}
    </div>
  @endif
</div>

{{-- Iconos --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

{{-- Print styles --}}
<style>
@media print {
  header, nav, .print\:hidden, form { display: none !important; }
  body { background: #fff !important; color: #000 !important; }
  a { color: #000 !important; text-decoration: none !important; }
  .dark * { color: #000 !important; background: #fff !important; }
}
</style>
@endsection