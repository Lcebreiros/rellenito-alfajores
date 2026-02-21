<x-app-layout>

  {{-- Fondo oscuro violeta fijo, independiente del tema del usuario --}}
  @push('styles')
  <style>
    /* Fondo negro violeta en toda el área de contenido de Nexum */
    .app-main:has(.nexum-wrap),
    .app-main:has(.nexum-wrap) main {
      background:
        radial-gradient(ellipse 120% 60% at 80% -10%, rgba(109,40,217,.28) 0%, transparent 55%),
        radial-gradient(ellipse 80% 50% at -5% 110%, rgba(76,29,149,.22) 0%, transparent 50%),
        #000 !important;
    }
    /* Header: oscuro translúcido con borde violeta */
    .app-main:has(.nexum-wrap) .header-glass {
      background: rgba(8,3,20,.85) !important;
      backdrop-filter: blur(12px) !important;
      border-bottom: 1px solid rgba(109,40,217,.25) !important;
      box-shadow: none !important;
    }
  </style>
  @endpush

  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
             style="background: linear-gradient(135deg,#7c3aed,#4c1d95);">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-lg font-semibold text-white">Nexum</h1>
          <p class="text-xs text-purple-300/70">Inteligencia de negocio</p>
        </div>
      </div>
    </div>
  </x-slot>

  {{-- Estilos Nexum --}}
  <style>
    /* ── Layout ── */
    .nexum-wrap { padding: 1.25rem; max-width: 1400px; margin: 0 auto; }
    .nexum-top  { display: grid; grid-template-columns: 320px 1fr; gap: 1rem; margin-bottom: 1rem; }
    @media(max-width:900px){ .nexum-top { grid-template-columns: 1fr; } }

    /* ── Flash ── */
    .nexum-flash {
      display: flex; align-items: center; gap: 8px;
      padding: .65rem 1rem; border-radius: .75rem; margin-bottom: 1rem;
      font-size: .85rem; font-weight: 500;
    }
    .nexum-flash-success { background: rgba(16,185,129,.1); color: #10b981; border: 1px solid rgba(16,185,129,.2); }
    .nexum-flash-error   { background: rgba(239,68,68,.1);  color: #ef4444; border: 1px solid rgba(239,68,68,.2); }

    /* ── Health Card ── */
    .nexum-health-card {
      background: linear-gradient(180deg, rgba(109,40,217,.18) 0%, rgba(76,29,149,.08) 100%);
      border: 1px solid rgba(139,92,246,.25);
      border-radius: 1.25rem; padding: 1.25rem;
    }
    .nexum-health-header { text-align: center; margin-bottom: 1.25rem; }
    .nexum-label-sm { font-size: .7rem; font-weight: 600; letter-spacing: .08em;
                      text-transform: uppercase; color: rgba(196,181,253,.7); }
    .nexum-score-ring { margin: .5rem auto; }
    .nexum-score-num  { font-size: 3rem; font-weight: 800; color: var(--score-color, #8b5cf6); line-height: 1; }
    .nexum-score-den  { font-size: .9rem; color: rgba(196,181,253,.5); }
    .nexum-status-pill {
      display: inline-block; padding: .2rem .75rem; border-radius: 20px;
      font-size: .75rem; font-weight: 600; border: 1px solid; margin-top: .4rem;
    }
    .nexum-summary-text { font-size: .78rem; color: rgba(196,181,253,.7); margin-top: .5rem; line-height: 1.5; }

    /* ── Category bars ── */
    .nexum-categories { display: flex; flex-direction: column; gap: .55rem; margin-bottom: 1rem; }
    .nexum-cat-row    { display: flex; align-items: center; gap: .5rem; }
    .nexum-cat-label  { font-size: .75rem; color: rgba(196,181,253,.8); width: 72px; flex-shrink: 0; }
    .nexum-bar-wrap   { flex: 1; height: 6px; background: rgba(139,92,246,.15); border-radius: 4px; overflow: hidden; }
    .nexum-bar-fill   { height: 100%; border-radius: 4px; transition: width .6s ease; }
    .nexum-cat-score  { font-size: .75rem; font-weight: 700; width: 28px; text-align: right; flex-shrink: 0; }

    /* ── Gen button ── */
    .nexum-gen-btn {
      width: 100%; display: flex; align-items: center; justify-content: center; gap: .5rem;
      padding: .55rem; border-radius: .75rem; font-size: .8rem; font-weight: 600; cursor: pointer;
      background: rgba(109,40,217,.2); border: 1px solid rgba(139,92,246,.3);
      color: rgba(196,181,253,.9); transition: all .2s;
    }
    .nexum-gen-btn:hover { background: rgba(109,40,217,.35); border-color: rgba(139,92,246,.5); }

    /* ── Insights panel ── */
    .nexum-insights-panel {
      background: rgba(15,8,40,.6); border: 1px solid rgba(139,92,246,.15);
      border-radius: 1.25rem; padding: 1.25rem; display: flex; flex-direction: column;
    }
    .nexum-insights-header { display: flex; align-items: center; gap: .5rem; margin-bottom: .75rem; }
    .nexum-badge-count {
      background: rgba(139,92,246,.2); color: #c4b5fd; border: 1px solid rgba(139,92,246,.3);
      border-radius: 20px; padding: .1rem .5rem; font-size: .7rem; font-weight: 700;
    }
    .nexum-filters { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .75rem; }
    .nexum-chip {
      padding: .2rem .65rem; border-radius: 20px; font-size: .7rem; font-weight: 500; cursor: pointer;
      background: rgba(109,40,217,.1); border: 1px solid rgba(139,92,246,.2);
      color: rgba(196,181,253,.7); transition: all .15s;
    }
    .nexum-chip:hover, .nexum-chip-active {
      background: rgba(109,40,217,.3) !important; border-color: rgba(139,92,246,.5) !important;
      color: #e9d5ff !important;
    }
    .nexum-insights-list { flex: 1; overflow-y: auto; max-height: 400px; display: flex; flex-direction: column; gap: .5rem; }

    /* ── Insight card ── */
    .nexum-insight-card {
      background: rgba(109,40,217,.07); border: 1px solid rgba(139,92,246,.15);
      border-radius: .75rem; padding: .65rem .8rem; transition: border-color .15s;
    }
    .nexum-insight-card:hover { border-color: rgba(139,92,246,.35); }
    .nexum-insight-top    { display: flex; align-items: flex-start; gap: .6rem; }
    .nexum-insight-dot    { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
    .nexum-insight-content { flex: 1; min-width: 0; }
    .nexum-insight-title  { font-size: .8rem; font-weight: 600; color: rgba(233,213,255,.9); }
    .nexum-insight-desc   { font-size: .74rem; color: rgba(196,181,253,.65); margin-top: 2px; line-height: 1.4; }
    .nexum-insight-meta   { display: flex; align-items: center; gap: .4rem; flex-shrink: 0; }
    .nexum-priority-badge { padding: .1rem .45rem; border-radius: 20px; font-size: .65rem; font-weight: 600; border: 1px solid; }
    .nexum-dismiss-btn    { width: 18px; height: 18px; border-radius: 50%; border: none; cursor: pointer;
                            background: rgba(239,68,68,.15); color: #f87171; font-size: .85rem; line-height: 1;
                            display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nexum-dismiss-btn:hover { background: rgba(239,68,68,.3); }

    /* ── Empty state ── */
    .nexum-empty { text-align: center; padding: 2rem 1rem; color: rgba(196,181,253,.5); font-size: .85rem; }

    /* ── Reports section ── */
    .nexum-reports-section {
      background: rgba(15,8,40,.6); border: 1px solid rgba(139,92,246,.15);
      border-radius: 1.25rem; padding: 1.25rem;
    }
    .nexum-reports-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: .5rem; }
    .nexum-section-title  { font-size: .95rem; font-weight: 700; color: #e9d5ff; }
    .nexum-section-sub    { font-size: .75rem; color: rgba(196,181,253,.6); margin-top: 2px; }

    /* ── Buttons ── */
    .nexum-btn-secondary {
      display: flex; align-items: center; gap: .4rem; padding: .45rem .9rem;
      border-radius: .6rem; font-size: .8rem; font-weight: 600; cursor: pointer;
      background: rgba(109,40,217,.2); border: 1px solid rgba(139,92,246,.35);
      color: #c4b5fd; transition: all .2s;
    }
    .nexum-btn-secondary:hover { background: rgba(109,40,217,.35); }
    .nexum-btn-config {
      display: flex; align-items: center; gap: .4rem; padding: .45rem .9rem;
      border-radius: .6rem; font-size: .8rem; font-weight: 600; cursor: pointer;
      background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
      color: rgba(196,181,253,.8); transition: all .2s;
    }
    .nexum-btn-config:hover { background: rgba(255,255,255,.1); }
    .nexum-btn-primary {
      padding: .45rem 1rem; border-radius: .6rem; font-size: .8rem; font-weight: 600; cursor: pointer;
      background: linear-gradient(135deg,#7c3aed,#6d28d9);
      border: none; color: #fff;
      box-shadow: 0 4px 16px rgba(109,40,217,.3); transition: box-shadow .2s;
    }
    .nexum-btn-primary:hover { box-shadow: 0 4px 24px rgba(109,40,217,.5); }
    .nexum-btn-cancel {
      padding: .45rem 1rem; border-radius: .6rem; font-size: .8rem; font-weight: 600; cursor: pointer;
      background: transparent; border: 1px solid rgba(255,255,255,.1); color: rgba(196,181,253,.7);
    }

    /* ── Config panel ── */
    .nexum-config-panel {
      background: rgba(109,40,217,.08); border: 1px solid rgba(139,92,246,.2);
      border-radius: .75rem; padding: 1rem; margin-bottom: 1rem;
    }
    .nexum-config-title { font-size: .85rem; font-weight: 600; color: #e9d5ff; margin-bottom: .75rem; }
    .nexum-config-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media(max-width:600px){ .nexum-config-grid { grid-template-columns: 1fr; } }
    .nexum-select {
      width: 100%; padding: .5rem .75rem; border-radius: .6rem; font-size: .8rem;
      background: rgba(15,8,40,.8); border: 1px solid rgba(139,92,246,.3);
      color: #e9d5ff; outline: none;
    }
    .nexum-select:focus { border-color: rgba(139,92,246,.6); }
    .nexum-toggle-label { display: flex; align-items: center; gap: .5rem; cursor: pointer; }
    .nexum-toggle {
      width: 36px; height: 20px; border-radius: 10px; background: rgba(255,255,255,.1);
      border: 1px solid rgba(255,255,255,.15); position: relative; transition: background .2s;
    }
    .nexum-toggle::after {
      content: ''; position: absolute; top: 2px; left: 2px;
      width: 14px; height: 14px; border-radius: 50%;
      background: #fff; transition: transform .2s;
    }
    .peer:checked ~ .nexum-toggle::after { transform: translateX(16px); }

    /* ── Table ── */
    .nexum-table-wrap { overflow-x: auto; border-radius: .75rem; border: 1px solid rgba(139,92,246,.15); }
    .nexum-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
    .nexum-table thead th {
      padding: .65rem .9rem; text-align: left; font-size: .7rem; font-weight: 600;
      text-transform: uppercase; letter-spacing: .05em;
      background: rgba(109,40,217,.15); color: rgba(196,181,253,.8);
      border-bottom: 1px solid rgba(139,92,246,.2);
    }
    .nexum-table tbody tr { border-bottom: 1px solid rgba(139,92,246,.08); }
    .nexum-table tbody tr:last-child { border-bottom: none; }
    .nexum-table tbody td { padding: .6rem .9rem; color: rgba(233,213,255,.8); }

    /* ── Status badges ── */
    .nexum-status-ok      { padding: .15rem .55rem; border-radius: 20px; font-size: .7rem; font-weight: 600; background: rgba(16,185,129,.1); color: #10b981; border: 1px solid rgba(16,185,129,.2); }
    .nexum-status-pending { padding: .15rem .55rem; border-radius: 20px; font-size: .7rem; font-weight: 600; background: rgba(245,158,11,.1); color: #f59e0b; border: 1px solid rgba(245,158,11,.2); }
    .nexum-status-error   { padding: .15rem .55rem; border-radius: 20px; font-size: .7rem; font-weight: 600; background: rgba(239,68,68,.1); color: #ef4444; border: 1px solid rgba(239,68,68,.2); }
    .nexum-download-btn {
      display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .65rem;
      border-radius: .5rem; font-size: .75rem; font-weight: 600; text-decoration: none;
      background: rgba(109,40,217,.2); border: 1px solid rgba(139,92,246,.3); color: #c4b5fd;
      transition: all .15s;
    }
    .nexum-download-btn:hover { background: rgba(109,40,217,.4); color: #e9d5ff; }
  </style>

  @livewire('nexum')

</x-app-layout>
