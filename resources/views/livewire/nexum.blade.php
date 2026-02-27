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
          <div class="nexum-cat-row" title="{{ $cat['display_hint'] ?? '' }}">
            <div class="nexum-cat-label-wrap">
              <span class="nexum-cat-label">{{ $cat['label'] }}</span>
              @if(!empty($cat['weight']))
                <span class="nexum-cat-weight">{{ $cat['weight'] }}%</span>
              @endif
            </div>
            <div class="nexum-bar-wrap">
              <div class="nexum-bar-fill" style="width:{{ $cat['score'] }}%; background:{{ $cat['color'] }};"></div>
            </div>
            <span class="nexum-cat-value" style="color:{{ $cat['color'] }};">{{ $cat['display_value'] ?? $cat['score'] }}</span>
          </div>
        @endforeach
      </div>

      {{-- Botón generar --}}
      <button wire:click="generate" wire:loading.attr="disabled" wire:loading.class="opacity-60"
              class="nexum-gen-btn">
        <span wire:loading.remove wire:target="generate" class="flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Actualizar análisis
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
        @if($this->hasAiInsights)
          <div class="nexum-ai-icon" style="width:16px;height:16px;flex-shrink:0;">
            <span style="font-size:7px;">N</span>
          </div>
          <span class="nexum-label-sm">Diagnósticos · Nexum AI</span>
        @else
          <span class="nexum-label-sm">Diagnósticos activos</span>
        @endif
        <span class="nexum-badge-count">{{ $this->stats['total'] ?? 0 }}</span>
      </div>

      {{-- Filter chips con íconos --}}
      @php
        $chips = [
          'all' => [
            'label' => 'Todos',
            'path'  => 'M4 6h16M4 10h16M4 14h16M4 18h7',
          ],
          'critical' => [
            'label' => 'Urgentes',
            'path'  => 'M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
          ],
          'stock_alert' => [
            'label' => 'Stock',
            'path'  => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
          ],
          'revenue_opportunity' => [
            'label' => 'Ingresos',
            'path'  => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
          ],
          'cost_warning' => [
            'label' => 'Costos',
            'path'  => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
          ],
          'client_retention' => [
            'label' => 'Clientes',
            'path'  => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75',
          ],
        ];
      @endphp
      <div class="nexum-filters">
        @foreach($chips as $val => $chip)
          <button wire:click="setFilter('{{ $val }}')"
                  class="nexum-chip {{ $filter === $val ? 'nexum-chip-active' : '' }}">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $chip['path'] }}"/>
            </svg>
            {{ $chip['label'] }}
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
                        class="nexum-dismiss-btn" title="Descartar">
                  <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        @empty
          <div class="nexum-empty">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>No hay diagnósticos en esta categoría.</p>
            <p class="text-xs opacity-60 mt-1">Actualizá el análisis para ver nuevos diagnósticos.</p>
          </div>
        @endforelse
      </div>

      {{-- Banner upgrade para usuarios Basic --}}
      @if(!$this->hasAiInsights)
        <div style="margin-top:.75rem; padding:.6rem .85rem; border-radius:.65rem; background:rgba(109,40,217,.06); border:1px solid rgba(139,92,246,.18); display:flex; align-items:center; justify-content:space-between; gap:.5rem;">
          <div style="display:flex; align-items:center; gap:.45rem;">
            <div class="nexum-ai-icon" style="width:16px;height:16px;flex-shrink:0;"><span style="font-size:7px;">N</span></div>
            <span style="font-size:.72rem; color:var(--nx-t3);">Diagnósticos con IA disponibles en Premium</span>
          </div>
          <a href="{{ route('plans') }}" style="font-size:.7rem; font-weight:600; color:var(--nx-t2); text-decoration:none; white-space:nowrap;">Mejorar plan →</a>
        </div>
      @endif
    </div>
  </div>

  {{-- ── REPORTES ──────────────────────────────────────────────────── --}}
  <div class="nexum-reports-section">
    <div class="nexum-reports-header">
      <div>
        <h3 class="nexum-section-title">Reportes descargables</h3>
        <p class="nexum-section-sub">PDFs con ventas, gastos, margen y health score del período.</p>
      </div>
      <div class="flex items-center gap-2">

        {{-- Generar reporte con dropdown de período --}}
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open" :disabled="$wire.requestingManual"
                  wire:loading.attr="disabled" wire:loading.class="opacity-60"
                  class="nexum-btn-secondary">
            <span wire:loading.remove wire:target="requestManualReport" class="flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Generar reporte
              <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                   fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
              </svg>
            </span>
            <span wire:loading wire:target="requestManualReport" class="flex items-center gap-1.5">
              <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Generando...
            </span>
          </button>

          <div x-show="open" @click.away="open = false"
               x-transition:enter="transition ease-out duration-150"
               x-transition:enter-start="opacity-0 scale-95"
               x-transition:enter-end="opacity-100 scale-100"
               x-transition:leave="transition ease-in duration-100"
               x-transition:leave-start="opacity-100 scale-100"
               x-transition:leave-end="opacity-0 scale-95"
               class="nexum-period-dropdown">
            @foreach([
              'weekly'     => ['Semanal',     'Últimos 7 días'],
              'monthly'    => ['Mensual',      'Últimos 30 días'],
              'quarterly'  => ['Trimestral',   'Últimos 3 meses'],
              'semiannual' => ['Semestral',    'Últimos 6 meses'],
              'annual'     => ['Anual',        'Último año'],
            ] as $value => [$label, $hint])
              <button @click="$dispatch('open-report-modal'); $wire.requestManualReport('{{ $value }}'); open = false"
                      class="nexum-period-item">
                <span>{{ $label }}</span>
                <span class="nexum-period-hint">{{ $hint }}</span>
              </button>
            @endforeach
          </div>
        </div>

        {{-- Programar reportes automáticos --}}
        <button wire:click="$set('showConfig', true)" class="nexum-btn-config">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          Programar
        </button>
      </div>
    </div>

    {{-- Config panel --}}
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
            <span class="nexum-label-sm">Email</span>
          </label>
        </div>
      </div>
      <div class="flex gap-2 mt-4">
        <button wire:click="saveConfig" class="nexum-btn-primary">Guardar</button>
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
          <p class="text-xs opacity-60 mt-1">Hacé clic en "Generar reporte" para crear el primero.</p>
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
              <td>{{ $report->period_start->format('d/m/Y') }} &rarr; {{ $report->period_end->format('d/m/Y') }}</td>
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
                <div class="nexum-action-btns">
                  @if($report->isReady())
                    <a href="{{ route('nexum.reports.view', $report) }}"
                       target="_blank"
                       class="nexum-view-btn"
                       title="Ver online">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </a>
                    <a href="{{ route('nexum.reports.download', $report) }}"
                       class="nexum-download-btn"
                       title="Descargar PDF">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                      </svg>
                      PDF
                    </a>
                  @endif
                  <button wire:click="deleteReport({{ $report->id }})"
                          wire:confirm="¿Eliminar este reporte? Esta acción no se puede deshacer."
                          wire:loading.attr="disabled"
                          class="nexum-delete-btn"
                          title="Eliminar reporte">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>

  {{-- ── Glass modal: generando / listo ──────────────────────────────── --}}
  <div
    x-data="{
      show: false,
      done: false,
      viewUrl: null,
      downloadUrl: null,
    }"
    @open-report-modal.window="show = true; done = false; viewUrl = null; downloadUrl = null;"
    @report-ready.window="done = true; viewUrl = $event.detail.viewUrl; downloadUrl = $event.detail.downloadUrl;"
    @report-failed.window="show = false;"
    x-show="show"
    x-transition:enter="transition ease-out duration-250"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-180"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="nexum-modal-overlay"
    style="display:none;"
    @click.self="if(done) { show = false; }"
  >
    <div
      class="nexum-modal-glass"
      x-transition:enter="transition ease-out duration-280"
      x-transition:enter-start="opacity-0 scale-95 -translate-y-3"
      x-transition:enter-end="opacity-100 scale-100 translate-y-0"
      x-transition:leave="transition ease-in duration-180"
      x-transition:leave-start="opacity-100 scale-100 translate-y-0"
      x-transition:leave-end="opacity-0 scale-95 translate-y-2"
    >

      {{-- X cerrar (solo cuando listo) --}}
      <button x-show="done" @click="show = false" class="nexum-modal-x" title="Cerrar" x-cloak>
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>

      {{-- Estado: Generando ──────────────────────────── --}}
      <div x-show="!done">
        <div class="nexum-modal-spinner"></div>
        <p class="nexum-modal-title">Generando reporte</p>
        <p class="nexum-modal-sub">Analizando ventas, márgenes e inventario&hellip;</p>
      </div>

      {{-- Estado: Listo ──────────────────────────────── --}}
      <div x-show="done" x-cloak>
        <div class="nexum-modal-check">
          <svg width="24" height="24" fill="none" stroke="#34d399" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <p class="nexum-modal-title">Reporte listo</p>
        <p class="nexum-modal-sub">Tu PDF ya está disponible.</p>
        <div class="nexum-modal-actions">
          <a :href="viewUrl" target="_blank" class="nexum-modal-btn-view">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Ver
          </a>
          <a :href="downloadUrl" class="nexum-modal-btn-download">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Descargar PDF
          </a>
        </div>
      </div>

    </div>
  </div>

</div>
