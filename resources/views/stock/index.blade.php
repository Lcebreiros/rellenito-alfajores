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

  <div class="flex flex-wrap gap-2">
    {{-- Selector de Sucursal --}}
    @include('stock.partials.branch-selector', [
      'availableBranches' => $availableBranches ?? [],
      'branchId' => $branchId ?? null,
      'isCompanyView' => $isCompanyView ?? false
    ])

    <button type="button"
            id="openNotificationSettingsBtn"
            aria-label="Configurar notificaciones"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm
                   bg-indigo-600 text-white font-semibold hover:bg-indigo-700
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                   transition-colors relative">
      <i class="fa-solid fa-bell"></i>
      <span class="hidden sm:inline">Notificaciones</span>
    </button>

    <button type="button"
            id="downloadReportBtn"
            aria-label="Descargar reporte"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm
                   bg-green-600 text-white font-semibold hover:bg-green-700
                   focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2
                   transition-colors">
      <i class="fa-solid fa-download"></i>
      <span class="hidden sm:inline">Descargar</span>
    </button>

    <button type="button"
            onclick="window.print()"
            aria-label="Imprimir reporte"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors
                   bg-white border border-gray-300 text-gray-700 hover:bg-gray-50
                   dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
      <i class="fas fa-print"></i>
      <span class="hidden sm:inline">Imprimir</span>
    </button>

    <a href="{{ route('stock.history') }}"
       aria-label="Ver historial de stock"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors
              bg-indigo-600 text-white hover:bg-indigo-700
              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
       <i class="fas fa-clock"></i>
       <span class="hidden sm:inline">Historial</span>
    </a>
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

  {{-- Panel de Sucursales (solo para vista company) --}}
  @if($isCompanyView && !empty($branchList))
    @include('stock.partials.branch-list', [
      'branchList' => $branchList,
      'branchStocks' => $branchStocks ?? [],
      'companyTotal' => $companyTotal ?? 0
    ])
  @endif

  {{-- Estadísticas resumen --}}
  @include('stock.partials.summary-stats', [
    'totals' => $totals ?? ['items' => 0, 'units' => 0, 'value' => 0],
    'products' => $products
  ])

  {{-- Filtros rápidos --}}
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
</div>

{{-- Modal de Descarga --}}
@include('stock.partials.download-modal', [
  'branchId' => $branchId ?? null,
  'isCompanyView' => $isCompanyView ?? false
])

{{-- Modal de Configuración de Notificaciones --}}
@include('stock.partials.notification-settings-modal')

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
</script>
@endpush
@endsection
