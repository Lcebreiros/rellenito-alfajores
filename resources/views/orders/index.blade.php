@extends('layouts.app')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 flex items-center">
    <i class="fas fa-history text-indigo-600 mr-3"></i> Historial de Pedidos
  </h1>
  <div class="flex gap-2 mt-3 sm:mt-0">
    <button id="toggleFilters" type="button"
            class="inline-flex items-center px-3 py-2 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 rounded-lg font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
      <i class="fas fa-filter mr-2 text-sm"></i>
      <span class="filter-text">Mostrar Filtros</span>
      <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-200" id="filterChevron"></i>
    </button>

    {{-- Botón Nuevo Pedido --}}
    <livewire:order-quick-modal />

    {{-- Abre modal de descarga --}}
    <button data-modal-open="downloadModal" id="downloadReportBtn" type="button"
      class="inline-flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors">
      <i class="fa-solid fa-download"></i><span>Descargar</span>
    </button>
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

  {{-- Filtros rápidos --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm p-4 mb-6 border border-neutral-100 dark:border-neutral-800">
    <div class="flex flex-wrap gap-2 mb-2">
      <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300 flex items-center py-2">
        <i class="fas fa-clock text-neutral-500 dark:text-neutral-400 mr-2"></i> Período:
      </span>
      @php
        $currentPeriod = request('period','');
        $periods = [
          '' => 'Todos','today' => 'Hoy','yesterday' => 'Ayer','this_week' => 'Esta semana',
          'last_week' => 'Semana pasada','last_7_days' => 'Últimos 7 días','this_month' => 'Este mes',
          'last_month' => 'Mes pasado','last_30_days' => 'Últimos 30 días',
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

  {{-- Búsqueda + filtros avanzados --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm p-4 mb-6 border border-neutral-100 dark:border-neutral-800">
    <div class="flex flex-col sm:flex-row gap-3">
      <div class="flex-1">
        <form method="GET" class="flex gap-2">
          <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por ID, cliente, notas…"
                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
          </div>
          @foreach(['status','period','from','to','client','client_id'] as $keep)
            @if(request($keep)) <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}"> @endif
          @endforeach
          <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            <i class="fas fa-search"></i>
          </button>
        </form>
      </div>

      {{-- Filtros activos --}}
      @if(request()->anyFilled(['status','period','from','to','q','client','client_id']))
        <div class="flex flex-wrap items-center gap-2">
          <span class="text-xs text-neutral-500 dark:text-neutral-400">Filtros activos:</span>
          @foreach(['status'=>'Estado','period'=>'Período','from'=>'Desde','to'=>'Hasta','client'=>'Cliente','client_id'=>'Cliente ID'] as $key=>$label)
            @if(request($key))
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200">
                {{ $label }}: {{ $key==='period' ? ($periods[request('period')] ?? request('period')) : request($key) }}
                <a href="{{ request()->fullUrlWithQuery([$key=>null]) }}" class="ml-1 hover:opacity-70"><i class="fas fa-times text-xs"></i></a>
              </span>
            @endif
          @endforeach
          <a href="{{ route('orders.index') }}"
             class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700">
            <i class="fas fa-times mr-1"></i> Limpiar todo
          </a>
        </div>
      @endif
    </div>
  </div>

  {{-- Panel de Filtros Avanzados (colapsable) --}}
  <div id="filtersPanel" class="hidden bg-white dark:bg-neutral-900 rounded-xl shadow-sm p-5 mb-6 border border-neutral-100 dark:border-neutral-800 transition-all duration-300"
       aria-hidden="true">
    <div class="mb-4 pb-3 border-b border-neutral-100 dark:border-neutral-800">
      <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center">
        <i class="fas fa-sliders-h text-indigo-600 mr-2"></i> Filtros Avanzados
      </h3>
    </div>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
      @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Estado</label>
        <select name="status" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors">
          <option value="">Todos</option>
          <option value="completed" {{ request('status')==='completed'?'selected':'' }}>Completado</option>
          <option value="draft"     {{ request('status')==='draft'?'selected':'' }}>Borrador</option>
          <option value="canceled"  {{ request('status')==='canceled'?'selected':'' }}>Cancelado</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Fecha Desde</label>
        <input type="date" name="from" value="{{ request('from') }}"
               class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors">
      </div>
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Fecha Hasta</label>
        <input type="date" name="to" value="{{ request('to') }}"
               class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors">
      </div>
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Cliente (texto)</label>
        <input type="text" name="client" value="{{ request('client') }}" placeholder="Nombre del cliente"
               class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors">
      </div>
      <div class="md:col-span-2 lg:col-span-4 flex gap-2">
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

{{-- Resumen + orden --}}
@if($orders->total() > 0)
<div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm">
  
  {{-- Información de items --}}
  <div class="flex items-center gap-2 bg-blue-50 dark:bg-neutral-800/40 text-blue-600 dark:text-blue-300 rounded-lg px-3 py-1.5">
    <i class="fas fa-info-circle text-xs"></i>
    <span>{{ $orders->firstItem() }}–{{ $orders->lastItem() }} de {{ $orders->total() }}</span>
  </div>

  {{-- Selector de orden --}}
  <div class="flex items-center gap-2">
    <span class="text-neutral-500 dark:text-neutral-400 text-xs">Ordenar:</span>
    <div class="relative">
      <select onchange="window.location.href=this.value"
              class="appearance-none text-xs border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md pl-2 pr-6 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
        <option value="{{ request()->fullUrlWithQuery(['sort'=>'newest']) }}" {{ request('sort','newest')==='newest'?'selected':'' }}>Más recientes</option>
        <option value="{{ request()->fullUrlWithQuery(['sort'=>'oldest']) }}" {{ request('sort')==='oldest'?'selected':'' }}>Más antiguos</option>
        <option value="{{ request()->fullUrlWithQuery(['sort'=>'total_desc']) }}" {{ request('sort')==='total_desc'?'selected':'' }}>Mayor valor</option>
        <option value="{{ request()->fullUrlWithQuery(['sort'=>'total_asc']) }}" {{ request('sort')==='total_asc'?'selected':'' }}>Menor valor</option>
      </select>
    </div>
  </div>

</div>
@endif


  {{-- Tabla --}}
  @php
    $statusBadge = fn($s) => match($s){
      'completed' => ['text'=>'Completado','cls'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
      'canceled'  => ['text'=>'Cancelado','cls'=>'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'],
      'draft'     => ['text'=>'Borrador','cls'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
      default     => ['text'=>ucfirst($s??'—'),'cls'=>'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-100'],
    };
    $receiptRoute = \Illuminate\Support\Facades\Route::has('orders.ticket');
    $fmt = fn($n)=> '$'.number_format((float)$n,2,',','.');
  @endphp

{{-- Botón para eliminar múltiples (más profesional y pequeño) --}}
<div class="mb-3 flex justify-end">
    <button id="deleteSelected"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-rose-600 rounded-md hover:bg-rose-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
            disabled>
        <i class="fas fa-trash-alt text-sm"></i> Eliminar
    </button>
</div>


{{-- Tabla de órdenes --}}
<div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[980px] text-sm">
            <thead class="sticky top-0 z-10">
                <tr class="bg-neutral-100/80 dark:bg-neutral-800/60 backdrop-blur">
                    <th class="px-3 py-3 text-center">
                        <input type="checkbox" id="selectAll" class="rounded border-neutral-300 dark:border-neutral-700">
                    </th>
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
                    @php $badge = $statusBadge($o->status); @endphp
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                        <td class="px-3 py-3 text-center">
                            <input type="checkbox" class="selectOrder" value="{{ $o->id }}">
                        </td>
                        <td class="px-6 py-3 font-semibold text-neutral-900 dark:text-neutral-100">#{{ $o->id }}</td>
                        <td class="px-3 py-3">
                            <div class="max-w-xs">
                                <div class="font-medium text-neutral-800 dark:text-neutral-100 truncate">
                                    {{ optional($o->client)->name ?? 'Sin cliente' }}
                                </div>
                                @if(!empty($o->note))
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                                        <i class="far fa-comment mr-1"></i>{{ $o->note }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-3 text-neutral-700 dark:text-neutral-200 whitespace-nowrap">{{ $o->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-3 text-neutral-700 dark:text-neutral-200">{{ (int)($o->items_qty ?? 0) }}</td>
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
                        <td colspan="8" class="px-6 py-16 text-center text-neutral-500 dark:text-neutral-400">
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

{{-- Script de selección y eliminación --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('selectAll');
    const orderCheckboxes = document.querySelectorAll('.selectOrder');
    const deleteBtn = document.getElementById('deleteSelected');

    // Seleccionar/Deseleccionar todo
    selectAll?.addEventListener('change', () => {
        orderCheckboxes.forEach(cb => cb.checked = selectAll.checked);
        deleteBtn.disabled = ![...orderCheckboxes].some(cb => cb.checked);
    });

    // Checkbox individual
    orderCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            selectAll.checked = [...orderCheckboxes].every(c => c.checked);
            deleteBtn.disabled = ![...orderCheckboxes].some(c => c.checked);
        });
    });

    // Botón eliminar
    deleteBtn?.addEventListener('click', () => {
        const ids = [...orderCheckboxes].filter(c => c.checked).map(c => c.value);
        if (!ids.length) return;
        if (!confirm(`¿Eliminar ${ids.length} pedidos? Esta acción no se puede deshacer.`)) return;

        fetch('{{ route("orders.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Error al eliminar los pedidos');
        });
    });
});
</script>

  {{-- Paginación --}}
  @if($orders->hasPages())
    <div class="mt-6">{{ $orders->withQueryString()->links() }}</div>
  @endif
</div>

{{-- Modal de Descarga --}}
<div id="downloadModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" role="dialog" aria-modal="true" aria-labelledby="downloadTitle">
  <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 max-w-md w-full mx-4 border border-neutral-100 dark:border-neutral-800" role="document">
    <div class="flex items-center justify-between mb-4">
      <h3 id="downloadTitle" class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 flex items-center">
        <i class="fas fa-download text-emerald-600 mr-2"></i> Descargar Reporte
      </h3>
      <button id="closeModal" type="button" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300" aria-label="Cerrar">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <p class="text-neutral-600 dark:text-neutral-300 mb-4">Seleccioná el formato del reporte:</p>
    <div class="space-y-3">
      <a href="{{ route('orders.download-report', array_merge(request()->query(), ['format'=>'csv'])) }}"
         class="w-full flex items-center justify-between p-3 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
        <div class="flex items-center">
          <div class="bg-green-100 dark:bg-emerald-900/30 p-2 rounded-lg mr-3">
            <i class="fas fa-file-csv text-green-600 dark:text-emerald-300"></i>
          </div>
          <div>
            <div class="font-medium text-neutral-900 dark:text-neutral-100">CSV (Excel)</div>
            <div class="text-sm text-neutral-500 dark:text-neutral-400">UTF-8 con separador ;</div>
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
            <div class="text-sm text-neutral-500 dark:text-neutral-400">Tabla HTML compatible</div>
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
(function (){
  const ready = (fn) => {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once:true });
    } else {
      fn();
    }
  };

  ready(() => {
    const filtersPanel = document.getElementById('filtersPanel');
    const toggleFiltersBtn = document.getElementById('toggleFilters');
    const chevron = document.getElementById('filterChevron');
    const filterText = document.querySelector('.filter-text');

    const modalId = 'downloadModal';
    const modal = document.getElementById(modalId);
    const closeBtn = document.getElementById('closeModal');

    // Filtros: null-safe
    if (toggleFiltersBtn && filtersPanel && chevron && filterText) {
      const hasActive = {{ request()->anyFilled(['status','from','to','client','client_id']) ? 'true' : 'false' }};
      const showFilters = () => {
        filtersPanel.classList.remove('hidden');
        requestAnimationFrame(() => filtersPanel.classList.add('show'));
        chevron.style.transform = 'rotate(180deg)';
        filterText.textContent = 'Ocultar Filtros';
        filtersPanel.setAttribute('aria-hidden', 'false');
      };
      const hideFilters = () => {
        filtersPanel.classList.remove('show');
        setTimeout(() => filtersPanel.classList.add('hidden'), 300);
        chevron.style.transform = 'rotate(0deg)';
        filterText.textContent = 'Mostrar Filtros';
        filtersPanel.setAttribute('aria-hidden', 'true');
      };
      toggleFiltersBtn.addEventListener('click', () =>
        filtersPanel.classList.contains('hidden') ? showFilters() : hideFilters()
      );
      if (hasActive) showFilters();
    }

    // Modal: null-safe
    if (!modal) return;

    const openers = document.querySelectorAll(`[data-modal-open="${modalId}"]`);
    const open = () => {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      closeBtn?.focus?.();
      const onEsc = (e) => {
        if (e.key === 'Escape') {
          close();
          document.removeEventListener('keydown', onEsc);
        }
      };
      document.addEventListener('keydown', onEsc);
    };
    const close = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    };

    openers.forEach(btn => btn.addEventListener('click', open));
    closeBtn?.addEventListener('click', close);
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
  });
})();
</script>
@endsection


