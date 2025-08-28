@extends('layouts.app')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
  <h1 class="text-2xl font-bold text-gray-900 flex items-center">
    <i class="fas fa-history text-indigo-600 mr-3"></i> Historial de Pedidos
  </h1>
  <div class="flex gap-2 mt-3 sm:mt-0">
    <button id="toggleFilters" 
            class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
      <i class="fas fa-filter mr-2 text-sm"></i> 
      <span class="filter-text">Mostrar Filtros</span>
      <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-200" id="filterChevron"></i>
    </button>
<button id="downloadReportBtn"
  class="inline-flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm
         bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors"
  style="background:#16a34a; color:#fff; border-color:transparent">
  <i class="fa-solid fa-download"></i>
  <span>Descargar</span>
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
    <div class="mb-6 rounded-xl bg-green-50 text-green-800 px-4 py-3 flex items-center border border-green-200">
      <i class="fas fa-check-circle text-green-500 mr-3"></i>
      <span>{{ session('ok') }}</span>
    </div>
  @endif
  
  @if($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 text-red-800 px-4 py-3 border border-red-200">
      <div class="flex items-center">
        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
        <div>
          @foreach($errors->all() as $e) 
            <div>{{ $e }}</div> 
          @endforeach
        </div>
      </div>
    </div>
  @endif

  {{-- Filtros Rápidos por Período --}}
  <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100">
    <div class="flex flex-wrap gap-2 mb-4">
      <span class="text-sm font-medium text-gray-700 flex items-center py-2">
        <i class="fas fa-clock text-gray-500 mr-2"></i> Período:
      </span>
      
      @php
        $currentPeriod = request('period', '');
        $periods = [
          '' => ['🗓️', 'Todos'],
          'today' => ['📅', 'Hoy'],
          'yesterday' => ['⏪', 'Ayer'],
          'this_week' => ['📊', 'Esta semana'],
          'last_week' => ['📉', 'Semana pasada'],
          'last_7_days' => ['🗓️', 'Últimos 7 días'],
          'this_month' => ['📈', 'Este mes'],
          'last_month' => ['📊', 'Mes pasado'],
          'last_30_days' => ['📋', 'Últimos 30 días'],
        ];
      @endphp
      
      @foreach($periods as $period => $data)
        <a href="{{ request()->fullUrlWithQuery(['period' => $period ?: null, 'from' => null, 'to' => null, 'page' => null]) }}" 
           class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                  {{ $currentPeriod === $period ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
          <span class="mr-1">{{ $data[0] }}</span>
          {{ $data[1] }}
        </a>
      @endforeach
    </div>
  </div>

  {{-- Búsqueda Rápida Siempre Visible --}}
  <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100">
    <div class="flex flex-col sm:flex-row gap-3">
      <div class="flex-1">
        <form method="GET" class="flex gap-2">
          <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" 
                   name="q" 
                   value="{{ request('q') }}" 
                   placeholder="Buscar por ID, cliente, notas…"
                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
          </div>
          
          {{-- Mantener filtros existentes como campos ocultos --}}
          @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
          @endif
          @if(request('period'))
            <input type="hidden" name="period" value="{{ request('period') }}">
          @endif
          @if(request('from'))
            <input type="hidden" name="from" value="{{ request('from') }}">
          @endif
          @if(request('to'))
            <input type="hidden" name="to" value="{{ request('to') }}">
          @endif
          
          <button type="submit" 
                  class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            <i class="fas fa-search"></i>
          </button>
        </form>
      </div>
      
      {{-- Filtros activos --}}
      @if(request()->anyFilled(['status', 'period', 'from', 'to', 'q']))
        <div class="flex flex-wrap items-center gap-2">
          <span class="text-xs text-gray-500">Filtros activos:</span>
          
          @if(request('status'))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800">
              Estado: {{ ucfirst(request('status')) }}
              <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="ml-1 hover:text-indigo-600">
                <i class="fas fa-times text-xs"></i>
              </a>
            </span>
          @endif
          
          @if(request('period'))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800">
              Período: {{ $periods[request('period')][1] ?? request('period') }}
              <a href="{{ request()->fullUrlWithQuery(['period' => null]) }}" class="ml-1 hover:text-purple-600">
                <i class="fas fa-times text-xs"></i>
              </a>
            </span>
          @endif
          
          @if(request('from'))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
              Desde: {{ request('from') }}
              <a href="{{ request()->fullUrlWithQuery(['from' => null]) }}" class="ml-1 hover:text-blue-600">
                <i class="fas fa-times text-xs"></i>
              </a>
            </span>
          @endif
          
          @if(request('to'))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
              Hasta: {{ request('to') }}
              <a href="{{ request()->fullUrlWithQuery(['to' => null]) }}" class="ml-1 hover:text-green-600">
                <i class="fas fa-times text-xs"></i>
              </a>
            </span>
          @endif
          
          <a href="{{ route('orders.index') }}" 
             class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700 hover:bg-gray-200">
            <i class="fas fa-times mr-1"></i> Limpiar todo
          </a>
        </div>
      @endif
    </div>
  </div>

  {{-- Panel de Filtros Avanzados (Colapsable) --}}
  <div id="filtersPanel" class="hidden bg-white rounded-xl shadow-sm p-5 mb-6 border border-gray-100 transition-all duration-300">
    <div class="mb-4 pb-3 border-b border-gray-100">
      <h3 class="text-lg font-semibold text-gray-800 flex items-center">
        <i class="fas fa-sliders-h text-indigo-600 mr-2"></i>
        Filtros Avanzados
      </h3>
    </div>
    
    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
      {{-- Mantener búsqueda actual --}}
      @if(request('q'))
        <input type="hidden" name="q" value="{{ request('q') }}">
      @endif
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
          <i class="fas fa-flag text-gray-500 mr-2 text-sm"></i> Estado
        </label>
        <select name="status" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors">
          <option value="">Todos los estados</option>
          <option value="completed" {{ request('status')==='completed'?'selected':'' }}>✅ Completado</option>
          <option value="draft"     {{ request('status')==='draft'?'selected':'' }}>📝 Borrador</option>
          <option value="canceled"  {{ request('status')==='canceled'?'selected':'' }}>❌ Cancelado</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
          <i class="fas fa-calendar-alt text-gray-500 mr-2 text-sm"></i> Fecha Desde
        </label>
        <input type="date" name="from" value="{{ request('from') }}"
               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
          <i class="fas fa-calendar-alt text-gray-500 mr-2 text-sm"></i> Fecha Hasta
        </label>
        <input type="date" name="to" value="{{ request('to') }}"
               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors">
      </div>

      <div class="flex gap-2">
        <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2.5 rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center">
          <i class="fas fa-search mr-2"></i> Aplicar
        </button>
        <a href="{{ route('orders.index') }}"
           class="flex-1 text-center border border-gray-300 px-4 py-2.5 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center">
          <i class="fas fa-eraser mr-2"></i> Reset
        </a>
      </div>
    </form>
  </div>

  {{-- Estadísticas Compactas --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500">Total</p>
          <p class="text-lg font-bold text-gray-800">{{ $orders->total() }}</p>
        </div>
        <div class="rounded-lg bg-indigo-50 p-2">
          <i class="fas fa-shopping-basket text-indigo-600"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500">Completados</p>
          <p class="text-lg font-bold text-emerald-600">{{ $orders->where('status', 'completed')->count() }}</p>
        </div>
        <div class="rounded-lg bg-emerald-50 p-2">
          <i class="fas fa-check-circle text-emerald-600"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500">Borradores</p>
          <p class="text-lg font-bold text-amber-600">{{ $orders->where('status', 'draft')->count() }}</p>
        </div>
        <div class="rounded-lg bg-amber-50 p-2">
          <i class="fas fa-edit text-amber-600"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500">Cancelados</p>
          <p class="text-lg font-bold text-rose-600">{{ $orders->where('status', 'canceled')->count() }}</p>
        </div>
        <div class="rounded-lg bg-rose-50 p-2">
          <i class="fas fa-times-circle text-rose-600"></i>
        </div>
      </div>
    </div>
  </div>

  {{-- Resumen de resultados compacto --}}
  @if($orders->total() > 0)
    <div class="mb-4 flex items-center justify-between text-sm">
      <div class="text-gray-600 bg-blue-50 rounded-lg px-3 py-1.5 flex items-center">
        <i class="fas fa-info-circle text-blue-500 mr-2 text-xs"></i>
        {{ $orders->firstItem() }}–{{ $orders->lastItem() }} de {{ $orders->total() }}
      </div>
      
      <div class="flex items-center gap-3">
        {{-- Botón de descarga adicional --}}
        <button id="downloadReportBtn2" 
                class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium">
          <i class="fas fa-download mr-1.5 text-xs"></i> 
          <span class="hidden sm:inline">Exportar</span>
          <span class="sm:hidden">📊</span>
        </button>
        
        {{-- Ordenamiento rápido --}}
        <div class="flex items-center gap-2">
          <span class="text-gray-500 text-xs">Ordenar:</span>
          <select onchange="window.location.href = this.value" class="text-xs border-gray-300 rounded px-2 py-1">
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>
              🆕 Más recientes
            </option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}" {{ request('sort') === 'oldest' ? 'selected' : '' }}>
              📅 Más antiguos
            </option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'total_desc']) }}" {{ request('sort') === 'total_desc' ? 'selected' : '' }}>
              💰 Mayor valor
            </option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'total_asc']) }}" {{ request('sort') === 'total_asc' ? 'selected' : '' }}>
              💸 Menor valor
            </option>
          </select>
        </div>
      </div>
    </div>
  @endif

  {{-- Lista de Pedidos Optimizada --}}
  <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
    @forelse($orders as $o)
      <div class="border-b border-gray-50 last:border-0 hover:bg-gray-25 transition-colors">
        <div class="p-4">
          <div class="flex items-center justify-between">
            {{-- Info principal más compacta --}}
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2">
                <span class="font-mono text-sm font-bold text-indigo-700">#{{ $o->id }}</span>
                @php
                  $badge = match($o->status){
                    'completed' => 'bg-emerald-100 text-emerald-700',
                    'canceled'  => 'bg-rose-100 text-rose-700',
                    'draft'     => 'bg-amber-100 text-amber-700',
                    default     => 'bg-gray-100 text-gray-700',
                  };
                  $emoji = match($o->status){
                    'completed' => '✅',
                    'canceled'  => '❌',
                    'draft'     => '📝',
                    default     => '⏱️',
                  };
                @endphp
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badge }}">
                  {{ $emoji }} {{ ucfirst($o->status) }}
                </span>
              </div>
              
              <div class="hidden sm:block text-xs text-gray-500">
                {{ $o->created_at?->format('d/m/Y H:i') }}
              </div>
              
              <div class="max-w-xs">
                <div class="font-medium text-gray-900 truncate text-sm">
                  {{ $o->guest_name ?? $o->customer_name ?? 'Sin cliente' }}
                </div>
                @if(!empty($o->note))
                  <div class="text-xs text-gray-500 truncate">
                    💬 {{ $o->note }}
                  </div>
                @endif
              </div>
            </div>
            
            {{-- Stats y acciones compactas --}}
            <div class="flex items-center gap-4">
              <div class="text-right text-sm">
                <div class="flex items-center gap-3">
                  <div class="text-center">
                    <div class="text-xs text-gray-500">Items</div>
                    <div class="font-semibold">{{ (int) ($o->items_qty ?? optional($o->items)->sum('quantity') ?? 0) }}</div>
                  </div>
                  <div class="text-center">
                    <div class="text-xs text-gray-500">Total</div>
                    <div class="font-bold text-gray-900">${{ number_format($o->total, 2, ',', '.') }}</div>
                  </div>
                </div>
              </div>
              
              <a href="{{ route('orders.show', $o) }}" 
                 class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                👁️ Ver
              </a>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="p-12 text-center">
        <div class="mb-4">
          <i class="fas fa-search text-gray-300 text-5xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron pedidos</h3>
        <p class="text-gray-500 mb-4">Intenta ajustar los filtros de búsqueda</p>
        <div class="flex justify-center gap-2">
          <a href="{{ route('orders.index') }}" 
             class="inline-flex items-center px-3 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
            🧹 Limpiar filtros
          </a>
          <a href="{{ route('orders.create') }}" 
             class="inline-flex items-center px-3 py-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
            ➕ Crear pedido
          </a>
        </div>
      </div>
    @endforelse
  </div>

  {{-- Paginación --}}
  @if($orders->hasPages())
    <div class="mt-6">
      {{ $orders->withQueryString()->links() }}
    </div>
  @endif
</div>

{{-- Modal de Descarga --}}
<div id="downloadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900 flex items-center">
        <i class="fas fa-download text-emerald-600 mr-2"></i>
        Descargar Reporte
      </h3>
      <button id="closeModal" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <p class="text-gray-600 mb-4">Selecciona el formato del reporte:</p>
    
    <div class="space-y-3">
      <a href="{{ route('orders.download-report', array_merge(request()->query(), ['format' => 'csv'])) }}" 
         class="w-full flex items-center justify-between p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <div class="flex items-center">
          <div class="bg-green-100 p-2 rounded-lg mr-3">
            <i class="fas fa-file-csv text-green-600"></i>
          </div>
          <div>
            <div class="font-medium text-gray-900">CSV (Excel)</div>
            <div class="text-sm text-gray-500">Formato separado por comas</div>
          </div>
        </div>
        <i class="fas fa-download text-gray-400"></i>
      </a>
      
      <a href="{{ route('orders.download-report', array_merge(request()->query(), ['format' => 'excel'])) }}" 
         class="w-full flex items-center justify-between p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <div class="flex items-center">
          <div class="bg-blue-100 p-2 rounded-lg mr-3">
            <i class="fas fa-file-excel text-blue-600"></i>
          </div>
          <div>
            <div class="font-medium text-gray-900">Excel (XLS)</div>
            <div class="text-sm text-gray-500">Con formato y totales</div>
          </div>
        </div>
        <i class="fas fa-download text-gray-400"></i>
      </a>
    </div>
    
    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
      <div class="flex items-start">
        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
        <div class="text-sm text-blue-700">
          <strong>Se incluirán:</strong> {{ $orders->total() }} pedidos con los filtros actuales aplicados.
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  .transition-all {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  .transition-colors {
    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
  }
  
  .transition-transform {
    transition: transform 0.2s ease;
  }

  .bg-gray-25 {
    background-color: #fafafa;
  }
  
  #filtersPanel {
    transform: translateY(-10px);
    opacity: 0;
  }
  
  #filtersPanel.show {
    transform: translateY(0);
    opacity: 1;
  }
  .downloadReportBtn {
    text: #000000ff
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const toggleButton = document.getElementById('toggleFilters');
  const filtersPanel = document.getElementById('filtersPanel');
  const chevron = document.getElementById('filterChevron');
  const filterText = document.querySelector('.filter-text');
  
  // Modal de descarga
  const downloadBtn = document.getElementById('downloadReportBtn');
  const downloadBtn2 = document.getElementById('downloadReportBtn2');
  const downloadModal = document.getElementById('downloadModal');
  const closeModal = document.getElementById('closeModal');
  
  // Estado inicial - mostrar si hay filtros activos
  const hasActiveFilters = {{ request()->anyFilled(['status', 'from', 'to']) ? 'true' : 'false' }};
  if (hasActiveFilters) {
    showFilters();
  }
  
  toggleButton.addEventListener('click', function() {
    if (filtersPanel.classList.contains('hidden')) {
      showFilters();
    } else {
      hideFilters();
    }
  });
  
  // Modal de descarga - ambos botones
  function showDownloadModal() {
    downloadModal.classList.remove('hidden');
    downloadModal.classList.add('flex');
  }
  
  downloadBtn.addEventListener('click', showDownloadModal);
  if (downloadBtn2) {
    downloadBtn2.addEventListener('click', showDownloadModal);
  }
  
  closeModal.addEventListener('click', function() {
    downloadModal.classList.add('hidden');
    downloadModal.classList.remove('flex');
  });
  
  // Cerrar modal al hacer click fuera
  downloadModal.addEventListener('click', function(e) {
    if (e.target === downloadModal) {
      downloadModal.classList.add('hidden');
      downloadModal.classList.remove('flex');
    }
  });
  
  function showFilters() {
    filtersPanel.classList.remove('hidden');
    setTimeout(() => filtersPanel.classList.add('show'), 10);
    chevron.style.transform = 'rotate(180deg)';
    filterText.textContent = 'Ocultar Filtros';
  }
  
  function hideFilters() {
    filtersPanel.classList.remove('show');
    setTimeout(() => filtersPanel.classList.add('hidden'), 300);
    chevron.style.transform = 'rotate(0deg)';
    filterText.textContent = 'Mostrar Filtros';
  }
  
  // Mejorar la experiencia de los filtros
  const forms = document.querySelectorAll('form[method="GET"]');
  forms.forEach(form => {
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
      form.addEventListener('submit', function() {
        const originalContent = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Buscando...';
        submitButton.disabled = true;
        
        // Restaurar después de un tiempo en caso de error
        setTimeout(() => {
          submitButton.innerHTML = originalContent;
          submitButton.disabled = false;
        }, 5000);
      });
    }
  });
});
</script>
@endsection