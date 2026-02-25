<x-app-layout>

  @push('styles')
  <style>
    /* ── Page background: light mode ── */
    .app-main:has(.nexum-wrap),
    .app-main:has(.nexum-wrap) main {
      background:
        radial-gradient(ellipse 120% 55% at 75% -5%,  rgba(139,92,246,.14) 0%, transparent 55%),
        radial-gradient(ellipse 70%  45% at -5% 105%, rgba(109,40,217,.09) 0%, transparent 50%),
        #f7f5ff !important;
    }
    .app-main:has(.nexum-wrap) .header-glass {
      background: rgba(255,255,255,.90) !important;
      backdrop-filter: blur(14px) !important;
      -webkit-backdrop-filter: blur(14px) !important;
      border-bottom: 1px solid rgba(139,92,246,.18) !important;
      box-shadow: 0 1px 16px rgba(109,40,217,.07) !important;
    }
    /* ── Page background: dark mode ── */
    .dark .app-main:has(.nexum-wrap),
    .dark .app-main:has(.nexum-wrap) main {
      background:
        radial-gradient(ellipse 120% 60% at 80% -10%, rgba(109,40,217,.28) 0%, transparent 55%),
        radial-gradient(ellipse 80%  50% at -5% 110%, rgba(76,29,149,.22)  0%, transparent 50%),
        #000 !important;
    }
    .dark .app-main:has(.nexum-wrap) .header-glass {
      background: rgba(8,3,20,.85) !important;
      backdrop-filter: blur(12px) !important;
      -webkit-backdrop-filter: blur(12px) !important;
      border-bottom: 1px solid rgba(109,40,217,.25) !important;
      box-shadow: none !important;
    }
  </style>
  @endpush

  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div>
          <span style="font-size:1.45rem; font-weight:800; letter-spacing:.22em; background:linear-gradient(135deg,#a78bfa 0%,#7c3aed 55%,#4c1d95 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; line-height:1.2; display:inline-block;">NEXUM</span>
          <p class="text-xs text-violet-500 dark:text-purple-300/70" style="letter-spacing:.04em;">Inteligencia de negocio</p>
        </div>
      </div>
    </div>
  </x-slot>

  {{-- ── Nexum CSS ─────────────────────────────────────────────────────── --}}
  <style>
    /* ══════════════════════════════════════════════════════════
       TOKEN SYSTEM — light mode defaults / dark mode overrides
       ══════════════════════════════════════════════════════════ */
    .nexum-wrap {
      /* Backgrounds */
      --nx-bg-card:        rgba(255,255,255,0.84);
      --nx-bg-panel:       rgba(255,255,255,0.76);
      --nx-bg-insight:     rgba(109,40,217,0.04);
      --nx-bg-config:      rgba(109,40,217,0.05);
      --nx-bg-chip:        rgba(109,40,217,0.07);
      --nx-bg-chip-active: rgba(109,40,217,0.15);
      --nx-bg-thead:       rgba(109,40,217,0.07);
      --nx-bg-genbtn:      rgba(109,40,217,0.10);
      --nx-bg-select:      rgba(255,255,255,0.95);
      --nx-bg-toggle:      rgba(139,92,246,0.14);
      --nx-bg-badge:       rgba(109,40,217,0.10);
      --nx-bg-sec-btn:     rgba(109,40,217,0.10);
      --nx-bg-cfg-btn:     rgba(255,255,255,0.80);
      --nx-bg-dismiss:     rgba(239,68,68,0.10);
      /* Borders */
      --nx-border:         rgba(139,92,246,0.20);
      --nx-border-card:    rgba(139,92,246,0.22);
      --nx-border-chip:    rgba(139,92,246,0.20);
      --nx-border-chip-a:  rgba(139,92,246,0.50);
      --nx-border-tbl:     rgba(139,92,246,0.10);
      --nx-border-row:     rgba(139,92,246,0.07);
      --nx-border-select:  rgba(139,92,246,0.26);
      --nx-border-toggle:  rgba(139,92,246,0.28);
      --nx-border-badge:   rgba(139,92,246,0.22);
      --nx-border-sec-btn: rgba(139,92,246,0.30);
      --nx-border-cfg-btn: rgba(139,92,246,0.20);
      /* Text */
      --nx-t1: #1e1b4b;
      --nx-t2: #5b21b6;
      --nx-t3: rgba(91,33,182,0.80);
      --nx-t4: rgba(91,33,182,0.68);
      --nx-t5: rgba(91,33,182,0.52);
      --nx-t-chip:   rgba(91,33,182,0.82);
      --nx-t-chip-a: #3b0764;
      /* Misc */
      --nx-bar-bg:       rgba(139,92,246,0.12);
      --nx-shadow-card:  0 6px 28px rgba(109,40,217,0.10), 0 1px 4px rgba(109,40,217,0.06);
      --nx-shadow-panel: 0 4px 20px rgba(109,40,217,0.07);
    }
    .dark .nexum-wrap {
      /* Backgrounds */
      --nx-bg-card:        linear-gradient(180deg,rgba(109,40,217,.18) 0%,rgba(76,29,149,.08) 100%);
      --nx-bg-panel:       rgba(15,8,40,.60);
      --nx-bg-insight:     rgba(109,40,217,.07);
      --nx-bg-config:      rgba(109,40,217,.08);
      --nx-bg-chip:        rgba(109,40,217,.10);
      --nx-bg-chip-active: rgba(109,40,217,.30);
      --nx-bg-thead:       rgba(109,40,217,.15);
      --nx-bg-genbtn:      rgba(109,40,217,.20);
      --nx-bg-select:      rgba(15,8,40,.80);
      --nx-bg-toggle:      rgba(255,255,255,.10);
      --nx-bg-badge:       rgba(139,92,246,.20);
      --nx-bg-sec-btn:     rgba(109,40,217,.20);
      --nx-bg-cfg-btn:     rgba(255,255,255,.05);
      --nx-bg-dismiss:     rgba(239,68,68,.15);
      /* Borders */
      --nx-border:         rgba(139,92,246,.15);
      --nx-border-card:    rgba(139,92,246,.25);
      --nx-border-chip:    rgba(139,92,246,.20);
      --nx-border-chip-a:  rgba(139,92,246,.50);
      --nx-border-tbl:     rgba(139,92,246,.15);
      --nx-border-row:     rgba(139,92,246,.08);
      --nx-border-select:  rgba(139,92,246,.30);
      --nx-border-toggle:  rgba(255,255,255,.15);
      --nx-border-badge:   rgba(139,92,246,.30);
      --nx-border-sec-btn: rgba(139,92,246,.35);
      --nx-border-cfg-btn: rgba(255,255,255,.10);
      /* Text */
      --nx-t1: rgba(233,213,255,.90);
      --nx-t2: rgba(196,181,253,.80);
      --nx-t3: rgba(196,181,253,.72);
      --nx-t4: rgba(196,181,253,.65);
      --nx-t5: rgba(196,181,253,.48);
      --nx-t-chip:   rgba(196,181,253,.72);
      --nx-t-chip-a: #e9d5ff;
      /* Misc */
      --nx-bar-bg:       rgba(139,92,246,.15);
      --nx-shadow-card:  none;
      --nx-shadow-panel: none;
    }

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
    .nexum-flash-success { background: rgba(16,185,129,.1); color: #059669; border: 1px solid rgba(16,185,129,.25); }
    .dark .nexum-flash-success { color: #10b981; }
    .nexum-flash-error   { background: rgba(239,68,68,.1); color: #dc2626; border: 1px solid rgba(239,68,68,.2); }
    .dark .nexum-flash-error   { color: #ef4444; }

    /* ── Labels ── */
    .nexum-label-sm {
      font-size: .7rem; font-weight: 600; letter-spacing: .08em;
      text-transform: uppercase; color: var(--nx-t3);
    }

    /* ── Health Card ── */
    .nexum-health-card {
      background: var(--nx-bg-card);
      border: 1px solid var(--nx-border-card);
      border-radius: 1.25rem; padding: 1.25rem;
      box-shadow: var(--nx-shadow-card);
      -webkit-backdrop-filter: blur(18px);
      backdrop-filter: blur(18px);
    }
    .dark .nexum-health-card { -webkit-backdrop-filter: none; backdrop-filter: none; }
    .nexum-health-header { text-align: center; margin-bottom: 1.25rem; }
    .nexum-score-ring    { margin: .5rem auto; }
    .nexum-score-num     { font-size: 3rem; font-weight: 800; color: var(--score-color, #7c3aed); line-height: 1; }
    .nexum-score-den     { font-size: .9rem; color: var(--nx-t4); }
    .nexum-status-pill   {
      display: inline-block; padding: .2rem .75rem; border-radius: 20px;
      font-size: .75rem; font-weight: 600; border: 1px solid; margin-top: .4rem;
    }
    .nexum-summary-text  { font-size: .78rem; color: var(--nx-t4); margin-top: .5rem; line-height: 1.5; }

    /* ── Category bars ── */
    .nexum-categories     { display: flex; flex-direction: column; gap: .55rem; margin-bottom: 1rem; }
    .nexum-cat-row        { display: flex; align-items: center; gap: .5rem; }
    .nexum-cat-label-wrap { display: flex; align-items: baseline; gap: .3rem; width: 90px; flex-shrink: 0; }
    .nexum-cat-label      { font-size: .75rem; color: var(--nx-t3); }
    .nexum-cat-weight     { font-size: .62rem; color: var(--nx-t4); opacity: .7; }
    .nexum-bar-wrap   { flex: 1; height: 6px; background: var(--nx-bar-bg); border-radius: 4px; overflow: hidden; }
    .nexum-bar-fill   { height: 100%; border-radius: 4px; transition: width .6s ease; }
    .nexum-cat-score  { font-size: .75rem; font-weight: 700; width: 28px; text-align: right; flex-shrink: 0; }
    .nexum-cat-value  { font-size: .72rem; font-weight: 700; min-width: 46px; max-width: 64px; text-align: right; flex-shrink: 0; white-space: nowrap; }

    /* ── Gen button ── */
    .nexum-gen-btn {
      width: 100%; display: flex; align-items: center; justify-content: center; gap: .5rem;
      padding: .55rem; border-radius: .75rem; font-size: .8rem; font-weight: 600; cursor: pointer;
      background: var(--nx-bg-genbtn); border: 1px solid var(--nx-border);
      color: var(--nx-t2); transition: all .2s;
    }
    .nexum-gen-btn:hover { background: var(--nx-bg-chip-active); border-color: var(--nx-border-chip-a); }

    /* ── Insights panel ── */
    .nexum-insights-panel {
      background: var(--nx-bg-panel); border: 1px solid var(--nx-border);
      border-radius: 1.25rem; padding: 1.25rem;
      display: flex; flex-direction: column;
      box-shadow: var(--nx-shadow-panel);
      -webkit-backdrop-filter: blur(18px);
      backdrop-filter: blur(18px);
    }
    .dark .nexum-insights-panel { -webkit-backdrop-filter: none; backdrop-filter: none; }
    .nexum-insights-header { display: flex; align-items: center; gap: .5rem; margin-bottom: .75rem; }
    .nexum-badge-count {
      background: var(--nx-bg-badge); color: var(--nx-t2);
      border: 1px solid var(--nx-border-badge);
      border-radius: 20px; padding: .1rem .5rem; font-size: .7rem; font-weight: 700;
    }

    /* ── Filter chips ── */
    .nexum-filters { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .75rem; }
    .nexum-chip {
      display: inline-flex; align-items: center; gap: .3rem;
      padding: .22rem .65rem; border-radius: 20px; font-size: .7rem; font-weight: 500; cursor: pointer;
      background: var(--nx-bg-chip); border: 1px solid var(--nx-border-chip);
      color: var(--nx-t-chip); transition: all .15s;
    }
    .nexum-chip:hover, .nexum-chip-active {
      background: var(--nx-bg-chip-active) !important;
      border-color: var(--nx-border-chip-a) !important;
      color: var(--nx-t-chip-a) !important;
    }
    .nexum-chip svg { flex-shrink: 0; }

    /* ── Insights list ── */
    .nexum-insights-list { flex: 1; overflow-y: auto; max-height: 400px; display: flex; flex-direction: column; gap: .5rem; }

    /* ── Insight card ── */
    .nexum-insight-card {
      background: var(--nx-bg-insight); border: 1px solid var(--nx-border);
      border-radius: .75rem; padding: .65rem .8rem; transition: border-color .15s;
    }
    .nexum-insight-card:hover { border-color: var(--nx-border-chip-a); }
    .nexum-insight-top     { display: flex; align-items: flex-start; gap: .6rem; }
    .nexum-insight-dot     { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
    .nexum-insight-content { flex: 1; min-width: 0; }
    .nexum-insight-title   { font-size: .8rem; font-weight: 600; color: var(--nx-t1); }
    .nexum-insight-desc    { font-size: .74rem; color: var(--nx-t4); margin-top: 2px; line-height: 1.4; }
    .nexum-insight-meta    { display: flex; align-items: center; gap: .4rem; flex-shrink: 0; }
    .nexum-priority-badge  { padding: .1rem .45rem; border-radius: 20px; font-size: .65rem; font-weight: 600; border: 1px solid; }
    .nexum-dismiss-btn     {
      width: 18px; height: 18px; border-radius: 50%; border: none; cursor: pointer;
      background: var(--nx-bg-dismiss); color: #f87171; font-size: .85rem; line-height: 1;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .nexum-dismiss-btn:hover { background: rgba(239,68,68,.28); }

    /* ── Empty state ── */
    .nexum-empty { text-align: center; padding: 2rem 1rem; color: var(--nx-t5); font-size: .85rem; }

    /* ── Reports section ── */
    .nexum-reports-section {
      background: var(--nx-bg-panel); border: 1px solid var(--nx-border);
      border-radius: 1.25rem; padding: 1.25rem;
      box-shadow: var(--nx-shadow-panel);
      -webkit-backdrop-filter: blur(18px);
      backdrop-filter: blur(18px);
    }
    .dark .nexum-reports-section { -webkit-backdrop-filter: none; backdrop-filter: none; }
    .nexum-reports-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: .5rem; }
    .nexum-section-title  { font-size: .95rem; font-weight: 700; color: var(--nx-t1); }
    .nexum-section-sub    { font-size: .75rem; color: var(--nx-t4); margin-top: 2px; }

    /* ── Buttons ── */
    .nexum-btn-secondary {
      display: flex; align-items: center; gap: .4rem; padding: .45rem .9rem;
      border-radius: .6rem; font-size: .8rem; font-weight: 600; cursor: pointer;
      background: var(--nx-bg-sec-btn); border: 1px solid var(--nx-border-sec-btn);
      color: var(--nx-t2); transition: all .2s;
    }
    .nexum-btn-secondary:hover { background: var(--nx-bg-chip-active); }
    .nexum-btn-config {
      display: flex; align-items: center; gap: .4rem; padding: .45rem .9rem;
      border-radius: .6rem; font-size: .8rem; font-weight: 600; cursor: pointer;
      background: var(--nx-bg-cfg-btn); border: 1px solid var(--nx-border-cfg-btn);
      color: var(--nx-t3); transition: all .2s;
    }
    .nexum-btn-config:hover { background: var(--nx-bg-chip); }
    .nexum-btn-primary {
      padding: .45rem 1rem; border-radius: .6rem; font-size: .8rem; font-weight: 600; cursor: pointer;
      background: linear-gradient(135deg,#7c3aed,#6d28d9); border: none; color: #fff;
      box-shadow: 0 4px 16px rgba(109,40,217,.30); transition: box-shadow .2s;
    }
    .nexum-btn-primary:hover { box-shadow: 0 4px 24px rgba(109,40,217,.50); }
    .nexum-btn-cancel {
      padding: .45rem 1rem; border-radius: .6rem; font-size: .8rem; font-weight: 600; cursor: pointer;
      background: transparent; border: 1px solid var(--nx-border); color: var(--nx-t4);
    }

    /* ── Period dropdown ── */
    .nexum-period-dropdown {
      position: absolute; right: 0; top: calc(100% + 6px); z-index: 50;
      min-width: 190px;
      background: var(--nx-bg-card);
      border: 1px solid var(--nx-border);
      border-radius: .75rem;
      padding: .3rem;
      box-shadow: 0 8px 32px rgba(0,0,0,.18), 0 2px 8px rgba(109,40,217,.10);
    }
    .nexum-period-item {
      display: flex; align-items: center; justify-content: space-between; width: 100%;
      padding: .5rem .75rem; border-radius: .5rem; cursor: pointer;
      font-size: .8rem; font-weight: 600; color: var(--nx-t2);
      background: transparent; border: none; text-align: left;
      transition: background .15s;
      gap: .75rem;
    }
    .nexum-period-item:hover { background: var(--nx-bg-chip); color: var(--nx-t1); }
    .nexum-period-hint { font-size: .7rem; color: var(--nx-t4); font-weight: 400; white-space: nowrap; }

    /* ── Config panel ── */
    .nexum-config-panel {
      background: var(--nx-bg-config); border: 1px solid var(--nx-border);
      border-radius: .75rem; padding: 1rem; margin-bottom: 1rem;
    }
    .nexum-config-title { font-size: .85rem; font-weight: 600; color: var(--nx-t1); margin-bottom: .75rem; }
    .nexum-config-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media(max-width:600px){ .nexum-config-grid { grid-template-columns: 1fr; } }
    .nexum-select {
      width: 100%; padding: .5rem .75rem; border-radius: .6rem; font-size: .8rem;
      background: var(--nx-bg-select); border: 1px solid var(--nx-border-select);
      color: var(--nx-t1); outline: none;
    }
    .nexum-select:focus { border-color: rgba(139,92,246,.55); }
    .nexum-toggle-label { display: flex; align-items: center; gap: .5rem; cursor: pointer; }
    .nexum-toggle {
      width: 36px; height: 20px; border-radius: 10px;
      background: var(--nx-bg-toggle); border: 1px solid var(--nx-border-toggle);
      position: relative; transition: background .2s;
    }
    .nexum-toggle::after {
      content: ''; position: absolute; top: 2px; left: 2px;
      width: 14px; height: 14px; border-radius: 50%;
      background: #fff; transition: transform .2s;
    }
    .peer:checked ~ .nexum-toggle { background: #7c3aed; border-color: #6d28d9; }
    .peer:checked ~ .nexum-toggle::after { transform: translateX(16px); }

    /* ── Table ── */
    .nexum-table-wrap { overflow-x: auto; border-radius: .75rem; border: 1px solid var(--nx-border-tbl); }
    .nexum-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
    .nexum-table thead th {
      padding: .65rem .9rem; text-align: left; font-size: .7rem; font-weight: 600;
      text-transform: uppercase; letter-spacing: .05em;
      background: var(--nx-bg-thead); color: var(--nx-t2);
      border-bottom: 1px solid var(--nx-border-tbl);
    }
    .nexum-table tbody tr { border-bottom: 1px solid var(--nx-border-row); }
    .nexum-table tbody tr:last-child { border-bottom: none; }
    .nexum-table tbody td { padding: .6rem .9rem; color: var(--nx-t1); }

    /* ── Status badges ── */
    .nexum-status-ok      { padding: .15rem .55rem; border-radius: 20px; font-size: .7rem; font-weight: 600; background: rgba(16,185,129,.1); color: #059669; border: 1px solid rgba(16,185,129,.22); }
    .dark .nexum-status-ok { color: #10b981; }
    .nexum-status-pending { padding: .15rem .55rem; border-radius: 20px; font-size: .7rem; font-weight: 600; background: rgba(245,158,11,.1); color: #d97706; border: 1px solid rgba(245,158,11,.22); }
    .dark .nexum-status-pending { color: #f59e0b; }
    .nexum-status-error   { padding: .15rem .55rem; border-radius: 20px; font-size: .7rem; font-weight: 600; background: rgba(239,68,68,.1); color: #dc2626; border: 1px solid rgba(239,68,68,.2); }
    .dark .nexum-status-error   { color: #ef4444; }

    /* ── Report action buttons ── */
    .nexum-action-btns { display: inline-flex; align-items: center; gap: .4rem; }
    .nexum-view-btn {
      display: inline-flex; align-items: center; justify-content: center;
      width: 28px; height: 28px; border-radius: .5rem;
      background: var(--nx-bg-chip); border: 1px solid var(--nx-border-chip);
      color: var(--nx-t2); transition: all .15s; text-decoration: none;
    }
    .nexum-view-btn:hover {
      background: var(--nx-bg-chip-active); border-color: var(--nx-border-chip-a);
      color: var(--nx-t-chip-a);
    }
    .nexum-download-btn {
      display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .65rem;
      border-radius: .5rem; font-size: .75rem; font-weight: 600; text-decoration: none;
      background: var(--nx-bg-sec-btn); border: 1px solid var(--nx-border-sec-btn);
      color: var(--nx-t2); transition: all .15s;
    }
    .nexum-download-btn:hover { background: var(--nx-bg-chip-active); color: var(--nx-t-chip-a); }
    .nexum-delete-btn {
      display: inline-flex; align-items: center; justify-content: center;
      width: 28px; height: 28px; border-radius: .5rem;
      background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.18);
      color: #dc2626; transition: all .15s; cursor: pointer;
    }
    .dark .nexum-delete-btn { background: rgba(239,68,68,.12); border-color: rgba(239,68,68,.22); color: #f87171; }
    .nexum-delete-btn:hover { background: rgba(239,68,68,.18); border-color: rgba(239,68,68,.35); }
    .dark .nexum-delete-btn:hover { background: rgba(239,68,68,.25); }

    /* ── Report generation glass modal ─────────────────────────────────── */
    .nexum-modal-overlay {
      position: fixed; inset: 0; z-index: 9999;
      background: rgba(0, 0, 0, 0.52);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      display: flex; align-items: center; justify-content: center;
    }
    .nexum-modal-glass {
      position: relative;
      text-align: center;
      min-width: 300px;
      max-width: 400px;
      padding: 1rem;
    }
    .nexum-modal-x {
      position: absolute; top: -2rem; right: -1rem;
      width: 32px; height: 32px;
      display: flex; align-items: center; justify-content: center;
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: 50%;
      color: rgba(255,255,255,0.55);
      cursor: pointer;
      transition: background .15s, color .15s;
      line-height: 1;
    }
    .nexum-modal-x:hover { background: rgba(255,255,255,0.16); color: #fff; }
    /* Spinner ring */
    .nexum-modal-spinner {
      width: 54px; height: 54px; margin: 0 auto 1.5rem;
      border: 3px solid rgba(167,139,250,0.18);
      border-top-color: #a78bfa;
      border-radius: 50%;
      animation: nx-modal-spin 0.75s linear infinite;
    }
    @keyframes nx-modal-spin { to { transform: rotate(360deg); } }
    /* Success ring */
    .nexum-modal-check {
      width: 54px; height: 54px; margin: 0 auto 1.5rem;
      border: 2px solid rgba(52,211,153,0.4);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      background: rgba(52,211,153,0.10);
    }
    .nexum-modal-title {
      font-size: 1.15rem; font-weight: 700; color: #fff;
      margin-bottom: .35rem; letter-spacing: -.015em;
    }
    .nexum-modal-sub {
      font-size: .82rem; color: rgba(255,255,255,0.45);
    }
    .nexum-modal-actions {
      display: flex; gap: .75rem; justify-content: center; margin-top: 1.65rem;
    }
    .nexum-modal-btn-view {
      display: inline-flex; align-items: center; gap: .45rem;
      padding: .6rem 1.2rem; border-radius: .65rem;
      font-size: .82rem; font-weight: 600; text-decoration: none;
      background: rgba(167,139,250,0.14);
      border: 1px solid rgba(167,139,250,0.30);
      color: #c4b5fd;
      transition: background .15s, border-color .15s;
    }
    .nexum-modal-btn-view:hover { background: rgba(167,139,250,0.26); border-color: rgba(167,139,250,0.50); }
    .nexum-modal-btn-download {
      display: inline-flex; align-items: center; gap: .45rem;
      padding: .6rem 1.2rem; border-radius: .65rem;
      font-size: .82rem; font-weight: 600; text-decoration: none;
      background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
      border: 1px solid rgba(167,139,250,0.22);
      color: #fff;
      transition: opacity .15s;
      box-shadow: 0 2px 14px rgba(109,40,217,0.38);
    }
    .nexum-modal-btn-download:hover { opacity: .88; }
    .nexum-modal-close-link {
      margin-top: 1.1rem; display: block;
      font-size: .76rem; color: rgba(255,255,255,0.30);
      background: none; border: none; cursor: pointer;
      transition: color .15s;
    }
    .nexum-modal-close-link:hover { color: rgba(255,255,255,0.58); }
  </style>

  @livewire('nexum')

</x-app-layout>
