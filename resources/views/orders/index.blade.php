@extends('layouts.app')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 flex items-center">
    <i class="fas fa-history text-indigo-600 mr-3"></i> Historial de Pedidos
  </h1>
  <div class="flex gap-2 mt-3 sm:mt-0">
    <button id="toggleFilters"
            class="inline-flex items-center px-3 py-2 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 rounded-lg font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
      <i class="fas fa-filter mr-2 text-sm"></i>
      <span class="filter-text">Mostrar Filtros</span>
      <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-200" id="filterChevron"></i>
    </button>

    <button id="downloadReportBtn"
      class="inline-flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors">
      <i class="fa-solid fa-download"></i><span>Descargar</span>
    </button>

    <a href="{{ route('orders.create') }}"
       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-white hover:bg-indigo-700 transition-colors">
      <i class="fas fa-plus-circle mr-2"></i> Nuevo Pedido
    </a>
  </div>
</div>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">

  {{-- Mensajes --}}
  @if(session('ok'))
    <div class="mb-6 rounded-xl bg-green-50 text-green-800 px-4 py-3 flex items-center border border-green-200
                dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800">
      <i class="fas fa-check-circle mr-3"></i>
      <span>{{ session('ok') }}</span>
    </div>
  @endif
  @if($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 text-red-800 px-4 py-3 border border-red-200
                dark:bg-rose-900/30 dark:text-rose-200 dark:border-rose-800">
      <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-3"></i>
        <div>@foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div>
      </div>
    </div>
  @endif

  {{-- Filtros rápidos por período --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm p-4 mb-6 border border-neutral-100 dark:border-neutral-800">
    <div class="flex flex-wrap gap-2 mb-2">
      <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300 flex items-center py-2">
        <i class="fas fa-clock text-neutral-500 dark:text-neutral-400 mr-2"></i> Período:
      </span>
      @php
        $currentPeriod = request('period','');
        $periods = [
          '' => 'Todos',
          'today' => 'Hoy',
          'yesterday' => 'Ayer',
          'this_week' => 'Esta semana',
          'last_week' => 'Semana pasada',
          'last_7_days' => 'Últimos 7 días',
          'this_month' => 'Este mes',
          'last_month' => 'Mes pasado',
          'last_30_days' => 'Últimos 30 días',
        ];
      @endphp
      @foreach($periods as $period => $label)
        <a href="{{ request()->fullUrlWithQuery(['period'=>$period?:null,'from'=>null,'to'=>null,'page'=>null]) }}"
           class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                  {{ $currentPeriod===$period ? 'bg-indigo-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>
  </div>

  {{-- Búsqueda --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm p-4 mb-6 border border-neutral-100 dark:border-neutral-800">
    <div class="flex flex-col sm:flex-row gap-3">
      <div class="flex-1">
        <form method="GET" class="flex gap-2">
          <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por ID, cliente, notas…"
                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
          </div>
          {{-- mantener filtros --}}
          @foreach(['status','period','from','to'] as $keep)
            @if(request($keep)) <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}"> @endif
          @endforeach
          <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            <i class="fas fa-search"></i>
          </button>
        </form>
      </div>

      {{-- Filtros activos --}}
      @if(request()->anyFilled(['status','period','from','to','q']))
        <div class="flex flex-wrap items-center gap-2">
          <span class="text-xs text-neutral-500 dark:text-neutral-400">Filtros activos:</span>
          @if(request('status'))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
              Estado: {{ ucfirst(request('status')) }}
              <a href="{{ request()->fullUrlWithQuery(['status'=>null]) }}" class="ml-1 hover:opacity-70"><i class="fas fa-times text-xs"></i></a>
            </span>
          @endif
          @if(request('period'))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
              Período: {{ $periods[request('period')] ?? request('period') }}
              <a href="{{ request()->fullUrlWithQuery(['period'=>null]) }}" class="ml-1 hover:opacity-70"><i class="fas fa-times text-xs"></i></a>
            </span>
          @endif
          @if(request('from'))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
              Desde: {{ request('from') }}
              <a href="{{ request()->fullUrlWithQuery(['from'=>null]) }}" class="ml-1 hover:opacity-70"><i class="fas fa-times text-xs"></i></a>
            </span>
          @endif
          @if(request('to'))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
              Hasta: {{ request('to') }}
              <a href="{{ request()->fullUrlWithQuery(['to'=>null]) }}" class="ml-1 hover:opacity-70"><i class="fas fa-times text-xs"></i></a>
            </span>
          @endif
          <a href="{{ route('orders.index') }}"
             class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700">
            <i class="fas fa-times mr-1"></i> Limpiar todo
          </a>
        </div>
      @endif
    </div>
  </div>

  {{-- Panel de Filtros Avanzados (colapsable) --}}
  <div id="filtersPanel" class="hidden bg-white dark:bg-neutral-900 rounded-xl shadow-sm p-5 mb-6 border border-neutral-100 dark:border-neutral-800 transition-all duration-300">
    <div class="mb-4 pb-3 border-b border-neutral-100 dark:border-neutral-800">
      <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center">
        <i class="fas fa-sliders-h text-indigo-600 mr-2"></i> Filtros Avanzados
      </h3>
    </div>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
      @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 flex items-center">
          <i class="fas fa-flag text-neutral-500 dark:text-neutral-400 mr-2 text-sm"></i> Estado
        </label>
        <select name="status" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors">
          <option value="">Todos los estados</option>
          <option value="completed" {{ request('status')==='completed'?'selected':'' }}>Completado</option>
          <option value="draft"     {{ request('status')==='draft'?'selected':'' }}>Borrador</option>
          <option value="canceled"  {{ request('status')==='canceled'?'selected':'' }}>Cancelado</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 flex items-center">
          <i class="fas fa-calendar-alt text-neutral-500 dark:text-neutral-400 mr-2 text-sm"></i> Fecha Desde
        </label>
        <input type="date" name="from" value="{{ request('from') }}"
               class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors">
      </div>
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 flex items-center">
          <i class="fas fa-calendar-alt text-neutral-500 dark:text-neutral-400 mr-2 text-sm"></i> Fecha Hasta
        </label>
        <input type="date" name="to" value="{{ request('to') }}"
               class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors">
      </div>
      <div class="flex gap-2">
        <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2.5 rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center">
          <i class="fas fa-search mr-2"></i> Aplicar
        </button>
        <a href="{{ route('orders.index') }}"
           class="flex-1 text-center border border-neutral-300 dark:border-neutral-700 px-4 py-2.5 text-neutral-700 dark:text-neutral-200 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center">
          <i class="fas fa-eraser mr-2"></i> Reset
        </a>
      </div>
    </form>
  </div>

  {{-- Resumen de resultados + orden --}}
  @if($orders->total() > 0)
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm">
      <div class="text-neutral-600 dark:text-neutral-300 bg-blue-50 dark:bg-neutral-800/40 rounded-lg px-3 py-1.5 flex items-center">
        <i class="fas fa-info-circle text-blue-500 dark:text-blue-300 mr-2 text-xs"></i>
        {{ $orders->firstItem() }}–{{ $orders->lastItem() }} de {{ $orders->total() }}
      </div>
      <div class="flex items-center gap-3">
        <button id="downloadReportBtn2"
                class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium">
          <i class="fas fa-download mr-1.5 text-xs"></i> Exportar
        </button>
        <div class="flex items-center gap-2">
          <span class="text-neutral-500 dark:text-neutral-400 text-xs">Ordenar:</span>
          <select onchange="window.location.href=this.value" class="text-xs border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded px-2 py-1">
            <option value="{{ request()->fullUrlWithQuery(['sort'=>'newest']) }}" {{ request('sort','newest')==='newest'?'selected':'' }}>Más recientes</option>
            <option value="{{ request()->fullUrlWithQuery(['sort'=>'oldest']) }}" {{ request('sort')==='oldest'?'selected':'' }}>Más antiguos</option>
            <option value="{{ request()->fullUrlWithQuery(['sort'=>'total_desc']) }}" {{ request('sort')==='total_desc'?'selected':'' }}>Mayor valor</option>
            <option value="{{ request()->fullUrlWithQuery(['sort'=>'total_asc']) }}" {{ request('sort')==='total_asc'?'selected':'' }}>Menor valor</option>
          </select>
        </div>
      </div>
    </div>
  @endif

  {{-- TABLA --}}
  @php
    $statusBadge = fn($s) => match($s){
      'completed' => ['text'=>'Completado','cls'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
      'canceled'  => ['text'=>'Cancelado','cls'=>'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'],
      'draft'     => ['text'=>'Borrador','cls'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
      default     => ['text'=>ucfirst($s??'—'),'cls'=>'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-100'],
    };
    $receiptRoute = \Illuminate\Support\Facades\Route::has('orders.ticket');
  @endphp

  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[980px] text-sm">
        <thead class="sticky top-0 z-10">
          <tr class="bg-neutral-100/80 dark:bg-neutral-800/60 backdrop-blur">
            <th class="text-left px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">Pedido</th>
            <th class="text-left px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Cliente</th>
            <th class="text-left px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Fecha</th>
            <th class="text-left px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Items</th>
            <th class="text-left px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Total</th>
            <th class="text-left px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Estado</th>
            <th class="text-right px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">Acción</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
          @forelse($orders as $o)
            @php
              $badge = $statusBadge($o->status);
              $itemsQty = (int)($o->items_qty ?? optional($o->items)->sum('quantity') ?? 0);
              $fmt = fn($n)=> '$'.number_format((float)$n,2,',','.');
            @endphp
            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
              <td class="px-6 py-3 font-semibold text-neutral-900 dark:text-neutral-100">#{{ $o->id }}</td>
              <td class="px-3 py-3">
                <div class="max-w-xs">
                  <div class="font-medium text-neutral-800 dark:text-neutral-100 truncate">
                    {{ $o->guest_name ?? $o->customer_name ?? 'Sin cliente' }}
                  </div>
                  @if(!empty($o->note))
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                      <i class="far fa-comment mr-1"></i>{{ $o->note }}
                    </div>
                  @endif
                </div>
              </td>
              <td class="px-3 py-3 text-neutral-700 dark:text-neutral-200 whitespace-nowrap">{{ $o->created_at?->format('d/m/Y H:i') }}</td>
              <td class="px-3 py-3 text-neutral-700 dark:text-neutral-200">{{ $itemsQty }}</td>
              <td class="px-3 py-3 font-semibold text-neutral-900 dark:text-neutral-100">{{ $fmt($o->total) }}</td>
              <td class="px-3 py-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium {{ $badge['cls'] }}">
                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $badge['text'] }}
                </span>
              </td>
              <td class="px-6 py-3">
                <div class="flex items-center justify-end gap-2">
                  <a href="{{ route('orders.show',$o) }}"
                     class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50">
                    <i class="far fa-eye"></i> Ver
                  </a>
                  <a href="{{ $receiptRoute ? route('orders.ticket',$o) : '#' }}"
                     @if(!$receiptRoute) aria-disabled="true" @endif
                     class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/50 {{ $receiptRoute ? '' : 'opacity-50 cursor-not-allowed' }}">
                    <i class="far fa-file-alt"></i> Comprobante
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-6 py-16 text-center text-neutral-500 dark:text-neutral-400">
                <i class="fas fa-search text-neutral-300 dark:text-neutral-600 text-5xl mb-3"></i>
                <div class="text-lg font-medium">No se encontraron pedidos</div>
                <p class="text-neutral-500 dark:text-neutral-400">Ajustá los filtros para ver resultados.</p>
                <div class="mt-4 flex justify-center gap-2">
                  <a href="{{ route('orders.index') }}" class="inline-flex items-center px-3 py-2 text-sm text-neutral-700 dark:text-neutral-200 bg-neutral-100 dark:bg-neutral-800 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700">
                    <i class="fas fa-broom mr-2"></i> Limpiar filtros
                  </a>
                  <a href="{{ route('orders.create') }}" class="inline-flex items-center px-3 py-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-plus-circle mr-2"></i> Crear pedido
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Paginación --}}
  @if($orders->hasPages())
    <div class="mt-6">{{ $orders->withQueryString()->links() }}</div>
  @endif
</div>

{{-- Modal de Descarga --}}
<div id="downloadModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 max-w-md w-full mx-4 border border-neutral-100 dark:border-neutral-800">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 flex items-center">
        <i class="fas fa-download text-emerald-600 mr-2"></i> Descargar Reporte
      </h3>
      <button id="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <p class="text-neutral-600 dark:text-neutral-300 mb-4">Selecciona el formato del reporte:</p>
    <div class="space-y-3">
      <a href="{{ route('orders.download-report', array_merge(request()->query(), ['format'=>'csv'])) }}"
         class="w-full flex items-center justify-between p-3 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
        <div class="flex items-center">
          <div class="bg-green-100 dark:bg-emerald-900/30 p-2 rounded-lg mr-3">
            <i class="fas fa-file-csv text-green-600 dark:text-emerald-300"></i>
          </div>
          <div>
            <div class="font-medium text-neutral-900 dark:text-neutral-100">CSV (Excel)</div>
            <div class="text-sm text-neutral-500 dark:text-neutral-400">Formato separado por comas</div>
          </div>
        </div>
        <i class="fas fa-download text-neutral-400"></i>
      </a>
      <a href="{{ route('orders.download-report', array_merge(request()->query(), ['format'=>'excel'])) }}"
         class="w-full flex items-center justify-between p-3 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
        <div class="flex items-center">
          <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg mr-3">
            <i class="fas fa-file-excel text-blue-600 dark:text-blue-300"></i>
          </div>
          <div>
            <div class="font-medium text-neutral-900 dark:text-neutral-100">Excel (XLS)</div>
            <div class="text-sm text-neutral-500 dark:text-neutral-400">Con formato y totales</div>
          </div>
        </div>
        <i class="fas fa-download text-neutral-400"></i>
      </a>
    </div>
    <div class="mt-4 p-3 bg-blue-50 dark:bg-neutral-800/40 rounded-lg text-sm text-blue-700 dark:text-neutral-200">
      Se incluirán: {{ $orders->total() }} pedidos con los filtros actuales.
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  #filtersPanel{transform:translateY(-10px);opacity:0}
  #filtersPanel.show{transform:translateY(0);opacity:1}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const toggleButton = document.getElementById('toggleFilters');
  const filtersPanel = document.getElementById('filtersPanel');
  const chevron = document.getElementById('filterChevron');
  const filterText = document.querySelector('.filter-text');

  const downloadBtn = document.getElementById('downloadReportBtn');
  const downloadBtn2 = document.getElementById('downloadReportBtn2');
  const downloadModal = document.getElementById('downloadModal');
  const closeModal = document.getElementById('closeModal');

  const hasActive = {{ request()->anyFilled(['status','from','to']) ? 'true' : 'false' }};
  if (hasActive) showFilters();

  toggleButton.addEventListener('click', () => filtersPanel.classList.contains('hidden') ? showFilters() : hideFilters());
  downloadBtn.addEventListener('click', showDownloadModal);
  downloadBtn2?.addEventListener('click', showDownloadModal);
  closeModal.addEventListener('click', hideDownloadModal);
  downloadModal.addEventListener('click', (e)=>{ if(e.target===downloadModal) hideDownloadModal(); });

  function showFilters(){ filtersPanel.classList.remove('hidden'); setTimeout(()=>filtersPanel.classList.add('show'),10); chevron.style.transform='rotate(180deg)'; filterText.textContent='Ocultar Filtros'; }
  function hideFilters(){ filtersPanel.classList.remove('show'); setTimeout(()=>filtersPanel.classList.add('hidden'),300); chevron.style.transform='rotate(0deg)'; filterText.textContent='Mostrar Filtros'; }
  function showDownloadModal(){ downloadModal.classList.remove('hidden'); downloadModal.classList.add('flex'); }
  function hideDownloadModal(){ downloadModal.classList.add('hidden'); downloadModal.classList.remove('flex'); }
});
</script>
@endsection
