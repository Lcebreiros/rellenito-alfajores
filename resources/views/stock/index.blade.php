@extends('layouts.app')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
  <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex flex-wrap items-center gap-2">
    <i class="fas fa-boxes-stacked text-indigo-600 dark:text-indigo-400"></i>
    <span>Reporte de Stock</span>

    @if($branchId && !empty($availableBranches))
      @php
        $currentBranch = collect($availableBranches)->firstWhere('id', $branchId);
      @endphp
      @if($currentBranch)
        <span class="text-sm font-normal text-gray-500 dark:text-neutral-400">
          - {{ $currentBranch['name'] }}
        </span>
        @if(!empty($branchUsesCompanyInventory))
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
            <i class="fas fa-building"></i> Inventario de empresa
          </span>
        @endif
      @endif
    @elseif($isCompanyView)
      <span class="text-sm font-normal text-gray-500 dark:text-neutral-400">- Vista Consolidada</span>
    @elseif($isMasterView ?? false)
      <span class="text-sm font-normal text-gray-500 dark:text-neutral-400">- Vista Global</span>
    @endif
  </h1>

  <div class="flex items-center gap-2">
    {{-- Selector de Sucursal --}}
    @include('stock.partials.branch-selector', [
      'availableBranches' => $availableBranches ?? [],
      'branchId' => $branchId ?? null,
      'isCompanyView' => $isCompanyView ?? false
    ])

    {{-- Separador --}}
    <div class="hidden sm:block w-px h-6 bg-neutral-200 dark:bg-neutral-700 mx-1"></div>

    {{-- Utilidades: icon-only --}}
    <button type="button"
            id="openNotificationSettingsBtn"
            aria-label="Configurar notificaciones"
            title="Configurar notificaciones"
            class="inline-flex items-center justify-center w-9 h-9 rounded-lg
                   text-neutral-500 dark:text-neutral-400
                   hover:bg-neutral-100 dark:hover:bg-neutral-800
                   hover:text-neutral-700 dark:hover:text-neutral-200
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                   transition-colors">
      <i class="fa-solid fa-bell text-sm"></i>
    </button>

    <button type="button"
            onclick="window.print()"
            aria-label="Imprimir reporte"
            title="Imprimir reporte"
            class="inline-flex items-center justify-center w-9 h-9 rounded-lg
                   text-neutral-500 dark:text-neutral-400
                   hover:bg-neutral-100 dark:hover:bg-neutral-800
                   hover:text-neutral-700 dark:hover:text-neutral-200
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                   transition-colors">
      <i class="fas fa-print text-sm"></i>
    </button>

    {{-- Separador --}}
    <div class="hidden sm:block w-px h-6 bg-neutral-200 dark:bg-neutral-700 mx-1"></div>

    {{-- Historial: secundario --}}
    <a href="{{ route('stock.history') }}"
       aria-label="Ver historial de stock"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors
              border border-neutral-300 dark:border-neutral-600
              text-neutral-700 dark:text-neutral-200
              hover:bg-neutral-50 dark:hover:bg-neutral-800
              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
       <i class="fas fa-clock text-xs"></i>
       <span>Historial</span>
    </a>

    {{-- Descargar: CTA primario --}}
    <button type="button"
            id="downloadReportBtn"
            aria-label="Descargar reporte"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm
                   bg-green-600 text-white font-semibold hover:bg-green-700
                   focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2
                   transition-colors shadow-sm">
      <i class="fa-solid fa-download text-xs"></i>
      <span>Descargar</span>
    </button>
  </div>
</div>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">

  {{-- Mensajes de éxito --}}
  @if(session('ok'))
    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 text-green-800 px-4 py-3 flex items-center
                dark:border-green-700 dark:bg-green-900/20 dark:text-green-200" role="alert">
      <i class="fas fa-check-circle text-green-500 dark:text-green-300 mr-3" aria-hidden="true"></i>
      <span>{{ session('ok') }}</span>
    </div>
  @endif

  {{-- Pestañas --}}
  <div class="mb-6">
    <div class="border-b border-gray-200 dark:border-neutral-700">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <a href="{{ route('stock.index', array_merge(request()->except('tab'), ['tab' => 'products'])) }}"
           class="@if(($tab ?? 'products') === 'products') border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-neutral-400 dark:hover:text-neutral-300 @endif
                  whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
          <i class="fas fa-box mr-2"></i>
          Productos
        </a>
        <a href="{{ route('stock.index', array_merge(request()->except('tab'), ['tab' => 'supplies'])) }}"
           class="@if(($tab ?? 'products') === 'supplies') border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-neutral-400 dark:hover:text-neutral-300 @endif
                  whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
          <i class="fas fa-boxes-stacked mr-2"></i>
          Insumos
        </a>
      </nav>
    </div>
  </div>

  {{-- Panel de Sucursales (solo para vista company en productos) --}}
  @if(($tab ?? 'products') === 'products' && $isCompanyView && !empty($branchList))
    @include('stock.partials.branch-list', [
      'branchList' => $branchList,
      'branchStocks' => $branchStocks ?? [],
      'companyTotal' => $companyTotal ?? 0
    ])
  @endif

  {{-- Estadísticas resumen --}}
  @include('stock.partials.summary-stats', [
    'totals' => $totals ?? ['items' => 0, 'units' => 0, 'value' => 0],
    'products' => $products ?? null,
    'supplies' => $supplies ?? null,
    'tab' => $tab ?? 'products'
  ])

  @if(($tab ?? 'products') === 'products')
    {{-- Filtros rápidos para productos --}}
    @include('stock.partials.filters', [
      'currentStatus' => request('status', ''),
      'currentQuery' => request('q'),
      'currentOrderBy' => request('order_by', 'name'),
      'currentDir' => request('dir', 'asc'),
      'branchId' => $branchId ?? null
    ])

    {{-- Grid de productos --}}
    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 p-4">
      @if($products->count() === 0)
        <div class="py-16 text-center">
          <i class="fas fa-magnifying-glass text-gray-300 dark:text-neutral-600 text-5xl mb-3" aria-hidden="true"></i>
          <div class="text-gray-600 dark:text-neutral-300">No hay productos para mostrar con los filtros actuales.</div>
        </div>
      @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
          @foreach($products as $p)
            @include('stock.partials.product-card', [
              'product' => $p,
              'branchId' => $branchId ?? null
            ])
          @endforeach
        </div>
      @endif
    </div>

    {{-- Paginación --}}
    @if($products->hasPages())
      <div class="mt-6 print:hidden">
        {{ $products->withQueryString()->links() }}
      </div>
    @endif
  @else
    {{-- Vista de insumos --}}
    {{-- Barra de búsqueda y ordenamiento --}}
    <div class="mb-6 bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-100 dark:border-neutral-700 p-4">
      <form method="GET" action="{{ route('stock.index') }}" class="flex flex-col sm:flex-row gap-3">
        <input type="hidden" name="tab" value="supplies">

        <div class="flex-1">
          <input type="text"
                 name="q"
                 value="{{ request('q') }}"
                 placeholder="Buscar insumos..."
                 class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-lg
                        focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                        dark:bg-neutral-800 dark:text-neutral-100">
        </div>

        <select name="order_by"
                class="px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-lg
                       focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                       dark:bg-neutral-800 dark:text-neutral-100">
          <option value="name" {{ request('order_by') === 'name' ? 'selected' : '' }}>Nombre</option>
          <option value="stock" {{ request('order_by') === 'stock' ? 'selected' : '' }}>Stock</option>
          <option value="value" {{ request('order_by') === 'value' ? 'selected' : '' }}>Valorización</option>
        </select>

        <select name="dir"
                class="px-4 py-2 border border-gray-300 dark:border-neutral-600 rounded-lg
                       focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                       dark:bg-neutral-800 dark:text-neutral-100">
          <option value="asc" {{ request('dir') === 'asc' ? 'selected' : '' }}>Ascendente</option>
          <option value="desc" {{ request('dir') === 'desc' ? 'selected' : '' }}>Descendente</option>
        </select>

        <button type="submit"
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700
                       focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
          Buscar
        </button>
      </form>
    </div>

    {{-- Grid de insumos --}}
    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 p-4">
      @if($supplies->count() === 0)
        <div class="py-16 text-center">
          <i class="fas fa-magnifying-glass text-gray-300 dark:text-neutral-600 text-5xl mb-3" aria-hidden="true"></i>
          <div class="text-gray-600 dark:text-neutral-300">No hay insumos para mostrar.</div>
        </div>
      @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
          @foreach($supplies as $supply)
            @include('stock.partials.supply-card', ['supply' => $supply])
          @endforeach
        </div>
      @endif
    </div>

    {{-- Paginación --}}
    @if($supplies->hasPages())
      <div class="mt-6 print:hidden">
        {{ $supplies->withQueryString()->links() }}
      </div>
    @endif
  @endif
</div>

{{-- Modal de Descarga --}}
@include('stock.partials.download-modal', [
  'branchId' => $branchId ?? null,
  'isCompanyView' => $isCompanyView ?? false
])

{{-- Modal de Configuración de Notificaciones --}}
@include('stock.partials.notification-settings-modal')

{{-- Modal de Descuento de Stock --}}
@livewire('stock-discount-modal')

{{-- Iconos --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">

{{-- Print styles --}}
@push('styles')
<style>
@media print {
  header, nav, .print\:hidden, #downloadModal, form, button { display: none !important; }
  body { background: #fff !important; color: #000 !important; }
  a { color: #000 !important; text-decoration: none !important; }
  .dark * { color: #000 !important; background: #fff !important; }
  h1 { margin-bottom: 20px; }
  .grid { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
  'use strict';

  const modal = {
    element: null,
    downloadBtn: null,
    closeBtn: null,

    init() {
      this.element = document.getElementById('downloadModal');
      this.downloadBtn = document.getElementById('downloadReportBtn');
      this.closeBtn = document.getElementById('closeModal');

      if (!this.element || !this.downloadBtn || !this.closeBtn) return;

      this.attachEvents();
    },

    attachEvents() {
      this.downloadBtn.addEventListener('click', () => this.show());
      this.closeBtn.addEventListener('click', () => this.hide());
      this.element.addEventListener('click', (e) => {
        if (e.target === this.element) this.hide();
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.isVisible()) this.hide();
      });
    },

    show() {
      this.element.classList.remove('hidden');
      this.element.classList.add('flex');
      document.body.style.overflow = 'hidden';
    },

    hide() {
      this.element.classList.add('hidden');
      this.element.classList.remove('flex');
      document.body.style.overflow = '';
    },

    isVisible() {
      return !this.element.classList.contains('hidden');
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => modal.init());
  } else {
    modal.init();
  }
})();

// Recargar página cuando se actualiza el stock
document.addEventListener('livewire:init', () => {
  Livewire.on('refresh-page', () => {
    setTimeout(() => {
      window.location.reload();
    }, 1500);
  });
});
</script>
@endpush
@endsection
