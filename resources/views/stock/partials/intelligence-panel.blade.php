{{--
  Panel de inteligencia de stock — estilo Nexum.
  Variables esperadas:
    $intel   — array retornado por StockIntelligenceService::forProduct() o forSupply()
    $subject — 'product' | 'supply'
--}}
@php
    $isPremium    = in_array(auth()->user()->effectiveSubscriptionLevel(), ['premium', 'enterprise']);
    $isSupply     = $subject === 'supply';
    $dailyValue   = $isSupply ? ($intel['dailyConsumption'] ?? 0) : ($intel['dailyAvg'] ?? 0);
    $velocityLabel = $isSupply ? 'Consumo diario estimado' : 'Velocidad de venta';

    $daysRemaining = $intel['daysRemaining'] ?? null;

    // ── Texto legible para días restantes ─────────────────────────────────
    if ($daysRemaining === null) {
        $daysText  = 'Sin datos';
        $daysShort = null;
    } elseif ($daysRemaining === 0) {
        $daysText  = 'Sin stock disponible';
        $daysShort = 0;
    } elseif ($daysRemaining > 730) {
        $years     = (int) floor($daysRemaining / 365);
        $daysText  = "Más de {$years} años de stock al ritmo actual";
        $daysShort = $daysRemaining;
    } elseif ($daysRemaining > 365) {
        $daysText  = 'Más de 1 año de stock al ritmo actual';
        $daysShort = $daysRemaining;
    } elseif ($daysRemaining > 180) {
        $daysText  = 'Más de 6 meses de stock al ritmo actual';
        $daysShort = $daysRemaining;
    } elseif ($daysRemaining > 90) {
        $months    = (int) floor($daysRemaining / 30);
        $daysText  = "Más de {$months} meses de stock al ritmo actual";
        $daysShort = $daysRemaining;
    } else {
        $daysText  = "{$daysRemaining} días al ritmo actual";
        $daysShort = $daysRemaining;
    }

    // ── Color de urgencia ─────────────────────────────────────────────────
    $urgencyDot  = match(true) {
        $daysRemaining === null         => 'background:#9ca3af',
        $daysRemaining === 0            => 'background:#ef4444',
        $daysRemaining <= 7             => 'background:#ef4444',
        $daysRemaining <= 30            => 'background:#f59e0b',
        $daysRemaining <= 90            => 'background:#3b82f6',
        default                         => 'background:#10b981',
    };
    $urgencyTextStyle = match(true) {
        $daysRemaining === null         => 'color:var(--nx-t5)',
        $daysRemaining === 0            => 'color:#ef4444;font-weight:700',
        $daysRemaining <= 7             => 'color:#ef4444;font-weight:600',
        $daysRemaining <= 30            => 'color:#d97706;font-weight:600',
        default                         => 'color:var(--nx-t1)',
    };

    // ── Quiebre: solo mostrar si es dentro de 180 días ────────────────────
    $showStockout = $isPremium && ($intel['stockoutDate'] ?? null) && $daysRemaining !== null && $daysRemaining <= 180 && $daysRemaining > 0;

    // ── Rotación ──────────────────────────────────────────────────────────
    $rotConfig = [
        'high'    => ['label' => 'Alta',           'dot' => 'background:#10b981', 'text' => 'color:#059669'],
        'medium'  => ['label' => 'Media',          'dot' => 'background:#3b82f6', 'text' => 'color:#2563eb'],
        'low'     => ['label' => 'Baja',           'dot' => 'background:#f97316', 'text' => 'color:#ea580c'],
        'dead'    => ['label' => 'Sin movimiento', 'dot' => 'background:#ef4444', 'text' => 'color:#dc2626'],
        'no_data' => ['label' => 'Sin datos',      'dot' => 'background:#9ca3af', 'text' => 'color:#6b7280'],
    ];
    $rot = $rotConfig[$intel['rotationLabel'] ?? 'no_data'] ?? $rotConfig['no_data'];

    // ── Capital inmovilizado ──────────────────────────────────────────────
    $capital    = (float) ($intel['immobilizedCapital'] ?? 0);
    $unitCost   = (float) ($intel['unitCost'] ?? 0);
    $hasCapital = $capital > 0;
    $hasCost    = $unitCost > 0;

    // ── Interpretación contextual ─────────────────────────────────────────
    $rotLabel  = $intel['rotationLabel'] ?? 'no_data';
    $hasSales  = (bool) ($intel['hasSales'] ?? false);
    $capitalFmt = $hasCapital ? '$' . number_format($capital, 0, ',', '.') : null;

    $interpretation = null;
    if ($hasSales || $rotLabel === 'dead') {
        $interpretation = match(true) {

            // Sin movimiento + stock excesivo
            $rotLabel === 'dead' && $daysRemaining !== null && $daysRemaining > 365 && $capitalFmt
                => "Este producto no genera ventas y tenés {$capitalFmt} inmovilizados sin retorno. Hacé una promoción agresiva o retiralo del catálogo.",
            $rotLabel === 'dead' && $daysRemaining !== null && $daysRemaining > 365
                => 'Este producto no genera ventas y tenés stock excesivo sin retorno proyectado. Hacé una promoción agresiva o retiralo del catálogo.',

            // Sin movimiento + algo de stock
            $rotLabel === 'dead' && $daysRemaining !== null && $daysRemaining > 0 && $capitalFmt
                => "Sin ventas recientes. Estás inmovilizando {$capitalFmt} en un producto sin demanda. Revisá si sigue activo.",
            $rotLabel === 'dead' && $daysRemaining !== null && $daysRemaining > 0
                => 'Sin ventas recientes. El stock disponible no está generando retorno. Revisá si el producto sigue activo.',

            // Sin movimiento + sin stock
            $rotLabel === 'dead'
                => 'Sin ventas registradas y sin stock. Revisá si el producto debe seguir en el catálogo.',

            // Alta rotación + sin stock
            $rotLabel === 'high' && $daysRemaining !== null && $daysRemaining === 0
                => 'Alta rotación y sin stock disponible. Estás perdiendo ventas ahora mismo. Reposición urgente.',

            // Alta rotación + stock crítico
            $rotLabel === 'high' && $daysRemaining !== null && $daysRemaining <= 7
                => 'Alta rotación con stock crítico. Si no reponés en los próximos días, vas a perder ventas activas.',

            // Alta rotación + stock ajustado
            $rotLabel === 'high' && $daysRemaining !== null && $daysRemaining <= 30
                => 'Producto con buena demanda y stock ajustado. Programá la reposición para no quedarte corto.',

            // Baja rotación + exceso grave de stock
            $rotLabel === 'low' && $daysRemaining !== null && $daysRemaining > 365 && $capitalFmt
                => "Baja rotación con stock excesivo. Estás inmovilizando {$capitalFmt} sin retorno proyectado. Evitá reponer hasta reducir el inventario.",
            $rotLabel === 'low' && $daysRemaining !== null && $daysRemaining > 365
                => 'Baja rotación con stock excesivo. Capital inmovilizado sin retorno proyectado. Evitá reponer hasta reducir el inventario.',

            // Baja rotación + stock alto
            $rotLabel === 'low' && $daysRemaining !== null && $daysRemaining > 90 && $capitalFmt
                => "Rotación baja. El stock actual te alcanza para varios meses. Tenés {$capitalFmt} inmovilizados — priorizá otros productos antes de reponer.",
            $rotLabel === 'low' && $daysRemaining !== null && $daysRemaining > 90
                => 'Rotación baja. El stock actual te alcanza para varios meses. Priorizá otros productos antes de reponer.',

            default => null,
        };
    }
@endphp

<style>
  .nxi-panel {
    --nx-bg-panel:   rgba(255,255,255,.84);
    --nx-border:     rgba(139,92,246,.18);
    --nx-border-card:rgba(139,92,246,.22);
    --nx-t1: #1e1b4b;
    --nx-t2: #5b21b6;
    --nx-t3: rgba(91,33,182,.80);
    --nx-t4: rgba(91,33,182,.62);
    --nx-t5: rgba(91,33,182,.42);
    --nx-bg-chip:    rgba(109,40,217,.07);
    --nx-shadow:     0 4px 20px rgba(109,40,217,.08);
  }
  .dark .nxi-panel {
    --nx-bg-panel:   rgba(15,8,40,.60);
    --nx-border:     rgba(139,92,246,.16);
    --nx-border-card:rgba(139,92,246,.26);
    --nx-t1: rgba(233,213,255,.90);
    --nx-t2: rgba(196,181,253,.85);
    --nx-t3: rgba(196,181,253,.70);
    --nx-t4: rgba(196,181,253,.55);
    --nx-t5: rgba(196,181,253,.38);
    --nx-bg-chip:    rgba(109,40,217,.14);
    --nx-shadow:     none;
  }

  .nxi-panel {
    background: var(--nx-bg-panel);
    border: 1px solid var(--nx-border-card);
    border-radius: 1.25rem;
    padding: 1.25rem;
    box-shadow: var(--nx-shadow);
    -webkit-backdrop-filter: blur(18px);
    backdrop-filter: blur(18px);
  }
  .dark .nxi-panel { -webkit-backdrop-filter:none; backdrop-filter:none; }

  .nxi-header {
    display:flex; align-items:center; gap:.6rem;
    margin-bottom:1rem; padding-bottom:.75rem;
    border-bottom:1px solid var(--nx-border);
  }
  .nxi-n-icon {
    width:22px; height:22px; border-radius:50%; flex-shrink:0;
    background:linear-gradient(135deg,#a78bfa 0%,#7c3aed 55%,#4c1d95 100%);
    display:flex; align-items:center; justify-content:center;
  }
  .nxi-n-icon span { color:#fff; font-size:9px; font-weight:900; letter-spacing:-.5px; }
  .nxi-title { font-size:.7rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--nx-t2); }
  .nxi-subtitle { font-size:.62rem; color:var(--nx-t4); margin-top:1px; letter-spacing:.03em; }

  .nxi-row {
    display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem;
    padding:.58rem 0;
    border-bottom:1px solid rgba(139,92,246,.07);
  }
  .nxi-row:last-child { border-bottom:none; }
  .nxi-label {
    display:flex; align-items:center; gap:.45rem;
    font-size:.74rem; color:var(--nx-t3); flex-shrink:0; padding-top:1px;
  }
  .nxi-label svg { flex-shrink:0; opacity:.7; }
  .nxi-value { font-size:.8rem; color:var(--nx-t1); text-align:right; line-height:1.4; }
  .nxi-muted { font-size:.75rem; color:var(--nx-t5); text-align:right; }
  .nxi-hint  { font-size:.67rem; color:var(--nx-t5); text-align:right; margin-top:2px; }

  .nxi-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; margin-top:4px; }
  .nxi-rot-badge {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.15rem .55rem; border-radius:20px;
    background:var(--nx-bg-chip); border:1px solid var(--nx-border);
    font-size:.72rem; font-weight:600;
  }

  .nxi-premium-section {
    margin-top:.85rem; padding-top:.85rem;
    border-top:1px solid var(--nx-border);
  }
  .nxi-premium-header {
    display:flex; align-items:center; gap:.4rem; margin-bottom:.6rem;
  }
  .nxi-premium-label {
    font-size:.65rem; font-weight:700; letter-spacing:.1em;
    text-transform:uppercase; color:var(--nx-t4);
  }
  .nxi-premium-link {
    font-size:.7rem; color:var(--nx-t2); text-decoration:none; font-weight:600; opacity:.9;
    white-space:nowrap;
  }
  .nxi-premium-link:hover { opacity:1; text-decoration:underline; }

  .nxi-interp {
    margin-top:.85rem; padding:.65rem .8rem;
    border-radius:.65rem;
    background:rgba(109,40,217,.05); border:1px solid rgba(139,92,246,.14);
    font-size:.74rem; color:var(--nx-t3); line-height:1.55;
  }
  .dark .nxi-interp { background:rgba(109,40,217,.10); border-color:rgba(139,92,246,.20); }

  .nxi-no-cost {
    font-size:.7rem; color:var(--nx-t4); font-style:italic;
  }
</style>

<div class="nxi-panel">

  {{-- Header --}}
  <div class="nxi-header">
    <div class="nxi-n-icon"><span>N</span></div>
    <div>
      <div class="nxi-title">Nexum Stock</div>
      <div class="nxi-subtitle">Inteligencia de inventario · últimos 30 días</div>
    </div>
  </div>

  {{-- ── Métricas base ── --}}

  {{-- Velocidad --}}
  <div class="nxi-row">
    <div class="nxi-label">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M8 2v4l2.5 2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        <circle cx="8" cy="8" r="6.25" stroke="currentColor" stroke-width="1.5"/>
      </svg>
      {{ $velocityLabel }}
    </div>
    <div class="nxi-value">
      @if($dailyValue > 0)
        {{ number_format($dailyValue, 2, ',', '.') }}&thinsp;<span style="font-size:.7rem;color:var(--nx-t4)">{{ $isSupply ? $supply->base_unit : 'uds.' }}/día</span>
      @else
        <span class="nxi-muted">Sin datos</span>
      @endif
    </div>
  </div>

  {{-- Días estimados --}}
  <div class="nxi-row">
    <div class="nxi-label">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="2" y="3.5" width="12" height="10.5" rx="1.5" stroke="currentColor" stroke-width="1.4"/>
        <path d="M5 2v3M11 2v3M2 7h12" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
      </svg>
      Días estimados de stock
    </div>
    <div>
      @if($daysRemaining === null)
        <div class="nxi-muted">Sin datos</div>
      @else
        <div class="nxi-value" style="{{ $urgencyTextStyle }}">{{ $daysText }}</div>
      @endif
    </div>
  </div>

  {{-- Capital inmovilizado --}}
  <div class="nxi-row">
    <div class="nxi-label">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="8" cy="8" r="6.25" stroke="currentColor" stroke-width="1.4"/>
        <path d="M8 4.5v1M8 10.5v1M5.5 6.5c0-.83.67-1.5 1.5-1.5h2a1.5 1.5 0 0 1 0 3H7a1.5 1.5 0 0 0 0 3h2c.83 0 1.5-.67 1.5-1.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
      </svg>
      Capital inmovilizado
    </div>
    <div>
      @if($hasCapital)
        <div class="nxi-value" style="color:#5b21b6;font-weight:700">
          ${{ number_format($capital, 0, ',', '.') }}
        </div>
        @if($unitCost > 0)
          <div class="nxi-hint">${{ number_format($unitCost, 2, ',', '.') }} / ud.</div>
        @endif
      @elseif(!$hasCost)
        <div class="nxi-no-cost">Registrá el costo para calcular</div>
      @else
        <div class="nxi-muted">—</div>
      @endif
    </div>
  </div>

  {{-- Rotación --}}
  <div class="nxi-row">
    <div class="nxi-label">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M13.5 5A6 6 0 1 0 14 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        <path d="M14 3v2.5h-2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Rotación
    </div>
    <div class="nxi-rot-badge" style="{{ $rot['text'] }}">
      <span class="nxi-dot" style="{{ $rot['dot'] }}"></span>
      {{ $rot['label'] }}
    </div>
  </div>

  {{-- Última venta (solo productos) --}}
  @if(!$isSupply)
    <div class="nxi-row">
      <div class="nxi-label">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M2 4.5h12M2 8.5h8M2 12.5h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
        </svg>
        Última venta
      </div>
      <div>
        @if($intel['lastSaleDate'])
          <div class="nxi-value">
            hace {{ $intel['daysSinceLastSale'] }} día{{ $intel['daysSinceLastSale'] !== 1 ? 's' : '' }}
          </div>
          <div class="nxi-hint">{{ $intel['lastSaleDate']->format('d/m/Y') }}</div>
        @else
          <span class="nxi-muted">Sin ventas registradas</span>
        @endif
      </div>
    </div>
  @endif

  {{-- ── Features Premium ── --}}
  <div class="nxi-premium-section">
    <div class="nxi-premium-header">
      @if(!$isPremium)
        <svg width="11" height="11" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" style="color:var(--nx-t4);opacity:.6">
          <rect x="2.5" y="6" width="9" height="7" rx="1.2" stroke="currentColor" stroke-width="1.3"/>
          <path d="M4.5 6V4.5a2.5 2.5 0 0 1 5 0V6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
        </svg>
      @endif
      <span class="nxi-premium-label">{{ $isPremium ? 'Predicciones' : 'Premium' }}</span>
    </div>

    {{-- Quiebre estimado --}}
    <div class="nxi-row">
      <div class="nxi-label">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M8 2L2 14h12L8 2z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
          <path d="M8 6.5v3M8 11.5h.01" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
        </svg>
        Quiebre estimado
      </div>
      @if($isPremium)
        <div>
          @if($showStockout)
            <div class="nxi-value" style="color:#d97706;font-weight:600">
              {{ $intel['stockoutDate']->translatedFormat('j \d\e M') }}
            </div>
            <div class="nxi-hint">en {{ $daysRemaining }} días</div>
          @elseif($daysRemaining === 0)
            <div class="nxi-value" style="color:#ef4444;font-weight:600">Sin stock</div>
          @elseif($daysRemaining !== null)
            <div class="nxi-value" style="color:#059669;font-size:.76rem">Sin riesgo de quiebre</div>
          @else
            <div class="nxi-muted">Sin datos</div>
          @endif
        </div>
      @else
        <a href="{{ route('plans') }}" class="nxi-premium-link">Mejorar plan →</a>
      @endif
    </div>

    {{-- Stock óptimo --}}
    <div class="nxi-row">
      <div class="nxi-label" style="flex-direction:column;align-items:flex-start;gap:2px">
        <div style="display:flex;align-items:center;gap:.45rem">
          <svg width="13" height="13" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2 12L6 7l3 3.5L11 6l3 6H2z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
          </svg>
          Stock óptimo
        </div>
        <span style="font-size:.65rem;color:var(--nx-t5);padding-left:1.25rem">14 días · promedio últimos 30 días</span>
      </div>
      @if($isPremium)
        <div>
          @if($intel['optimalStock'] ?? null)
            <div class="nxi-value" style="font-weight:700">{{ $intel['optimalStock'] }}&thinsp;<span style="font-size:.72rem;font-weight:400;color:var(--nx-t4)">uds.</span></div>
          @else
            <div class="nxi-muted">Sin datos</div>
          @endif
        </div>
      @else
        <a href="{{ route('plans') }}" class="nxi-premium-link">Mejorar plan →</a>
      @endif
    </div>
  </div>

  {{-- ── Interpretación contextual ── --}}
  @if($interpretation)
    <div class="nxi-interp">
      {{ $interpretation }}
    </div>
  @endif

</div>
