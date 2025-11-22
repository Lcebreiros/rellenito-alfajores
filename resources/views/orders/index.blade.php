@extends('layouts.app')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
  <div class="flex items-center gap-3">
    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-3">
      <x-svg-icon name="document" size="6" class="text-indigo-600" /> Historial de Pedidos
    </h1>
  </div>

  <div class="flex flex-wrap items-center gap-2">
    {{-- Nuevo Pedido --}}
    <div class="inline-flex">
      <livewire:order-quick-modal />
    </div>

    {{-- Importar CSV --}}
    <form method="POST" action="{{ route('orders.import-csv') }}" enctype="multipart/form-data" class="inline-flex">
      @csrf
      <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer transition-colors">
        <x-svg-icon name="document" size="5" />
        <span class="hidden sm:inline">Importar CSV</span>
        <input type="file" name="csv" accept=".csv,text/csv" class="hidden" onchange="this.form.submit()" />
      </label>
    </form>

    {{-- Descargar Reporte --}}
    <button data-modal-open="downloadModal" id="downloadReportBtn" type="button"
      class="inline-flex items-center gap-2 px-3 py-2 rounded-lg shadow-sm bg-green-600 text-white font-semibold hover:bg-green-700 transition-all duration-150 active:scale-[0.98]">
      <x-svg-icon name="download" size="5" />
      <span class="hidden sm:inline">Descargar</span>
    </button>

    {{-- Mostrar/Ocultar Filtros --}}
    <button id="toggleFilters" type="button"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
      <x-svg-icon name="filter" size="4" />
      <span class="hidden sm:inline filter-text">Mostrar Filtros</span>
      <x-svg-icon name="chevron-down" size="3" class="transition-transform duration-200" id="filterChevron" />
    </button>
  </div>
</div>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">

  {{-- Mensajes --}}
  @if(session('ok'))
    <div class="mb-6 rounded-xl bg-green-50 text-green-800 px-4 py-3 flex items-center gap-3 border border-green-200
                dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800">
      <x-svg-icon name="check" size="5" class="text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
      <span>{{ session('ok') }}</span>
    </div>
  @endif
  @if($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 text-red-800 px-4 py-3 border border-red-200
                dark:bg-rose-900/30 dark:text-rose-200 dark:border-rose-800">
      <div class="flex items-center gap-3">
        <x-svg-icon name="exclamation" size="5" class="text-rose-600 dark:text-rose-400 flex-shrink-0" />
        <div>@foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div>
      </div>
    </div>
  @endif
  @if(session('import_errors'))
    <div class="mb-6 rounded-xl bg-amber-50 text-amber-800 px-4 py-3 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800">
      <div class="font-semibold mb-1">Errores de importación</div>
      <ul class="list-disc ml-5 text-sm">
        @foreach(session('import_errors') as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Filtros rápidos --}}
  <div class="panel-glass shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-2 mb-2">
      <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300 flex items-center gap-2 py-2">
        <x-svg-icon name="calendar" size="4" class="text-neutral-400" /> Período:
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
  <div class="panel-glass shadow-sm p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-3">
      <div class="flex-1">
        <form method="GET" class="flex gap-2">
          <div class="flex-1 relative">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">
              <x-svg-icon name="search" size="5" />
            </div>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por ID, cliente, notas…"
                   class="input-enhanced w-full pl-10 pr-4 py-2.5">
          </div>
          @foreach(['status','period','from','to','client','client_id'] as $keep)
            @if(request($keep)) <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}"> @endif
          @endforeach
          <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
            <x-svg-icon name="search" size="5" />
          </button>
        </form>
      </div>

      {{-- Filtros activos --}}
      @if(request()->anyFilled(['status','period','from','to','q','client','client_id']))
        <div class="flex flex-wrap items-center gap-2">
          <span class="text-xs text-neutral-500 dark:text-neutral-400">Filtros activos:</span>
          @foreach(['status'=>'Estado','period'=>'Período','from'=>'Desde','to'=>'Hasta','client'=>'Cliente','client_id'=>'Cliente ID'] as $key=>$label)
            @if(request($key))
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200">
                {{ $label }}: {{ $key==='period' ? ($periods[request('period')] ?? request('period')) : request($key) }}
                <a href="{{ request()->fullUrlWithQuery([$key=>null]) }}" class="hover:opacity-70">
                  <x-svg-icon name="x" size="3" />
                </a>
              </span>
            @endif
          @endforeach
          <a href="{{ route('orders.index') }}"
             class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700 transition-colors">
            <x-svg-icon name="x" size="3" /> Limpiar todo
          </a>
        </div>
      @endif
    </div>
  </div>

  {{-- Panel de Filtros Avanzados (colapsable) --}}
  <div id="filtersPanel" class="hidden container-glass shadow-sm p-5 mb-6 transition-all duration-300"
       aria-hidden="true">
    <div class="mb-4 pb-3 border-b border-neutral-200/50 dark:border-neutral-700/50">
      <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
        <x-svg-icon name="filter" size="5" class="text-indigo-600" /> Filtros Avanzados
      </h3>
    </div>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
      @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Estado</label>
        <select name="status" class="input-enhanced w-full px-4 py-2.5">
          <option value="">Todos</option>
          <option value="completed" {{ request('status')==='completed'?'selected':'' }}>Completado</option>
          <option value="draft"     {{ request('status')==='draft'?'selected':'' }}>Borrador</option>
          <option value="canceled"  {{ request('status')==='canceled'?'selected':'' }}>Cancelado</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Fecha Desde</label>
        <input type="date" name="from" value="{{ request('from') }}"
               class="input-enhanced w-full px-4 py-2.5">
      </div>
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Fecha Hasta</label>
        <input type="date" name="to" value="{{ request('to') }}"
               class="input-enhanced w-full px-4 py-2.5">
      </div>
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Cliente (texto)</label>
        <input type="text" name="client" value="{{ request('client') }}" placeholder="Nombre del cliente"
               class="input-enhanced w-full px-4 py-2.5">
      </div>
      <div class="md:col-span-2 lg:col-span-4 flex gap-2">
        <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2.5 rounded-lg hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98] flex items-center justify-center gap-2">
          <x-svg-icon name="search" size="4" /> Aplicar
        </button>
        <a href="{{ route('orders.index') }}"
           class="flex-1 text-center border border-neutral-300 dark:border-neutral-700 px-4 py-2.5 text-neutral-700 dark:text-neutral-200 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors flex items-center justify-center gap-2">
          <x-svg-icon name="trash" size="4" /> Reset
        </a>
      </div>
    </form>
  </div>

{{-- Resumen + orden --}}
@if($orders->total() > 0)
<div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm">
  
  {{-- Información de items --}}
  <div class="flex items-center gap-2 bg-blue-50 dark:bg-neutral-800/40 text-blue-600 dark:text-blue-300 rounded-lg px-3 py-1.5">
    <x-svg-icon name="info" size="4" />
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
    $statusBadge = function($s){
      $key = ($s instanceof \BackedEnum) ? $s->value : (($s instanceof \UnitEnum) ? $s->name : (is_string($s) ? $s : (string) ($s ?? '')));
      return match($key){
        'completed' => ['text'=>'Completado','cls'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
        'canceled'  => ['text'=>'Cancelado','cls'=>'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'],
        'draft'     => ['text'=>'Borrador','cls'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
        default     => ['text'=>ucfirst($key ?: '—'),'cls'=>'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-100'],
      };
    };
    $receiptRoute = \Illuminate\Support\Facades\Route::has('orders.ticket');
    $fmt = fn($n)=> '$'.number_format((float)$n,2,',','.');
  @endphp

{{-- Botón para eliminar múltiples (más profesional y pequeño) --}}
<div class="mb-3 flex justify-end">
    <button id="deleteSelected"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-rose-600 rounded-md hover:bg-rose-700 transition-all duration-150 active:scale-[0.98] shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
            disabled>
        <x-svg-icon name="trash" size="4" /> Eliminar
    </button>
</div>


{{-- Tabla de órdenes --}}
<div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[980px] text-sm">
            <thead class="sticky top-0 z-10">
                <tr class="bg-neutral-100/80 dark:bg-neutral-800/60 backdrop-blur">
                    <th class="px-3 py-3 text-center">
                        <input type="checkbox" id="selectAll"
                               class="w-4 h-4 rounded border border-neutral-300 text-indigo-600
                                      dark:border-neutral-700 dark:bg-neutral-700 dark:checked:bg-indigo-500
                                      hover:none focus:none">
                    </th>
                    <th class="text-left px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">Pedido</th>
                    <th class="text-left px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Cliente</th>
                    <th class="text-left px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Fecha</th>
                    @if(!empty($isCompany))
                        <th class="text-left px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Sucursal</th>
                    @endif
                    @if(!empty($isMaster))
                        <th class="text-left px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Usuario</th>
                    @endif
                    <th class="text-left px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Items</th>
                    <th class="text-left px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Total</th>
                    <th class="text-left px-4 py-3 font-medium text-neutral-600 dark:text-neutral-300">Pago</th>
                    <th class="text-left px-2 py-3 font-medium text-neutral-600 dark:text-neutral-300 w-28">Estado</th>
                    <th class="text-right px-3 py-3 font-medium text-neutral-600 dark:text-neutral-300">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse($orders as $o)
                    @php $badge = $statusBadge($o->status); @endphp
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                        <td class="px-3 py-3 text-center">
                            <input type="checkbox" value="{{ $o->id }}" class="selectOrder w-4 h-4 text-indigo-600 bg-white border border-neutral-300 rounded focus:ring-2 focus:ring-indigo-500 dark:bg-neutral-700 dark:border-neutral-600 dark:checked:bg-indigo-500 dark:focus:ring-indigo-400">
                        </td>
                        <td class="px-6 py-3 font-semibold text-neutral-900 dark:text-neutral-100">#{{ $o->order_number ?? $o->id }}</td>
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
                        @if(!empty($isCompany))
                            <td class="px-3 py-3 text-neutral-700 dark:text-neutral-200">{{ optional($o->branch)->name ?? 'Sin sucursal' }}</td>
                        @endif
                        @if(!empty($isMaster))
                            <td class="px-3 py-3 text-neutral-700 dark:text-neutral-200">#{{ $o->user_id }} — {{ $o->user?->name ?? 'N/D' }}</td>
                        @endif
                        <td class="px-3 py-3 text-neutral-700 dark:text-neutral-200">{{ (int)($o->items_qty ?? 0) }}</td>
                        <td class="px-3 py-3 font-semibold text-neutral-900 dark:text-neutral-100">{{ $fmt($o->total) }}</td>
                        <td class="px-4 py-3">
                            @if($o->paymentMethods && $o->paymentMethods->isNotEmpty())
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @foreach($o->paymentMethods->take(3) as $pm)
                                        @if($pm->hasLogo())
                                            <img
                                                src="{{ asset('images/' . $pm->getLogo()) }}"
                                                alt="{{ $pm->name }}"
                                                title="{{ $pm->name }}"
                                                class="h-10 w-auto object-contain rounded"
                                                style="max-width: 56px;"
                                            />
                                        @else
                                            <div class="w-10 h-10 flex items-center justify-center" title="{{ $pm->name }}">
                                                <x-dynamic-component
                                                    :component="'heroicon-o-' . $pm->getIcon()"
                                                    class="w-8 h-8 text-neutral-600 dark:text-neutral-400"
                                                />
                                            </div>
                                        @endif
                                    @endforeach
                                    @if($o->paymentMethods->count() > 3)
                                        <span class="text-xs text-neutral-500 dark:text-neutral-400">+{{ $o->paymentMethods->count() - 3 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-neutral-400 dark:text-neutral-500">—</span>
                            @endif
                        </td>
                        <td class="px-2 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium {{ $badge['cls'] }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $badge['text'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('orders.show',$o) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50 transition-colors">
                                    <x-svg-icon name="eye" size="4" /> Ver
                                </a>
                                <a href="{{ $receiptRoute ? route('orders.ticket',$o) : '#' }}" @if(!$receiptRoute) aria-disabled="true" @endif
                                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/50 transition-colors {{ $receiptRoute ? '' : 'opacity-50 cursor-not-allowed' }}">
                                    <x-svg-icon name="document" size="4" /> Comprobante
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ (!empty($isCompany) ? 10 : 9) + (!empty($isMaster) ? 1 : 0) }}" class="px-6 py-16 text-center text-neutral-500 dark:text-neutral-400">
                            <div class="flex flex-col items-center">
                                <x-svg-icon name="search" size="12" class="text-neutral-300 dark:text-neutral-600 mb-3" />
                                <div class="text-lg font-medium">No se encontraron pedidos</div>
                                <p class="text-neutral-500 dark:text-neutral-400">Ajustá los filtros para ver resultados.</p>
                                <div class="mt-4 flex justify-center gap-2">
                                    <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm text-neutral-700 dark:text-neutral-200 bg-neutral-100 dark:bg-neutral-800 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                                        <x-svg-icon name="x" size="4" /> Limpiar filtros
                                    </a>
                                    <a href="{{ route('orders.create') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
                                        <x-svg-icon name="plus" size="4" /> Crear pedido
                                    </a>
                                </div>
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
<div id="downloadModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" role="dialog" aria-modal="true" aria-labelledby="downloadTitle">
  <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 max-w-md w-full mx-4 border border-neutral-100 dark:border-neutral-800" role="document">
    <div class="flex items-center justify-between mb-4">
      <h3 id="downloadTitle" class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
        <x-svg-icon name="download" size="5" class="text-emerald-600" /> Descargar Reporte
      </h3>
      <button id="closeModal" type="button" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors" aria-label="Cerrar">
        <x-svg-icon name="x" size="5" />
      </button>
    </div>
    <p class="text-neutral-600 dark:text-neutral-300 mb-4">Seleccioná el formato del reporte:</p>
    <div class="space-y-3">
      <a href="{{ route('orders.download-report', array_merge(request()->query(), ['format'=>'csv'])) }}"
         class="w-full flex items-center justify-between p-3 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-all duration-150 hover:scale-[1.02]">
        <div class="flex items-center gap-3">
          <div class="bg-green-100 dark:bg-emerald-900/30 p-2 rounded-lg">
            <x-svg-icon name="document" size="5" class="text-green-600 dark:text-emerald-300" />
          </div>
          <div>
            <div class="font-medium text-neutral-900 dark:text-neutral-100">CSV (Excel)</div>
            <div class="text-sm text-neutral-500 dark:text-neutral-400">UTF-8 con separador ;</div>
          </div>
        </div>
        <x-svg-icon name="download" size="4" class="text-neutral-400" />
      </a>
      <a href="{{ route('orders.download-report', array_merge(request()->query(), ['format'=>'excel'])) }}"
         class="w-full flex items-center justify-between p-3 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-all duration-150 hover:scale-[1.02]">
        <div class="flex items-center gap-3">
          <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg">
            <x-svg-icon name="document" size="5" class="text-blue-600 dark:text-blue-300" />
          </div>
          <div>
            <div class="font-medium text-neutral-900 dark:text-neutral-100">Excel (XLS)</div>
            <div class="text-sm text-neutral-500 dark:text-neutral-400">Tabla HTML compatible</div>
          </div>
        </div>
        <x-svg-icon name="download" size="4" class="text-neutral-400" />
      </a>
    </div>
    <div class="mt-4 p-3 bg-blue-50 dark:bg-neutral-800/40 rounded-lg text-sm text-blue-700 dark:text-neutral-200">
      Se incluirán: {{ $orders->total() }} pedidos con los filtros actuales.
    </div>
  </div>
</div>

<style>
  #filtersPanel{transform:translateY(-10px);opacity:0}
  #filtersPanel.show{transform:translateY(0);opacity:1}
</style>

<script>
// Script para manejo de selección y eliminación de pedidos
(function() {
    'use strict';

    function initializeOrderSelection() {
        const selectAll = document.getElementById('selectAll');
        const orderCheckboxes = document.querySelectorAll('.selectOrder');
        const deleteBtn = document.getElementById('deleteSelected');

        if (!deleteBtn || orderCheckboxes.length === 0) {
            return; // No hay elementos para manejar
        }

        // Función para contar checkboxes marcados
        function getCheckedCount() {
            return Array.from(orderCheckboxes).filter(cb => cb.checked).length;
        }

        // Función para actualizar el estado del botón eliminar
        // El botón se habilita si HAY AL MENOS UN checkbox individual marcado
        function updateDeleteButton() {
            const checkedCount = getCheckedCount();
            deleteBtn.disabled = checkedCount === 0;
            console.log('Checkboxes marcados:', checkedCount, 'Botón deshabilitado:', deleteBtn.disabled);
        }

        // Función para actualizar el estado del checkbox "Seleccionar todo"
        // Este checkbox solo sirve para seleccionar/deseleccionar todos
        function updateSelectAllCheckbox() {
            if (!selectAll) return;

            const checkedCount = getCheckedCount();
            const totalCount = orderCheckboxes.length;

            if (checkedCount === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else if (checkedCount === totalCount) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else {
                // Estado indeterminado cuando hay selección parcial
                selectAll.checked = false;
                selectAll.indeterminate = true;
            }
        }

        // Inicializar estados al cargar
        updateDeleteButton();
        updateSelectAllCheckbox();

        // Evento: Seleccionar/Deseleccionar todo
        if (selectAll) {
            selectAll.addEventListener('click', function(e) {
                // Forzar el comportamiento: si está indeterminado o no marcado, marcar todos
                // Si está marcado, desmarcar todos
                const shouldCheck = !this.checked || this.indeterminate;

                orderCheckboxes.forEach(function(cb) {
                    cb.checked = shouldCheck;
                });

                // Actualizar estados
                this.indeterminate = false;
                this.checked = shouldCheck;

                updateDeleteButton();
            });
        }

        // Evento: Checkbox individual
        orderCheckboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                // Primero actualizar el botón (lo más importante)
                updateDeleteButton();
                // Luego actualizar el checkbox "Seleccionar todo"
                updateSelectAllCheckbox();
            });
        });

        // Evento: Botón eliminar
        deleteBtn.addEventListener('click', async function() {
            const selectedIds = Array.from(orderCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            if (selectedIds.length === 0) {
                alert('No hay pedidos seleccionados');
                return;
            }

            const confirmMessage = selectedIds.length === 1
                ? '¿Eliminar este pedido? Esta acción no se puede deshacer.'
                : `¿Eliminar ${selectedIds.length} pedidos? Esta acción no se puede deshacer.`;

            if (!confirm(confirmMessage)) return;

            // Deshabilitar botón durante la operación
            deleteBtn.disabled = true;
            const originalText = deleteBtn.innerHTML;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i> Eliminando...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                const response = await fetch('{{ route("orders.bulk-delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ids: selectedIds })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Recargar la página para mostrar los cambios
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al eliminar los pedidos');
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error al eliminar pedidos:', error);
                alert('Error de conexión al eliminar los pedidos');
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalText;
            }
        });

        // Limpiar selección cuando se vuelve a la página (back/forward)
        window.addEventListener('pageshow', function(event) {
            // Si la página se carga desde caché
            if (event.persisted) {
                if (selectAll) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                }
                orderCheckboxes.forEach(cb => cb.checked = false);
                updateDeleteButton();
            }
        });
    }

    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', initializeOrderSelection);

    // Ejecutar cuando Livewire navega a esta página (wire:navigate)
    document.addEventListener('livewire:navigated', initializeOrderSelection);
})();

// Script para modal de descarga y filtros
(function() {
    'use strict';

    function initializeModalsAndFilters() {
        // Modal de descarga
        const downloadModal = document.getElementById('downloadModal');
        const downloadBtn = document.getElementById('downloadReportBtn');
        const closeModalBtn = document.getElementById('closeModal');

        if (downloadBtn && downloadModal) {
            // Remover listener anterior si existe
            const newDownloadBtn = downloadBtn.cloneNode(true);
            downloadBtn.parentNode.replaceChild(newDownloadBtn, downloadBtn);

            newDownloadBtn.addEventListener('click', function() {
                downloadModal.classList.remove('hidden');
                downloadModal.classList.add('flex');
            });
        }

        if (closeModalBtn && downloadModal) {
            // Remover listener anterior si existe
            const newCloseBtn = closeModalBtn.cloneNode(true);
            closeModalBtn.parentNode.replaceChild(newCloseBtn, closeModalBtn);

            newCloseBtn.addEventListener('click', function() {
                downloadModal.classList.add('hidden');
                downloadModal.classList.remove('flex');
            });
        }

        // Cerrar modal al hacer clic fuera (sin duplicar listeners)
        if (downloadModal) {
            const newModal = downloadModal.cloneNode(true);
            downloadModal.parentNode.replaceChild(newModal, downloadModal);

            newModal.addEventListener('click', function(e) {
                if (e.target === newModal) {
                    newModal.classList.add('hidden');
                    newModal.classList.remove('flex');
                }
            });
        }

        // Toggle de filtros
        const toggleFiltersBtn = document.getElementById('toggleFilters');
        const filtersPanel = document.getElementById('filtersPanel');
        const filterChevron = document.getElementById('filterChevron');

        if (toggleFiltersBtn && filtersPanel) {
            // Remover listener anterior si existe
            const newToggleBtn = toggleFiltersBtn.cloneNode(true);
            toggleFiltersBtn.parentNode.replaceChild(newToggleBtn, toggleFiltersBtn);

            newToggleBtn.addEventListener('click', function() {
                const isHidden = filtersPanel.classList.contains('hidden');
                const newFilterChevron = document.getElementById('filterChevron');

                if (isHidden) {
                    filtersPanel.classList.remove('hidden');
                    filtersPanel.classList.add('show');
                    filtersPanel.setAttribute('aria-hidden', 'false');
                    newFilterChevron?.classList.add('rotate-180');

                    const filterText = newToggleBtn.querySelector('.filter-text');
                    if (filterText) filterText.textContent = 'Ocultar Filtros';
                } else {
                    filtersPanel.classList.remove('show');
                    filtersPanel.classList.add('hidden');
                    filtersPanel.setAttribute('aria-hidden', 'true');
                    newFilterChevron?.classList.remove('rotate-180');

                    const filterText = newToggleBtn.querySelector('.filter-text');
                    if (filterText) filterText.textContent = 'Mostrar Filtros';
                }
            });
        }
    }

    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', initializeModalsAndFilters);

    // Ejecutar cuando Livewire navega a esta página (wire:navigate)
    document.addEventListener('livewire:navigated', initializeModalsAndFilters);
})();
</script>
@endsection
