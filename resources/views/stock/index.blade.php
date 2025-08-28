@extends('layouts.app')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
  <h1 class="text-2xl font-bold text-gray-900 flex items-center">
    <i class="fas fa-boxes-stacked text-indigo-600 mr-3"></i> Reporte de Stock
  </h1>

  <div class="flex gap-2 mt-3 sm:mt-0">
<button id="downloadReportBtn"
  class="inline-flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm
         bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors"
  style="background:#16a34a; color:#fff; border-color:transparent">
  <i class="fa-solid fa-download"></i>
  <span>Descargar</span>
</button>
    

    <button onclick="window.print()"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
      <i class="fas fa-print mr-2"></i> Imprimir / PDF
    </button>
  </div>
</div>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">

  {{-- Mensajes --}}
  @if(session('ok'))
    <div class="mb-6 rounded-xl bg-green-50 text-green-800 px-4 py-3 flex items-center border border-green-200">
      <i class="fas fa-check-circle text-green-500 mr-3"></i>
      <span>{{ session('ok') }}</span>
    </div>
  @endif

  {{-- Estadísticas resumen --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500">Productos</p>
          <p class="text-xl font-bold text-gray-900">{{ number_format($totals['items']) }}</p>
        </div>
        <div class="rounded-lg bg-indigo-50 p-2">
          <i class="fas fa-box text-indigo-600"></i>
        </div>
      </div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500">Unidades</p>
          <p class="text-xl font-bold text-gray-900">{{ number_format($totals['units']) }}</p>
        </div>
        <div class="rounded-lg bg-blue-50 p-2">
          <i class="fas fa-layer-group text-blue-600"></i>
        </div>
      </div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500">Valorización</p>
          <p class="text-xl font-bold text-gray-900">$ {{ number_format($totals['value'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-emerald-50 p-2">
          <i class="fas fa-sack-dollar text-emerald-600"></i>
        </div>
      </div>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
      @php
        $out = $products->getCollection()->where('stock', '<=', 0)->count();
        $low = $products->getCollection()->filter(fn($p)=>($p->stock??0)>0 && ($p->reorder_level??0)>0 && $p->stock <= $p->reorder_level)->count();
      @endphp
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500">Alertas</p>
          <p class="text-sm text-gray-700">
            <span class="mr-3"><span class="inline-block w-2 h-2 rounded-full bg-rose-500 mr-1"></span>Sin stock: <b>{{ $out }}</b></span>
            <span><span class="inline-block w-2 h-2 rounded-full bg-amber-500 mr-1"></span>Bajo: <b>{{ $low }}</b></span>
          </p>
        </div>
        <div class="rounded-lg bg-rose-50 p-2">
          <i class="fas fa-triangle-exclamation text-rose-600"></i>
        </div>
      </div>
    </div>
  </div>

  {{-- Filtros rápidos --}}
  <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100">
    <div class="flex flex-wrap items-center gap-2 mb-3">
      <span class="text-sm font-medium text-gray-700 flex items-center py-2">
        <i class="fas fa-filter text-gray-500 mr-2"></i> Estado:
      </span>

      @php $current = request('status',''); @endphp
      @foreach([
        ''=>'Todos',
        'in'=>'En stock',
        'low'=>'Bajo (≤ mín.)',
        'out'=>'Sin stock'
      ] as $key=>$label)
        <a href="{{ request()->fullUrlWithQuery(['status' => $key ?: null, 'page'=>null]) }}"
           class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium
                  {{ $current===$key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
          {{ $label }}
        </a>
      @endforeach

      <div class="ml-auto w-full sm:w-72">
        <form method="GET" class="flex">
          <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar nombre / SKU / código…"
                   class="w-full pl-9 pr-3 py-2 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
          </div>
          {{-- mantener filtros --}}
          <input type="hidden" name="status" value="{{ request('status') }}">
          <input type="hidden" name="order_by" value="{{ request('order_by','name') }}">
          <input type="hidden" name="dir" value="{{ request('dir','asc') }}">
          <button class="ml-2 px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
            Buscar
          </button>
        </form>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <span class="text-xs text-gray-500">Ordenar:</span>
      <a href="{{ request()->fullUrlWithQuery(['order_by'=>'name','dir'=> request('order_by')==='name' && request('dir','asc')==='asc' ? 'desc':'asc','page'=>null]) }}"
         class="px-2 py-1.5 rounded-lg text-xs border {{ request('order_by','name')==='name' ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
        Nombre {{ request('order_by','name')==='name' ? (request('dir','asc')==='asc'?'↑':'↓') : '' }}
      </a>
      <a href="{{ request()->fullUrlWithQuery(['order_by'=>'stock','dir'=> request('order_by')==='stock' && request('dir','asc')==='asc' ? 'desc':'asc','page'=>null]) }}"
         class="px-2 py-1.5 rounded-lg text-xs border {{ request('order_by')==='stock' ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
        Stock {{ request('order_by')==='stock' ? (request('dir','asc')==='asc'?'↑':'↓') : '' }}
      </a>
      <a href="{{ request()->fullUrlWithQuery(['order_by'=>'value','dir'=> request('order_by')==='value' && request('dir','asc')==='asc' ? 'desc':'asc','page'=>null]) }}"
         class="px-2 py-1.5 rounded-lg text-xs border {{ request('order_by')==='value' ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
        Valorización {{ request('order_by')==='value' ? (request('dir','asc')==='asc'?'↑':'↓') : '' }}
      </a>

      <a href="{{ route('stock.index') }}"
         class="ml-auto inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs bg-gray-100 text-gray-700 hover:bg-gray-200">
        <i class="fas fa-eraser mr-1"></i> Limpiar
      </a>
    </div>
  </div>

  {{-- Grid de productos --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    @if($products->count() === 0)
      <div class="py-16 text-center">
        <i class="fas fa-magnifying-glass text-gray-300 text-5xl mb-3"></i>
        <div class="text-gray-600">No hay productos para mostrar con los filtros actuales.</div>
      </div>
    @else
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        @foreach($products as $p)
          @php
            $stock = (int) ($p->stock ?? 0);
            $min   = (int) ($p->reorder_level ?? 0);
            $price = (float) ($p->price ?? 0);
            $value = $price * $stock;

            $badgeClass = $stock <= 0 ? 'bg-rose-100 text-rose-700'
              : (($min > 0 && $stock <= $min) ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');

            $badgeText = $stock <= 0 ? 'Sin stock' : (($min > 0 && $stock <= $min) ? 'Bajo' : 'OK');
          @endphp

          <div class="rounded-xl border border-gray-200 p-3 hover:shadow-sm transition-colors">
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <div class="font-semibold text-gray-900 truncate">{{ $p->name }}</div>
                <div class="text-xs text-gray-500 truncate">{{ $p->sku ?? '—' }} @if(!empty($p->barcode)) · {{ $p->barcode }} @endif</div>
              </div>
              <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] {{ $badgeClass }}">{{ $badgeText }}</span>
            </div>

            <div class="mt-3 grid grid-cols-3 gap-2 text-sm">
              <div class="bg-gray-50 rounded-lg p-2 text-center">
                <div class="text-[11px] text-gray-500">Stock</div>
                <div class="font-bold text-gray-800">{{ $stock }}</div>
              </div>
              <div class="bg-gray-50 rounded-lg p-2 text-center">
                <div class="text-[11px] text-gray-500">Mín.</div>
                <div class="font-semibold text-gray-800">{{ $min ?: '—' }}</div>
              </div>
              <div class="bg-gray-50 rounded-lg p-2 text-center">
                <div class="text-[11px] text-gray-500">Precio</div>
                <div class="font-semibold text-gray-800">$ {{ number_format($price, 2, ',', '.') }}</div>
              </div>
            </div>

            <div class="mt-2 flex items-center justify-between">
              <div class="text-sm text-gray-600">Valorización</div>
              <div class="font-semibold text-gray-900">$ {{ number_format($value, 2, ',', '.') }}</div>
            </div>
          </div>
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
<div id="downloadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900 flex items-center">
        <i class="fas fa-download text-emerald-600 mr-2"></i>
        Descargar reporte
      </h3>
      <button id="closeModal" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <p class="text-gray-600 mb-4">Selecciona el formato para guardar localmente:</p>

    <div class="space-y-3">
      <a href="{{ route('stock.export.csv', request()->query()) }}"
         class="w-full flex items-center justify-between p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <div class="flex items-center">
          <div class="bg-green-100 p-2 rounded-lg mr-3">
            <i class="fas fa-file-csv text-green-600"></i>
          </div>
          <div>
            <div class="font-medium text-gray-900">CSV</div>
            <div class="text-sm text-gray-500">Abrilo con Excel / Google Sheets</div>
          </div>
        </div>
        <i class="fas fa-download text-gray-400"></i>
      </a>

      <button onclick="window.print()"
              class="w-full flex items-center justify-between p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <div class="flex items-center">
          <div class="bg-blue-100 p-2 rounded-lg mr-3">
            <i class="fas fa-file-pdf text-blue-600"></i>
          </div>
          <div>
            <div class="font-medium text-gray-900">PDF</div>
            <div class="text-sm text-gray-500">Usa “Guardar como PDF” al imprimir</div>
          </div>
        </div>
        <i class="fas fa-print text-gray-400"></i>
      </button>
    </div>

    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
      <div class="flex items-start">
        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
        <div class="text-sm text-blue-700">
          Se exportan los productos con los filtros actuales.
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Iconos --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

{{-- Print minimal --}}
<style>
@media print {
  header, nav, .print\:hidden, #downloadModal, form { display: none !important; }
  body { background: #dd1717ff !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const downloadBtn = document.getElementById('downloadReportBtn');
  const modal = document.getElementById('downloadModal');
  const closeModal = document.getElementById('closeModal');

  function showModal(){ modal.classList.remove('hidden'); modal.classList.add('flex'); }
  function hideModal(){ modal.classList.add('hidden'); modal.classList.remove('flex'); }

  downloadBtn?.addEventListener('click', showModal);
  closeModal?.addEventListener('click', hideModal);
  modal?.addEventListener('click', (e)=>{ if(e.target===modal) hideModal(); });
});
</script>
@endsection
