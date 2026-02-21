<div class="nexum-wrap">

  {{-- Flash messages --}}
  @if(session('nexum_success'))
    <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,5000)"
         class="nexum-flash nexum-flash-success">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>
      {{ session('nexum_success') }}
    </div>
  @endif
  @if(session('nexum_error'))
    <div class="nexum-flash nexum-flash-error">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      {{ session('nexum_error') }}
    </div>
  @endif

  {{-- ── TOP: Health Score + Insights ─────────────────────────────── --}}
  <div class="nexum-top">

    {{-- HEALTH SCORE ──────── --}}
    <div class="nexum-health-card">
      <div class="nexum-health-header">
        <span class="nexum-label-sm">Business Health</span>
        <div class="nexum-score-ring" style="--score-color: {{ $this->healthReport['status_color'] ?? '#6b7280' }}">
          <span class="nexum-score-num">{{ $this->healthReport['overall_score'] ?? 0 }}</span>
          <span class="nexum-score-den">/100</span>
        </div>
        <div class="nexum-status-pill" style="background: {{ $this->healthReport['status_color'] ?? '#6b7280' }}1a; color: {{ $this->healthReport['status_color'] ?? '#6b7280' }}; border-color: {{ $this->healthReport['status_color'] ?? '#6b7280' }}33;">
          {{ $this->healthReport['status'] ?? '—' }}
        </div>
        <p class="nexum-summary-text">{{ $this->healthReport['summary'] ?? '' }}</p>
      </div>

      {{-- Categorías --}}
      <div class="nexum-categories">
        @foreach($this->healthReport['categories'] ?? [] as $key => $cat)
          <div class="nexum-cat-row">
            <span class="nexum-cat-label">{{ $cat['label'] }}</span>
            <div class="nexum-bar-wrap">
              <div class="nexum-bar-fill" style="width:{{ $cat['score'] }}%; background:{{ $cat['color'] }};"></div>
            </div>
            <span class="nexum-cat-score" style="color:{{ $cat['color'] }};">{{ $cat['score'] }}</span>
          </div>
        @endforeach
      </div>

      {{-- Botón generar --}}
      <button wire:click="generate" wire:loading.attr="disabled" wire:loading.class="opacity-60"
              class="nexum-gen-btn">
        <span wire:loading.remove wire:target="generate">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Actualizar insights
        </span>
        <span wire:loading wire:target="generate" class="flex items-center gap-2">
          <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Analizando...
        </span>
      </button>
    </div>

    {{-- INSIGHTS ──────────── --}}
    <div class="nexum-insights-panel">
      {{-- Header --}}
      <div class="nexum-insights-header">
        <span class="nexum-label-sm">Insights activos</span>
        <span class="nexum-badge-count">{{ $this->stats['total'] ?? 0 }}</span>
      </div>

      {{-- Filter chips --}}
      <div class="nexum-filters">
        @foreach([
          'all'                => 'Todos',
          'critical'           => 'Urgentes',
          'stock_alert'        => '📦 Stock',
          'revenue_opportunity'=> '📈 Ingresos',
          'cost_warning'       => '⚠ Costos',
          'client_retention'   => '👥 Clientes',
        ] as $val => $label)
          <button wire:click="setFilter('{{ $val }}')"
                  class="nexum-chip {{ $filter === $val ? 'nexum-chip-active' : '' }}">
            {{ $label }}
          </button>
        @endforeach
      </div>

      {{-- Insights list --}}
      <div class="nexum-insights-list">
        @forelse($this->insights as $insight)
          <div class="nexum-insight-card" x-data="{expanded:false}">
            <div class="nexum-insight-top">
              <div class="nexum-insight-dot" style="background:{{ $insight->getPriorityColor() }};"></div>
              <div class="nexum-insight-content" @click="expanded=!expanded" style="cursor:pointer;">
                <div class="nexum-insight-title">{{ $insight->title }}</div>
                <div class="nexum-insight-desc" x-show="!expanded">{{ \Str::limit($insight->description, 80) }}</div>
                <div class="nexum-insight-desc" x-show="expanded" x-cloak>{{ $insight->description }}</div>
              </div>
              <div class="nexum-insight-meta">
                <span class="nexum-priority-badge" style="background:{{ $insight->getPriorityColor() }}1a; color:{{ $insight->getPriorityColor() }}; border-color:{{ $insight->getPriorityColor() }}33;">
                  {{ ucfirst($insight->priority) }}
                </span>
                <button wire:click="dismiss({{ $insight->id }})"
                        class="nexum-dismiss-btn" title="Descartar insight">×</button>
              </div>
            </div>
          </div>
        @empty
          <div class="nexum-empty">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>No hay insights en esta categoría.</p>
            <p class="text-xs opacity-60 mt-1">Actualizá el análisis para ver nuevos insights.</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- ── REPORTES ──────────────────────────────────────────────────── --}}
  <div class="nexum-reports-section">
    <div class="nexum-reports-header">
      <div>
        <h3 class="nexum-section-title">Reportes descargables</h3>
        <p class="nexum-section-sub">PDFs completos con ventas, gastos, margen y health score.</p>
      </div>
      <div class="flex items-center gap-2">
        <button wire:click="requestManualReport" wire:loading.attr="disabled" wire:loading.class="opacity-60"
                class="nexum-btn-secondary">
          <span wire:loading.remove wire:target="requestManualReport">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Generar reporte ahora
          </span>
          <span wire:loading wire:target="requestManualReport">Solicitando...</span>
        </button>
        <button wire:click="$set('showConfig', true)" class="nexum-btn-config">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Configurar frecuencia
        </button>
      </div>
    </div>

    {{-- Config modal --}}
    @if($showConfig)
    <div class="nexum-config-panel">
      <h4 class="nexum-config-title">Configuración de reportes automáticos</h4>
      <div class="nexum-config-grid">
        <div>
          <label class="nexum-label-sm block mb-1">Frecuencia</label>
          <select wire:model="frequency" class="nexum-select">
            <option value="weekly">Semanal (últimos 7 días)</option>
            <option value="monthly">Mensual (mes anterior)</option>
            <option value="quarterly">Trimestral (últimos 3 meses)</option>
            <option value="semiannual">Semestral (últimos 6 meses)</option>
            <option value="annual">Anual (año anterior)</option>
          </select>
        </div>
        <div class="flex items-center gap-6 pt-5">
          <label class="nexum-toggle-label">
            <input type="checkbox" wire:model="isActive" class="sr-only peer">
            <div class="nexum-toggle peer-checked:bg-violet-600"></div>
            <span class="nexum-label-sm">Activo</span>
          </label>
          <label class="nexum-toggle-label">
            <input type="checkbox" wire:model="emailDelivery" class="sr-only peer">
            <div class="nexum-toggle peer-checked:bg-violet-600"></div>
            <span class="nexum-label-sm">Enviar por email</span>
          </label>
        </div>
      </div>
      <div class="flex gap-2 mt-4">
        <button wire:click="saveConfig" class="nexum-btn-primary">Guardar configuración</button>
        <button wire:click="$set('showConfig', false)" class="nexum-btn-cancel">Cancelar</button>
      </div>
    </div>
    @endif

    {{-- Tabla de reportes --}}
    <div class="nexum-table-wrap">
      @if($this->reports->isEmpty())
        <div class="nexum-empty" style="padding: 2rem;">
          <svg class="w-8 h-8 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="text-sm">Todavía no hay reportes generados.</p>
          <p class="text-xs opacity-60 mt-1">Hacé click en "Generar reporte ahora" para crear el primero.</p>
        </div>
      @else
        <table class="nexum-table">
          <thead>
            <tr>
              <th>Período</th>
              <th>Frecuencia</th>
              <th>Generado</th>
              <th>Tamaño</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($this->reports as $report)
            <tr>
              <td>{{ $report->period_start->format('d/m/Y') }} → {{ $report->period_end->format('d/m/Y') }}</td>
              <td>{{ $report->periodLabel() }}</td>
              <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
              <td>{{ $report->fileSizeFormatted() }}</td>
              <td>
                @if($report->status === 'ready')
                  <span class="nexum-status-ok">Listo</span>
                @elseif($report->status === 'generating' || $report->status === 'pending')
                  <span class="nexum-status-pending">
                    <svg class="w-3 h-3 animate-spin inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Generando...
                  </span>
                @else
                  <span class="nexum-status-error">Error</span>
                @endif
              </td>
              <td>
                @if($report->isReady())
                  <a href="{{ route('nexum.reports.download', $report) }}"
                     class="nexum-download-btn">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    PDF
                  </a>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>

</div>
