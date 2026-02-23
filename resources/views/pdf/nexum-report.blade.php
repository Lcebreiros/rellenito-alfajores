<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e1b4b; background: #fff; }

    /* ── Página ── */
    .page { padding: 28px 32px; }

    /* ── Header ── */
    .header { display: table; width: 100%; border-bottom: 2px solid #7c3aed; padding-bottom: 14px; margin-bottom: 18px; }
    .header-left  { display: table-cell; vertical-align: middle; width: 60%; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; }
    .brand { font-size: 22px; font-weight: bold; color: #7c3aed; letter-spacing: -0.5px; }
    .brand span { color: #4c1d95; }
    .report-title { font-size: 13px; color: #6d28d9; font-weight: bold; margin-top: 2px; }
    .report-meta  { font-size: 9px; color: #6b7280; margin-top: 3px; }
    .score-badge  {
      display: inline-block; padding: 6px 16px;
      border-radius: 20px; font-size: 14px; font-weight: bold;
      color: #fff; letter-spacing: -0.3px;
    }
    .score-label { font-size: 8px; color: #6b7280; text-align: center; margin-top: 3px; }

    /* ── Sección ── */
    .section { margin-bottom: 20px; }
    .section-title {
      font-size: 11px; font-weight: bold; color: #4c1d95;
      border-left: 3px solid #7c3aed; padding-left: 7px;
      margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;
    }

    /* ── Executive Summary ── */
    .summary-grid { display: table; width: 100%; }
    .summary-cell {
      display: table-cell; width: 25%; padding: 10px 8px;
      background: #f5f3ff; border-radius: 8px; text-align: center;
    }
    .summary-cell + .summary-cell { margin-left: 6px; }
    .summary-value { font-size: 16px; font-weight: bold; color: #4c1d95; }
    .summary-label { font-size: 8px; color: #6b7280; margin-top: 2px; }
    .change-up      { font-size: 8px; font-weight: bold; color: #059669; margin-top: 2px; }
    .change-down    { font-size: 8px; font-weight: bold; color: #dc2626; margin-top: 2px; }
    .change-neutral { font-size: 8px; color: #6b7280; margin-top: 2px; }

    /* ── Tabla base ── */
    table { width: 100%; border-collapse: collapse; font-size: 9px; }
    thead th {
      background: #4c1d95; color: #fff;
      padding: 5px 7px; text-align: left; font-size: 8.5px;
    }
    tbody tr:nth-child(even) { background: #f5f3ff; }
    tbody tr:nth-child(odd)  { background: #fff; }
    tbody td { padding: 4px 7px; color: #374151; border-bottom: 1px solid #e9d5ff; }
    .text-right  { text-align: right; }
    .text-center { text-align: center; }
    .bold { font-weight: bold; }

    /* ── Margen colores ── */
    .margin-good { color: #059669; font-weight: bold; }
    .margin-mid  { color: #b45309; font-weight: bold; }
    .margin-bad  { color: #dc2626; font-weight: bold; }

    /* ── Rotación inventario ── */
    .dead-stock { color: #dc2626; }
    .slow-stock { color: #b45309; }
    .ok-stock   { color: #059669; }

    /* ── Health score table ── */
    .health-row td { padding: 5px 7px; }
    .bar-bg   { background: #e9d5ff; border-radius: 4px; height: 8px; }
    .bar-fill { height: 8px; border-radius: 4px; }

    /* ── Insight card ── */
    .insight-item { display: table; width: 100%; margin-bottom: 6px; }
    .insight-dot  { display: table-cell; width: 8px; vertical-align: top; padding-top: 2px; }
    .insight-dot-inner { width: 6px; height: 6px; border-radius: 50%; margin-top: 1px; }
    .insight-body  { display: table-cell; padding-left: 5px; }
    .insight-title { font-weight: bold; font-size: 9px; color: #1e1b4b; }
    .insight-desc  { font-size: 8.5px; color: #6b7280; margin-top: 1px; }
    .badge {
      display: inline-block; padding: 1px 5px; border-radius: 3px;
      font-size: 7.5px; font-weight: bold; color: #fff;
    }

    /* ── Footer ── */
    .footer {
      border-top: 1px solid #e9d5ff; margin-top: 24px; padding-top: 8px;
      font-size: 7.5px; color: #9ca3af; text-align: center;
    }

    /* ── Divider ── */
    .divider { border: none; border-top: 1px solid #e9d5ff; margin: 10px 0; }

    /* ── 2-col layout ── */
    .two-col   { display: table; width: 100%; }
    .col-left  { display: table-cell; width: 48%; vertical-align: top; padding-right: 8px; }
    .col-right { display: table-cell; width: 48%; vertical-align: top; padding-left: 8px; border-left: 1px solid #e9d5ff; }
  </style>
</head>
<body>
<div class="page">

  {{-- ── HEADER ──────────────────────────────────────────── --}}
  <div class="header">
    <div class="header-left">
      <div class="brand">NEXUM <span>· Gestior</span></div>
      <div class="report-title">Reporte de Negocio — {{ $config['frequency_label'] }}</div>
      <div class="report-meta">
        Período: {{ $period['start']->format('d/m/Y') }} al {{ $period['end']->format('d/m/Y') }}
        &nbsp;·&nbsp; Generado: {{ now()->format('d/m/Y H:i') }}
        &nbsp;·&nbsp; {{ $user->business_name ?? $user->name }}
      </div>
    </div>
    <div class="header-right">
      <div class="score-badge" style="background: {{ $health['status_color'] }};">
        {{ $health['overall_score'] }}/100
      </div>
      <div class="score-label">Health Score</div>
    </div>
  </div>

  {{-- ── 1. RESUMEN EJECUTIVO ─────────────────────────────── --}}
  @php
    $rChg = $sales['revenue_change_pct'];
    $cChg = $sales['count_change_pct'];
    $tChg = $sales['ticket_change_pct'];
    $mPct = $health['categories']['costs']['metrics']['margin_pct'] ?? 0;
  @endphp
  <div class="section">
    <div class="section-title">1. Resumen Ejecutivo</div>
    <div class="summary-grid">
      <div class="summary-cell">
        <div class="summary-value">${{ number_format($sales['total_revenue'], 0, ',', '.') }}</div>
        @if($sales['prev_total_revenue'] > 0 || $sales['total_revenue'] > 0)
          <div class="{{ $rChg >= 0 ? 'change-up' : 'change-down' }}">
            {{ $rChg >= 0 ? '+' : '' }}{{ $rChg }}% vs anterior
          </div>
        @endif
        <div class="summary-label">Ingresos totales</div>
      </div>
      <div class="summary-cell">
        <div class="summary-value">{{ $sales['order_count'] }}</div>
        @if($sales['prev_order_count'] > 0 || $sales['order_count'] > 0)
          <div class="{{ $cChg >= 0 ? 'change-up' : 'change-down' }}">
            {{ $cChg >= 0 ? '+' : '' }}{{ $cChg }}% vs anterior
          </div>
        @endif
        <div class="summary-label">Pedidos completados</div>
      </div>
      <div class="summary-cell">
        <div class="summary-value">${{ number_format($sales['avg_ticket'], 0, ',', '.') }}</div>
        @if($sales['prev_avg_ticket'] > 0)
          <div class="{{ $tChg >= 0 ? 'change-up' : 'change-down' }}">
            {{ $tChg >= 0 ? '+' : '' }}{{ $tChg }}% vs anterior
          </div>
        @else
          <div class="change-neutral">Sin per. anterior</div>
        @endif
        <div class="summary-label">Ticket promedio</div>
      </div>
      <div class="summary-cell">
        <div class="summary-value" style="color: {{ $mPct >= 20 ? '#059669' : '#dc2626' }}">
          {{ $mPct }}%
        </div>
        <div class="change-neutral">${{ number_format($sales['total_revenue'] - ($health['categories']['costs']['metrics']['total_costs'] ?? 0), 0, ',', '.') }} ganancia</div>
        <div class="summary-label">Margen bruto est.</div>
      </div>
    </div>
  </div>

  {{-- ── 2 & 3. VENTAS + PRODUCTOS (2 columnas) ─────────────── --}}
  <div class="section">
    <div class="two-col">
      <div class="col-left">
        <div class="section-title">2. Ventas por Método de Pago</div>
        <table>
          <thead><tr><th>Método</th><th class="text-right">Total</th><th class="text-right">Pedidos</th></tr></thead>
          <tbody>
            @forelse($sales['by_payment_method'] as $method)
            <tr>
              <td>{{ $method['method'] }}</td>
              <td class="text-right">${{ number_format($method['total'], 0, ',', '.') }}</td>
              <td class="text-right">{{ $method['count'] }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center" style="color:#9ca3af;">Sin datos</td></tr>
            @endforelse
          </tbody>
        </table>

        {{-- Comparación período anterior --}}
        <div style="margin-top:8px; padding:6px 8px; background:#f5f3ff; border-radius:6px; font-size:8.5px;">
          <div class="bold" style="color:#4c1d95; margin-bottom:3px;">Período anterior ({{ $sales['prev_order_count'] }} pedidos)</div>
          <div style="color:#374151;">Ingresos: ${{ number_format($sales['prev_total_revenue'], 0, ',', '.') }}
            &nbsp;·&nbsp; Ticket: ${{ number_format($sales['prev_avg_ticket'], 0, ',', '.') }}</div>
        </div>
      </div>
      <div class="col-right">
        <div class="section-title">3. Top Productos</div>
        <table>
          <thead><tr><th>Producto</th><th class="text-right">Unid.</th><th class="text-right">Total</th></tr></thead>
          <tbody>
            @forelse($topProducts as $product)
            <tr>
              <td>{{ \Illuminate\Support\Str::limit($product['name'], 22) }}</td>
              <td class="text-right">{{ $product['quantity'] }}</td>
              <td class="text-right">${{ number_format($product['revenue'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center" style="color:#9ca3af;">Sin ventas en el período</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ── 3b. RENTABILIDAD POR PRODUCTO (si hay cost_price) ──── --}}
  @if(!empty($productMargin))
  <div class="section">
    <div class="section-title">3b. Rentabilidad por Producto</div>
    <table>
      <thead>
        <tr>
          <th>Producto</th>
          <th class="text-right">Unid.</th>
          <th class="text-right">Precio venta</th>
          <th class="text-right">Costo unit.</th>
          <th class="text-right">Margen</th>
          <th class="text-right">Ingreso total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($productMargin as $pm)
        <tr>
          <td>{{ \Illuminate\Support\Str::limit($pm['name'], 24) }}</td>
          <td class="text-right">{{ $pm['quantity'] }}</td>
          <td class="text-right">${{ number_format($pm['avg_price'], 2, ',', '.') }}</td>
          <td class="text-right">${{ number_format($pm['cost_price'], 2, ',', '.') }}</td>
          <td class="text-right {{ $pm['margin_pct'] >= 40 ? 'margin-good' : ($pm['margin_pct'] >= 20 ? 'margin-mid' : 'margin-bad') }}">
            {{ $pm['margin_pct'] }}%
          </td>
          <td class="text-right">${{ number_format($pm['revenue'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div style="font-size:7.5px; color:#6b7280; margin-top:4px;">
      * Solo incluye productos con costo unitario cargado. Verde ≥40%, Naranja ≥20%, Rojo &lt;20%.
    </div>
  </div>
  @endif

  {{-- ── 4. GASTOS ──────────────────────────────────────────── --}}
  <div class="section">
    <div class="section-title">4. Gastos y Costos</div>
    <table>
      <thead>
        <tr><th>Categoría</th><th class="text-right">Total</th><th class="text-right">% de Ingresos</th></tr>
      </thead>
      <tbody>
        @foreach($expenses as $exp)
        <tr>
          <td>{{ $exp['label'] }}</td>
          <td class="text-right">${{ number_format($exp['amount'], 0, ',', '.') }}</td>
          <td class="text-right">
            @if($sales['total_revenue'] > 0)
              {{ round(($exp['amount'] / $sales['total_revenue']) * 100, 1) }}%
            @else —
            @endif
          </td>
        </tr>
        @endforeach
        <tr style="background:#4c1d95;">
          <td class="bold" style="color:#fff;">Total costos</td>
          <td class="text-right bold" style="color:#fff;">${{ number_format(collect($expenses)->sum('amount'), 0, ',', '.') }}</td>
          <td class="text-right" style="color:#e9d5ff;">
            @if($sales['total_revenue'] > 0)
              {{ $health['categories']['costs']['metrics']['cost_ratio'] }}%
            @else — @endif
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  {{-- ── 5. MARGEN ─────────────────────────────────────────── --}}
  <div class="section">
    <div class="section-title">5. Margen Bruto Estimado</div>
    <table>
      <thead><tr><th>Concepto</th><th class="text-right">Monto</th><th class="text-right">%</th></tr></thead>
      <tbody>
        <tr>
          <td>Ingresos totales</td>
          <td class="text-right">${{ number_format($sales['total_revenue'], 2, ',', '.') }}</td>
          <td class="text-right">100%</td>
        </tr>
        <tr>
          <td>Total costos (estimado)</td>
          <td class="text-right">−${{ number_format($health['categories']['costs']['metrics']['total_costs'], 2, ',', '.') }}</td>
          <td class="text-right">{{ $health['categories']['costs']['metrics']['cost_ratio'] }}%</td>
        </tr>
        <tr style="background:#d1fae5;">
          <td class="bold" style="color:#065f46;">Ganancia bruta estimada</td>
          <td class="text-right bold" style="color:#065f46;">
            ${{ number_format($sales['total_revenue'] - $health['categories']['costs']['metrics']['total_costs'], 2, ',', '.') }}
          </td>
          <td class="text-right bold" style="color:#065f46;">
            {{ $health['categories']['costs']['metrics']['margin_pct'] }}%
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  {{-- ── 6 & 7. CLIENTES + INVENTARIO (2 columnas) ───────────── --}}
  <div class="section">
    <div class="two-col">
      <div class="col-left">
        <div class="section-title">6. Clientes</div>
        <table>
          <thead><tr><th>Métrica</th><th class="text-right">Valor</th></tr></thead>
          <tbody>
            <tr><td>Clientes activos (período)</td><td class="text-right bold">{{ $health['categories']['clients']['metrics']['active_now'] }}</td></tr>
            <tr><td>Clientes nuevos</td><td class="text-right">{{ $health['categories']['clients']['metrics']['new_clients'] }}</td></tr>
            <tr><td>Total clientes</td><td class="text-right">{{ $health['categories']['clients']['metrics']['total_clients'] }}</td></tr>
            <tr><td>Tasa de retención est.</td><td class="text-right">{{ $health['categories']['clients']['metrics']['retention_rate'] }}%</td></tr>
          </tbody>
        </table>
        <br>
        <div class="section-title" style="margin-top:4px;">Top Clientes</div>
        <table>
          <thead><tr><th>Cliente</th><th class="text-right">Pedidos</th><th class="text-right">Total</th></tr></thead>
          <tbody>
            @forelse($topClients as $client)
            <tr>
              <td>{{ \Illuminate\Support\Str::limit($client['name'], 20) }}</td>
              <td class="text-right">{{ $client['orders'] }}</td>
              <td class="text-right">${{ number_format($client['total'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center" style="color:#9ca3af;">Sin datos</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="col-right">
        <div class="section-title">7. Inventario</div>
        <table>
          <thead><tr><th>Métrica</th><th class="text-right">Valor</th></tr></thead>
          <tbody>
            <tr><td>Productos totales</td><td class="text-right">{{ $health['categories']['inventory']['metrics']['total'] }}</td></tr>
            <tr><td>Con stock saludable</td><td class="text-right bold" style="color:#065f46;">{{ $health['categories']['inventory']['metrics']['healthy'] }}</td></tr>
            <tr><td>Stock bajo</td><td class="text-right" style="color:#b45309;">{{ $health['categories']['inventory']['metrics']['low_stock'] }}</td></tr>
            <tr><td>Sin stock</td><td class="text-right bold" style="color:#b91c1c;">{{ $health['categories']['inventory']['metrics']['out_of_stock'] }}</td></tr>
            <tr><td>Valor del inventario</td><td class="text-right">${{ number_format($health['categories']['inventory']['metrics']['stock_value'], 0, ',', '.') }}</td></tr>
            @if($inventoryRot['has_data'])
            <tr><td>Días promedio en stock</td><td class="text-right bold">{{ $inventoryRot['avg_days_of_stock'] }} días</td></tr>
            <tr><td>Productos sin movimiento</td>
                <td class="text-right {{ $inventoryRot['dead_stock_count'] > 0 ? 'dead-stock' : 'ok-stock' }} bold">
                  {{ $inventoryRot['dead_stock_count'] }}
                </td>
            </tr>
            <tr><td>Capital inmovilizado</td>
                <td class="text-right {{ $inventoryRot['dead_stock_capital'] > 0 ? 'dead-stock' : 'ok-stock' }} bold">
                  ${{ number_format($inventoryRot['dead_stock_capital'], 0, ',', '.') }}
                </td>
            </tr>
            @endif
          </tbody>
        </table>

        {{-- Rotación por producto --}}
        @if($inventoryRot['has_data'] && !empty($inventoryRot['items']))
        <br>
        <div style="font-size:8.5px; font-weight:bold; color:#4c1d95; margin-bottom:3px;">Rotación por producto:</div>
        <table>
          <thead><tr><th>Producto</th><th class="text-right">Vendido</th><th class="text-right">Días stock</th></tr></thead>
          <tbody>
            @foreach($inventoryRot['items'] as $ri)
            <tr>
              <td>{{ \Illuminate\Support\Str::limit($ri['name'], 20) }}</td>
              <td class="text-right">{{ $ri['units_sold'] }}</td>
              <td class="text-right {{ $ri['is_dead'] ? 'dead-stock' : ($ri['days_of_stock'] > 30 ? 'slow-stock' : 'ok-stock') }} bold">
                {{ $ri['is_dead'] ? 'Inmovilizado' : ($ri['days_of_stock'] ? $ri['days_of_stock'].'d' : '—') }}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif

        @if($lowStockItems->isNotEmpty())
        <br>
        <div style="font-size:8.5px; color:#b45309; font-weight:bold; margin-bottom:3px;">Stock bajo o agotado:</div>
        @foreach($lowStockItems->take(5) as $item)
        <div style="font-size:8px; color:#374151; padding: 1px 0;">
          · {{ \Illuminate\Support\Str::limit($item->name, 26) }}
          <span style="color:{{ $item->stock <= 0 ? '#b91c1c' : '#b45309' }}">
            ({{ $item->stock <= 0 ? 'Sin stock' : 'Stock: '.$item->stock }})
          </span>
        </div>
        @endforeach
        @endif
      </div>
    </div>
  </div>

  {{-- ── 8. NEXUM HEALTH SCORE + INSIGHTS ──────────────────── --}}
  <div class="section">
    <div class="section-title">8. Nexum Health Score</div>
    <table>
      <thead><tr><th>Categoría</th><th>Valor</th><th>Barra</th><th class="text-right">Estado</th></tr></thead>
      <tbody class="health-row">
        @foreach([
          ['Ventas',     $health['categories']['revenue']],
          ['Stock',      $health['categories']['inventory']],
          ['Clientes',   $health['categories']['clients']],
          ['Margen',     $health['categories']['costs']],
        ] as [$label, $cat])
        <tr>
          <td class="bold">{{ $label }}</td>
          <td class="bold" style="color:{{ $cat['color'] }}; width:60px;">{{ $cat['display_value'] ?? $cat['score'].'/100' }}</td>
          <td style="width:120px; padding: 6px 7px;">
            <div class="bar-bg">
              <div class="bar-fill" style="width:{{ $cat['score'] }}%; background:{{ $cat['color'] }};"></div>
            </div>
          </td>
          <td class="text-right" style="color:{{ $cat['color'] }};">
            @if($cat['score'] >= 80) Excelente
            @elseif($cat['score'] >= 60) Bueno
            @elseif($cat['score'] >= 40) Regular
            @else Necesita atención
            @endif
          </td>
        </tr>
        @endforeach
        <tr style="background:#4c1d95;">
          <td colspan="1" class="bold" style="color:#fff;">OVERALL</td>
          <td class="bold" style="color:#e9d5ff; font-size:12px;">{{ $health['overall_score'] }}/100</td>
          <td style="padding: 6px 7px;">
            <div class="bar-bg" style="background:rgba(255,255,255,.2);">
              <div class="bar-fill" style="width:{{ $health['overall_score'] }}%; background:{{ $health['status_color'] }};"></div>
            </div>
          </td>
          <td class="text-right bold" style="color:#fff;">{{ $health['status'] }}</td>
        </tr>
      </tbody>
    </table>

    @if(!empty($insights))
    <div style="margin-top:10px;">
      <div style="font-size:9px; font-weight:bold; color:#4c1d95; margin-bottom:5px;">
        Diagnósticos generados al {{ now()->format('d/m/Y H:i') }}:
      </div>
      @foreach($insights as $insight)
      <div class="insight-item">
        <div class="insight-dot">
          <div class="insight-dot-inner" style="background:{{ $insight['priority_color'] }};"></div>
        </div>
        <div class="insight-body">
          <span class="insight-title">{{ $insight['title'] }}</span>
          <span class="badge" style="background:{{ $insight['priority_color'] }}; margin-left:4px;">
            {{ ucfirst($insight['priority']) }}
          </span>
          <div class="insight-desc">{{ \Illuminate\Support\Str::limit($insight['description'], 110) }}</div>
        </div>
      </div>
      @endforeach
    </div>
    @endif
  </div>

  {{-- ── FOOTER ─────────────────────────────────────────────── --}}
  <div class="footer">
    Generado automáticamente por <strong>Nexum · Gestior</strong>
    &nbsp;·&nbsp; {{ now()->format('d/m/Y H:i') }}
    &nbsp;·&nbsp; Este reporte es de uso interno y confidencial.
  </div>

</div>
</body>
</html>
