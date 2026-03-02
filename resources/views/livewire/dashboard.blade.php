{{-- resources/views/livewire/dashboard.blade.php --}}
<div class="w-full overflow-x-hidden" x-cloak>

  {{-- Estilos para scrollbar personalizado en widgets --}}
  <style>
    .dashboard-widget-scroll {
      scrollbar-width: thin;
      scrollbar-color: rgb(212 212 216 / 0.4) transparent;
    }
    .dark .dashboard-widget-scroll {
      scrollbar-color: rgb(82 82 91 / 0.4) transparent;
    }
    .dashboard-widget-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
    .dashboard-widget-scroll::-webkit-scrollbar-track { background: transparent; }
    .dashboard-widget-scroll::-webkit-scrollbar-thumb {
      background-color: rgb(212 212 216 / 0.4);
      border-radius: 9999px;
      border: 2px solid transparent;
      background-clip: padding-box;
    }
    .dashboard-widget-scroll::-webkit-scrollbar-thumb:hover { background-color: rgb(212 212 216 / 0.7); }
    .dark .dashboard-widget-scroll::-webkit-scrollbar-thumb { background-color: rgb(82 82 91 / 0.4); }
    .dark .dashboard-widget-scroll::-webkit-scrollbar-thumb:hover { background-color: rgb(82 82 91 / 0.7); }
  </style>

  {{-- ═══════════════════════════════════════════════════════ --}}
  {{-- KPI STRIP                                               --}}
  {{-- ═══════════════════════════════════════════════════════ --}}
  @php
    $qs = $quickStats;
    $hs = $healthScore;

    $fmtMoney  = fn ($v) => '$' . number_format((float)$v, 0, ',', '.');
    $fmtChange = function ($c) {
      if ($c === null) return null;
      return ['val' => abs($c) . '%', 'positive' => $c >= 0];
    };

    $scoreColor = match(true) {
      ($hs['score'] ?? 0) >= 80 => '#10b981',
      ($hs['score'] ?? 0) >= 60 => '#3b82f6',
      ($hs['score'] ?? 0) >= 40 => '#f59e0b',
      default                   => '#ef4444',
    };

    $kpiBase = 'rounded-xl p-4 flex flex-col gap-1 min-w-0
                bg-violet-50/50 dark:bg-neutral-900/65 backdrop-blur-sm
                shadow-[0_4px_20px_-2px_rgba(109,40,217,0.07),0_1px_4px_-1px_rgba(109,40,217,0.03)]
                dark:shadow-[0_4px_20px_-2px_rgba(0,0,0,0.4),0_1px_4px_-1px_rgba(0,0,0,0.2)]';
  @endphp

  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 px-4 sm:px-5 lg:px-6 mb-3">

    {{-- Ingresos --}}
    <div class="{{ $kpiBase }}">
      <span class="text-[10px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Ingresos</span>
      <span class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100 truncate tabular-nums">
        {{ $fmtMoney($qs['revenue']['value']) }}
      </span>
      <div class="flex items-center justify-between gap-1 mt-0.5">
        <span class="text-[10px] text-neutral-400 dark:text-neutral-500">últimos 30 días</span>
        @php $rc = $fmtChange($qs['revenue']['change']); @endphp
        @if($rc)
          <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full
                       {{ $rc['positive'] ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400'
                                          : 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400' }}">
            {{ $rc['positive'] ? '↑' : '↓' }} {{ $rc['val'] }}
          </span>
        @endif
      </div>
    </div>

    {{-- Costos --}}
    <div class="{{ $kpiBase }}">
      <span class="text-[10px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Costos</span>
      <span class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100 truncate tabular-nums">
        {{ $fmtMoney($qs['costs']['value']) }}
      </span>
      <div class="flex items-center justify-between gap-1 mt-0.5">
        <span class="text-[10px] text-neutral-400 dark:text-neutral-500">últimos 30 días</span>
        @php $cc = $fmtChange($qs['costs']['change']); @endphp
        @if($cc)
          <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full
                       {{ $cc['positive'] ? 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400'
                                          : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400' }}">
            {{ $cc['positive'] ? '↑' : '↓' }} {{ $cc['val'] }}
          </span>
        @endif
      </div>
    </div>

    {{-- Ganancia --}}
    @php $profit = $qs['profit']['value']; @endphp
    <div class="{{ $kpiBase }}">
      <span class="text-[10px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Ganancia</span>
      <span class="text-xl sm:text-2xl font-bold truncate tabular-nums
                   {{ $profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
        {{ $fmtMoney($profit) }}
      </span>
      <div class="flex items-center justify-between gap-1 mt-0.5">
        <span class="text-[10px] text-neutral-400 dark:text-neutral-500">últimos 30 días</span>
        @php $pc = $fmtChange($qs['profit']['change']); @endphp
        @if($pc)
          <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full
                       {{ $pc['positive'] ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400'
                                          : 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400' }}">
            {{ $pc['positive'] ? '↑' : '↓' }} {{ $pc['val'] }}
          </span>
        @endif
      </div>
    </div>

    {{-- Health Score --}}
    <a href="{{ route('nexum') }}"
       class="group rounded-xl p-4 flex flex-col gap-1 min-w-0 transition-all
              bg-violet-100/60 dark:bg-neutral-900/65 backdrop-blur-sm
              shadow-[0_4px_20px_-2px_rgba(109,40,217,0.10),0_1px_4px_-1px_rgba(109,40,217,0.05)]
              dark:shadow-[0_4px_20px_-2px_rgba(0,0,0,0.4),0_1px_4px_-1px_rgba(0,0,0,0.2)]
              hover:shadow-[0_6px_24px_-2px_rgba(139,92,246,0.18)]
              hover:bg-violet-100/80">
      <span class="text-[10px] font-semibold text-violet-500 dark:text-violet-400 uppercase tracking-widest">Business Health</span>
      @if($hs['score'] !== null)
        <span class="text-xl sm:text-2xl font-bold tabular-nums" style="color: {{ $scoreColor }}">
          {{ $hs['score'] }}<span class="text-sm font-normal text-neutral-400 dark:text-neutral-500">/100</span>
        </span>
        <div class="flex items-center justify-between gap-1 mt-0.5">
          <span class="text-[10px] font-semibold" style="color: {{ $scoreColor }}">{{ $hs['status'] }}</span>
          <span class="text-[10px] text-violet-500 dark:text-violet-400 group-hover:underline">Ver →</span>
        </div>
      @else
        <span class="text-xl sm:text-2xl font-bold text-neutral-400 dark:text-neutral-600">—</span>
        <span class="text-[10px] text-neutral-400 dark:text-neutral-500">Sin datos aún</span>
      @endif
    </a>

  </div>

  {{-- ═══════════════════════════════════════════════════════ --}}
  {{-- ALERTAS CRÍTICAS (AI Insights)                         --}}
  {{-- ═══════════════════════════════════════════════════════ --}}
  @if($criticalAlerts->isNotEmpty())
    <div class="mx-4 sm:mx-5 lg:mx-6 mb-3
                rounded-xl px-4 py-3
                bg-amber-50/80 dark:bg-amber-900/10 backdrop-blur-sm
                shadow-[0_2px_12px_-2px_rgba(245,158,11,0.15)] dark:shadow-[0_2px_12px_-2px_rgba(245,158,11,0.1)]
                ring-1 ring-amber-200/60 dark:ring-amber-800/40
                flex flex-wrap items-center gap-x-3 gap-y-2">
      <div class="flex items-center gap-2 shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
             class="w-4 h-4 text-amber-500 dark:text-amber-400">
          <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
        </svg>
        <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">Alertas activas</span>
      </div>
      <div class="flex flex-wrap gap-2">
        @foreach($criticalAlerts as $alert)
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                       bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300
                       border border-amber-200 dark:border-amber-800/50 max-w-[220px] truncate">
            {{ $alert->title }}
          </span>
        @endforeach
      </div>
      <a href="{{ route('nexum') }}"
         class="ml-auto shrink-0 text-xs font-semibold text-amber-700 dark:text-amber-400 hover:underline whitespace-nowrap">
        Ver en Nexum →
      </a>
    </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════ --}}
  {{-- PANEL: QUÉ NECESITA ATENCIÓN                           --}}
  {{-- ═══════════════════════════════════════════════════════ --}}
  <livewire:dashboard.attention-panel />

  {{-- ═══════════════════════════════════════════════════════ --}}
  {{-- GRID DE COMPONENTES (layout fijo)                      --}}
  {{-- ═══════════════════════════════════════════════════════ --}}
  <div class="px-4 sm:px-5 lg:px-6 space-y-3 md:space-y-4">

    {{-- Fila 1: Gráfico de ingresos (2/3) + Stock (1/3) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 md:gap-4">
      <div class="lg:col-span-2 h-[21rem] sm:h-[23rem]">
        <div class="h-full">
          <livewire:dashboard.revenue-widget />
        </div>
      </div>
      <div class="h-[21rem] sm:h-[23rem]">
        <div class="h-full">
          <livewire:dashboard.stock-widget />
        </div>
      </div>
    </div>

    {{-- Fila 2: Últimas órdenes + Top productos + Calendario --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4">
      <div class="h-[17rem]">
        <div class="h-full">
          <livewire:dashboard.recent-orders />
        </div>
      </div>
      <div class="h-[17rem]">
        <div class="h-full">
          <livewire:dashboard.top-products />
        </div>
      </div>
      <div class="h-[17rem]">
        <div class="h-full">
          <livewire:dashboard.calendar-widget />
        </div>
      </div>
    </div>

    {{-- Fila 3: Desglose de gastos (full width) --}}
    <div class="h-[21rem] sm:h-[23rem]">
      <div class="h-full">
        <livewire:dashboard.expenses-widget />
      </div>
    </div>

  </div>

  {{-- Espacio inferior --}}
  <div class="h-6"></div>

</div>
