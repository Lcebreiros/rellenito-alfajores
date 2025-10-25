@extends('layouts.app')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
  <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center">
    <i class="fas fa-boxes-stacked text-indigo-600 dark:text-indigo-400 mr-3"></i> 
    Reporte de Stock
    @if($branchId && !empty($availableBranches))
      @php
        $currentBranch = collect($availableBranches)->firstWhere('id', $branchId);
      @endphp
      @if($currentBranch)
        <span class="ml-2 text-sm font-normal text-gray-500 dark:text-neutral-400">
          - {{ $currentBranch['name'] }}
        </span>
        @if(!empty($branchUsesCompanyInventory))
          <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
            <i class="fas fa-building"></i> Inventario de empresa
          </span>
        @endif
      @endif
    @elseif($isCompanyView)
      <span class="ml-2 text-sm font-normal text-gray-500 dark:text-neutral-400">- Vista Consolidada</span>
    @elseif($isMasterView ?? false)
      <span class="ml-2 text-sm font-normal text-gray-500 dark:text-neutral-400">- Vista Global</span>
    @endif
  </h1>

  <div class="flex gap-2 mt-3 sm:mt-0">
    {{-- Selector de Sucursal --}}
    @php $u = auth()->user(); @endphp
    @if(($u && (method_exists($u,'isCompany') && $u->isCompany()) ) || ($u && method_exists($u,'isMaster') && $u->isMaster()) || (!empty($availableBranches) && count($availableBranches) >= 1))
      <div class="relative">
        <select onchange="window.location.href = this.value" 
                class="appearance-none bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600 
                       text-gray-900 dark:text-neutral-100 text-sm rounded-lg focus:ring-indigo-500 
                       focus:border-indigo-500 px-4 py-2 pr-8">
          
          @if($u && ((method_exists($u,'isCompany') && $u->isCompany()) || (method_exists($u,'isMaster') && $u->isMaster())))
            <option value="{{ route('stock.index', array_merge(request()->query(), ['branch_id' => null])) }}" 
                    {{ (!$branchId && $isCompanyView) ? 'selected' : '' }}>
              📊 Vista Consolidada{{ auth()->user()->isCompany() ? ' (' . auth()->user()->name . ')' : '' }}
            </option>
          @endif
          
          @if(auth()->user()->isMaster() && !$isCompanyView && !$branchId)
            <option value="{{ route('stock.index', array_merge(request()->query(), ['branch_id' => null])) }}" 
                    selected>
              🌐 Vista Global
            </option>
          @endif
          
          @foreach($availableBranches ?? [] as $branch)
            <option value="{{ route('stock.index', array_merge(request()->query(), ['branch_id' => $branch['id']])) }}" 
                    {{ $branchId == $branch['id'] ? 'selected' : '' }}>
              🏪 {{ $branch['name'] }}
              @if(isset($branch['company_name']) && auth()->user()->isMaster())
                ({{ $branch['company_name'] }})
              @endif
            </option>
          @endforeach
          @if((empty($availableBranches) || count($availableBranches)===0) && ($u && (method_exists($u,'isCompany') && $u->isCompany())))
            <option disabled>(Sin sucursales)</option>
          @endif
        </select>
        <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
      </div>
    @endif

    <button id="downloadReportBtn"
      class="inline-flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm
             bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors">
      <i class="fa-solid fa-download"></i>
      <span>Descargar</span>
    </button>

    <button onclick="window.print()"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors
                   bg-white border border-gray-300 text-gray-700 hover:bg-gray-50
                   dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700">
      <i class="fas fa-print"></i> <span>Imprimir / PDF</span>
    </button>

    <a href="{{ route('stock.history') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors
              bg-indigo-600 text-white hover:bg-indigo-700">
       <i class="fas fa-clock"></i> <span>Historial</span>
    </a>
  </div>
</div>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">

  {{-- Mensajes --}}
  @if(session('ok'))
    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 text-green-800 px-4 py-3 flex items-center
                dark:border-green-700 dark:bg-green-900/20 dark:text-green-200">
      <i class="fas fa-check-circle text-green-500 dark:text-green-300 mr-3"></i>
      <span>{{ session('ok') }}</span>
    </div>
  @endif

  {{-- Panel de Sucursales (solo para vista company) --}}
  @if($isCompanyView && !empty($branchList))
    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 p-4 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100 flex items-center">
          <i class="fas fa-building text-indigo-600 dark:text-indigo-400 mr-2"></i>
          Stock por Sucursal
        </h3>
        <div class="text-sm text-gray-500 dark:text-neutral-400">
          Total: <span class="font-semibold text-gray-900 dark:text-neutral-100">{{ number_format($companyTotal ?? 0) }}</span> unidades
        </div>
      </div>

      @if(!empty($branchList))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          @foreach($branchList as $branch)
            @php 
              $branchStock = $branchStocks[$branch['id']] ?? 0;
              $percentage = $companyTotal > 0 ? ($branchStock / $companyTotal) * 100 : 0;
            @endphp
            <a href="{{ route('stock.index', array_merge(request()->query(), ['branch_id' => $branch['id']])) }}"
               class="block p-4 rounded-lg border border-gray-200 dark:border-neutral-700 
                      hover:border-indigo-300 dark:hover:border-indigo-600 
                      hover:shadow-sm transition-all group">
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium text-gray-900 dark:text-neutral-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                  {{ $branch['name'] }}
                </h4>
                <i class="fas fa-external-link-alt text-xs text-gray-400 group-hover:text-indigo-500"></i>
              </div>
              
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500 dark:text-neutral-400">Stock:</span>
                <span class="font-semibold text-gray-900 dark:text-neutral-100">{{ number_format($branchStock) }}</span>
              </div>
              
              <div class="mt-2">
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-neutral-400 mb-1">
                  <span>Participación</span>
                  <span>{{ number_format($percentage, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-neutral-700 rounded-full h-1.5">
                  <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                </div>
              </div>
            </a>
          @endforeach
        </div>
      @else
        <div class="text-center py-8">
          <i class="fas fa-store text-gray-300 dark:text-neutral-600 text-3xl mb-3"></i>
          <p class="text-gray-500 dark:text-neutral-400">No hay sucursales registradas</p>
        </div>
      @endif
    </div>
  @endif

  {{-- Estadísticas resumen --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 dark:text-neutral-400">Productos</p>
          <p class="text-xl font-bold text-gray-900 dark:text-neutral-100">{{ number_format($totals['items']) }}</p>
        </div>
        <div class="rounded-lg bg-indigo-50 dark:bg-indigo-500/10 p-2">
          <i class="fas fa-box text-indigo-600 dark:text-indigo-300"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 dark:text-neutral-400">Unidades</p>
          <p class="text-xl font-bold text-gray-900 dark:text-neutral-100">{{ number_format($totals['units']) }}</p>
        </div>
        <div class="rounded-lg bg-blue-50 dark:bg-blue-500/10 p-2">
          <i class="fas fa-layer-group text-blue-600 dark:text-blue-300"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 dark:text-neutral-400">Valorización</p>
          <p class="text-xl font-bold text-gray-900 dark:text-neutral-100">$ {{ number_format($totals['value'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-500/10 p-2">
          <i class="fas fa-sack-dollar text-emerald-600 dark:text-emerald-300"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
      @php
        $out = $products->getCollection()->filter(function($p){
          $stock = (float) ($p->display_stock ?? 0);
          return $stock <= 0;
        })->count();

        $low = $products->getCollection()->filter(function($p){
          $stock = (float) ($p->display_stock ?? 0);
          $min   = (float) ($p->min_stock ?? 0);
          return $stock > 0 && $min > 0 && $stock <= $min;
        })->count();
      @endphp
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 dark:text-neutral-400">Alertas</p>
          <p class="text-sm text-gray-700 dark:text-neutral-300">
            <span class="mr-3"><span class="inline-block w-2 h-2 rounded-full bg-rose-500 mr-1"></span>Sin stock: <b>{{ $out }}</b></span>
            <span><span class="inline-block w-2 h-2 rounded-full bg-amber-500 mr-1"></span>Bajo: <b>{{ $low }}</b></span>
          </p>
        </div>
        <div class="rounded-lg bg-rose-50 dark:bg-rose-500/10 p-2">
          <i class="fas fa-triangle-exclamation text-rose-600 dark:text-rose-300"></i>
        </div>
      </div>
    </div>
  </div>

  {{-- Filtros rápidos --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm p-4 mb-6 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10">
    <div class="flex flex-wrap items-center gap-2 mb-3">
      <span class="text-sm font-medium text-gray-700 dark:text-neutral-300 flex items-center py-2">
        <i class="fas fa-filter text-gray-500 dark:text-neutral-400 mr-2"></i> Estado:
      </span>

      @php $current = request('status',''); @endphp
      @foreach([''=>'Todos','in'=>'En stock','low'=>'Bajo (≤ mín.)','out'=>'Sin stock'] as $key=>$label)
        @php $is = $current===$key; @endphp
        <a href="{{ request()->fullUrlWithQuery(['status' => $key ?: null, 'page'=>null]) }}"
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
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar nombre / SKU / precio…"
                   class="w-full pl-9 pr-3 py-2 rounded-lg text-sm
                          border-gray-300 focus:border-indigo-500 focus:ring-indigo-500
                          dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-100 dark:placeholder-neutral-400">
          </div>
          <input type="hidden" name="status" value="{{ request('status') }}">
          <input type="hidden" name="order_by" value="{{ request('order_by','name') }}">
          <input type="hidden" name="dir" value="{{ request('dir','asc') }}">
          <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
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
        $isName  = request('order_by','name')==='name';
        $isStock = request('order_by')==='stock';
        $isValue = request('order_by')==='value';
      @endphp

      <a href="{{ request()->fullUrlWithQuery(['order_by'=>'name','dir'=> ($isName && request('dir','asc')==='asc') ? 'desc':'asc','page'=>null]) }}"
         class="px-2 py-1.5 rounded-lg text-xs border
                {{ $isName
                    ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300'
                    : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700' }}">
        Nombre {{ $isName ? (request('dir','asc')==='asc'?'↑':'↓') : '' }}
      </a>

      <a href="{{ request()->fullUrlWithQuery(['order_by'=>'stock','dir'=> ($isStock && request('dir','asc')==='asc') ? 'desc':'asc','page'=>null]) }}"
         class="px-2 py-1.5 rounded-lg text-xs border
                {{ $isStock
                    ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300'
                    : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700' }}">
        Stock {{ $isStock ? (request('dir','asc')==='asc'?'↑':'↓') : '' }}
      </a>

      <a href="{{ request()->fullUrlWithQuery(['order_by'=>'value','dir'=> ($isValue && request('dir','asc')==='asc') ? 'desc':'asc','page'=>null]) }}"
         class="px-2 py-1.5 rounded-lg text-xs border
                {{ $isValue
                    ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300'
                    : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700' }}">
        Valorización {{ $isValue ? (request('dir','asc')==='asc'?'↑':'↓') : '' }}
      </a>

      <a href="{{ route('stock.index') }}"
         class="ml-auto inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs
                bg-gray-100 text-gray-700 hover:bg-gray-200
                dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700">
        <i class="fas fa-eraser mr-1"></i> Limpiar
      </a>
    </div>
  </div>

  {{-- Grid de productos --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 p-4">
    @if($products->count() === 0)
      <div class="py-16 text-center">
        <i class="fas fa-magnifying-glass text-gray-300 dark:text-neutral-600 text-5xl mb-3"></i>
        <div class="text-gray-600 dark:text-neutral-300">No hay productos para mostrar con los filtros actuales.</div>
      </div>
    @else
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        @foreach($products as $p)
          @php
            $totalStock = (int) ($p->total_stock ?? 0);
            $branchStock = (int) ($p->stock_in_branch ?? 0);
            $displayStock = (int) ($p->display_stock ?? ($branchId ? $branchStock : $totalStock));
            $min = (int) ($p->min_stock ?? 0);
            $price = (float) ($p->price ?? 0);
            $value = $price * $displayStock;

            $badgeBase = 'inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-medium';
            if ($displayStock <= 0) {
              $badge = "$badgeBase bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300";
              $badgeText = 'Sin stock';
            } elseif ($min > 0 && $displayStock <= $min) {
              $badge = "$badgeBase bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300";
              $badgeText = 'Bajo';
            } else {
              $badge = "$badgeBase bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300";
              $badgeText = 'OK';
            }
          @endphp

          <a href="{{ route('stock.show', $p->id) }}" class="rounded-xl border border-gray-200 dark:border-neutral-700 p-3 hover:shadow-sm transition-colors bg-white dark:bg-neutral-900 block focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <div class="flex items-start justify-between gap-2 mb-2">
              <div class="min-w-0 flex-1">
                <div class="font-semibold text-gray-900 dark:text-neutral-100 truncate">{{ $p->name }}</div>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-xs text-gray-500 dark:text-neutral-400 truncate">{{ $p->sku ?? '—' }}</span>
                  
                  {{-- 🎯 INDICADOR DE ORIGEN DEL PRODUCTO --}}
                  @if(isset($p->created_by_type))
                    @if($p->created_by_type === 'branch')
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 flex-shrink-0">
                        <i class="fas fa-store text-[8px] mr-0.5"></i> Sucursal
                      </span>
                    @else
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 flex-shrink-0">
                        <i class="fas fa-building text-[8px] mr-0.5"></i> Empresa
                      </span>
                    @endif
                  @endif
                </div>
              </div>
              <span class="{{ $badge }}">{{ $badgeText }}</span>
            </div>

            <div class="mt-3 grid grid-cols-3 gap-2 text-sm">
              <div class="bg-gray-50 dark:bg-neutral-800/60 rounded-lg p-2 text-center">
                <div class="text-[11px] text-gray-500 dark:text-neutral-300">
                  @if($branchId && $totalStock != $branchStock)
                    Sucursal
                  @else
                    Stock
                  @endif
                </div>
                <div class="font-bold text-gray-800 dark:text-neutral-100">{{ number_format($displayStock) }}</div>
                @if($branchId && $totalStock != $branchStock)
                  <div class="text-[10px] text-gray-400 dark:text-neutral-500">
                    Total: {{ number_format($totalStock) }}
                  </div>
                @endif
              </div>
              <div class="bg-gray-50 dark:bg-neutral-800/60 rounded-lg p-2 text-center">
                <div class="text-[11px] text-gray-500 dark:text-neutral-300">Mín.</div>
                <div class="font-semibold text-gray-800 dark:text-neutral-100">{{ $min ?: '—' }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-neutral-800/60 rounded-lg p-2 text-center">
                <div class="text-[11px] text-gray-500 dark:text-neutral-300">Precio</div>
                <div class="font-semibold text-gray-800 dark:text-neutral-100">$ {{ number_format($price, 2, ',', '.') }}</div>
              </div>
            </div>

            <div class="mt-2 flex items-center justify-between">
              <div class="text-sm text-gray-600 dark:text-neutral-300">Valorización</div>
              <div class="font-semibold text-gray-900 dark:text-neutral-100">$ {{ number_format($value, 2, ',', '.') }}</div>
            </div>
          </a>
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
<div id="downloadModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 max-w-md w-full mx-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100 flex items-center">
        <i class="fas fa-download text-emerald-600 dark:text-emerald-400 mr-2"></i>
        Descargar reporte
      </h3>
      <button id="closeModal" class="text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <p class="text-gray-600 dark:text-neutral-300 mb-4">
      Selecciona el formato para guardar localmente:
      @if($branchId)
        <br><small class="text-xs text-indigo-600 dark:text-indigo-400">Se exportará solo la sucursal actual</small>
      @elseif($isCompanyView)
        <br><small class="text-xs text-indigo-600 dark:text-indigo-400">Se exportará vista consolidada de la empresa</small>
      @endif
    </p>

    <div class="space-y-3">
      <a href="{{ route('stock.export.csv', request()->query()) }}"
         class="w-full flex items-center justify-between p-3 border rounded-lg transition-colors
                border-gray-300 hover:bg-gray-50
                dark:border-neutral-600 dark:hover:bg-neutral-800 dark:text-neutral-100">
        <div class="flex items-center">
          <div class="bg-green-100 dark:bg-emerald-500/10 p-2 rounded-lg mr-3">
            <i class="fas fa-file-csv text-green-600 dark:text-emerald-300"></i>
          </div>
          <div>
            <div class="font-medium text-gray-900 dark:text-neutral-100">CSV</div>
            <div class="text-sm text-gray-500 dark:text-neutral-400">Abrilo con Excel / Google Sheets</div>
          </div>
        </div>
        <i class="fas fa-download text-gray-400 dark:text-neutral-500"></i>
      </a>

      <button onclick="window.print(); document.getElementById('downloadModal').classList.add('hidden');"
              class="w-full flex items-center justify-between p-3 border rounded-lg transition-colors
                     border-gray-300 hover:bg-gray-50
                     dark:border-neutral-600 dark:hover:bg-neutral-800 dark:text-neutral-100">
        <div class="flex items-center">
          <div class="bg-blue-100 dark:bg-blue-500/10 p-2 rounded-lg mr-3">
            <i class="fas fa-file-pdf text-blue-600 dark:text-blue-300"></i>
          </div>
          <div>
            <div class="font-medium text-gray-900 dark:text-neutral-100">PDF</div>
            <div class="text-sm text-gray-500 dark:text-neutral-400">Usa "Guardar como PDF" al imprimir</div>
          </div>
        </div>
        <i class="fas fa-print text-gray-400<i class="fas fa-print text-gray-400 dark:text-neutral-500"></i>
      </button>
    </div>

    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-500/10 rounded-lg">
      <div class="flex items-start">
        <i class="fas fa-info-circle text-blue-500 dark:text-blue-300 mt-0.5 mr-2"></i>
        <div class="text-sm text-blue-700 dark:text-blue-200">
          Se exportan los productos con los filtros actuales.
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Iconos --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

{{-- Print styles --}}
<style>
@media print {
  header, nav, .print\:hidden, #downloadModal, form, button { display: none !important; }
  body { background: #fff !important; color: #000 !important; }
  a { color: #000 !important; text-decoration: none !important; }
  .dark * { color: #000 !important; background: #fff !important; }
  
  /* Mejorar impresión del título */
  h1 { margin-bottom: 20px; }
  
  /* Ajustar grid para impresión */
  .grid { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const downloadBtn = document.getElementById('downloadReportBtn');
  const modal = document.getElementById('downloadModal');
  const closeModal = document.getElementById('closeModal');

  const showModal = () => { 
    modal.classList.remove('hidden'); 
    modal.classList.add('flex'); 
  }
  
  const hideModal = () => { 
    modal.classList.add('hidden'); 
    modal.classList.remove('flex'); 
  }

  downloadBtn?.addEventListener('click', showModal);
  closeModal?.addEventListener('click', hideModal);
  
  // Cerrar modal al hacer click fuera
  modal?.addEventListener('click', (e) => { 
    if(e.target === modal) hideModal(); 
  });
  
  // Cerrar modal con ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
      hideModal();
    }
  });
});
</script>
@endsection
