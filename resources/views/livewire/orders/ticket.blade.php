{{-- resources/views/livewire/orders/ticket.blade.php --}}

@php
  // Mostrar controles (Volver / Descargar) salvo que controls=0|false|off|no
  $controlsParam = strtolower((string) request()->query('controls', '1'));
  $withControls = !in_array($controlsParam, ['0','false','off','no'], true);

  // Logo
  $avatar = $logoUrl ?? null;
  $isPreview = false;
  if ($avatar) {
      $low = strtolower($avatar);
      $isPreview = str_contains($low, 'livewire-tmp')
                || str_contains($low, 'temporary')
                || str_contains($low, 'ui-avatars.com')
                || str_starts_with($low, 'blob:')
                || str_starts_with($low, 'data:');
  }
@endphp

<style>
  :root{
    --ink:#0f172a;      /* Slate-900 */
    --muted:#475569;    /* Slate-600 */
    --line:#e2e8f0;     /* Slate-200 */
    --bg:#ffffff;
  }
  @page { margin: 6mm; }
  @media print { .no-print { display:none !important; } }

  body, .ticket {
    font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    color: var(--ink);
  }

  .wrap { max-width: 360px; margin: 16px auto; padding: 0 12px; }
  .actions { display:flex; gap:8px; justify-content:center; margin: 8px 0 12px; }
  .btn {
    border:1px solid var(--line); background:#fff; color:var(--ink);
    padding:8px 12px; border-radius:12px; font-size:13px; font-weight:600; cursor:pointer;
  }
  .btn:hover { transform: translateY(-1px); transition: transform .15s ease; }

  .card {
    background: var(--bg);
    border:1px solid var(--line);
    border-radius:16px;
    box-shadow: 0 6px 24px rgba(0,0,0,.06);
    overflow:hidden;
  }

  /* Encabezado */
  .header { text-align:center; padding:16px 14px 10px; }
  .logo { max-height:56px; max-width:260px; width:auto; height:auto; object-fit:contain; display:block; margin:0 auto 6px; }
  .brand { font-weight:700; font-size:15px; letter-spacing:.3px; }
  .subtitle { color:var(--muted); font-size:11px; margin-top:2px; }
  .badge {
    display:inline-block; margin-top:10px;
    padding:4px 10px; border:1px dashed var(--line); border-radius:999px;
    font-size:11px; font-weight:700; letter-spacing:.2px;
  }

  /* Separadores */
  .rule { height:1px; background:var(--line); margin:10px 0; }
  .rule--dotted { height:2px; background: repeating-linear-gradient(90deg, var(--line), var(--line) 6px, transparent 6px, transparent 12px); margin:12px 0; }

  /* Meta */
  .meta { padding: 8px 14px; font-size:12px; }
  .row { display:flex; justify-content:space-between; gap:12px; margin:6px 0; }
  .label { color:var(--muted); }
  .value { font-weight:600; }

  /* Items */
  .items { padding: 6px 14px 10px; }
  .item { display:grid; grid-template-columns: 1fr auto; gap:8px; align-items:baseline; padding:8px 0; }
  .name { font-size:13px; font-weight:400; line-height:1.3; word-break:break-word; } /* sin negrita */
  .muted { color:var(--muted); font-size:11px; font-weight:400; }
  .item-details .unit { font-weight:400; } /* sin negrita en unitario */
  .amt  { text-align:right; font-variant-numeric: tabular-nums; font-size:13px; font-weight:700; } /* total del ítem sí bold */

  /* Totales */
  .totals { padding: 8px 14px 12px; font-size:12px; }
  .trow { display:flex; justify-content:space-between; align-items:center; margin:4px 0; }
  .trow.total { margin-top:8px; padding-top:8px; border-top:2px solid var(--line); font-weight:800; }
  .tval { font-variant-numeric: tabular-nums; }

  /* Notas */
  .notes { padding: 8px 14px 12px; }
  .notes h4 { font-size:12px; margin:0 0 4px; }
  .notes p  { font-size:11px; color:var(--muted); white-space:pre-wrap; margin:0; }

  /* QR */
  .qr { display:flex; justify-content:center; padding: 8px 0 14px; }
  .qr img { width:96px; height:96px; border-radius:8px; border:1px solid var(--line); }

  /* Pie */
  .footer { text-align:center; padding: 10px 14px 14px; }
  .small { font-size:11px; color:var(--muted); }
</style>

<div class="wrap">
  {{-- Controles opcionales (se ocultan en iframe con controls=0) --}}
  @if($withControls)
    <div class="no-print actions">
      <button class="btn" onclick="if(history.length>1){history.back()}else{location.href='{{ route('orders.index') }}'}">Volver</button>
      <button class="btn" onclick="downloadTicketPdf('{{ $order->id }}')">Descargar PDF</button>
    </div>
  @endif

  <div id="ticket" class="card ticket">
    <div class="header">
      @if($avatar && !$isPreview)
        <img src="{{ $avatar }}" alt="Logo" class="logo" referrerpolicy="no-referrer">
      @else
        <div class="brand">{{ $appName }}</div>
      @endif
      <div class="subtitle">Comprobante de Pedido</div>
      <div class="badge">#{{ $order->id }}</div>
    </div>

    <div class="rule"></div>

    <div class="meta">
      <div class="row">
        <span class="label">Fecha</span>
        <span class="value">{{ $order->created_at?->format('d/m/Y H:i') }}</span>
      </div>

      @php $cliente = $order->customer_name ?? $order->guest_name ?? null; @endphp
      @if($cliente)
        <div class="row">
          <span class="label">Cliente</span>
          <span class="value">{{ $cliente }}</span>
        </div>
      @endif

      @if(!empty($order->payment_method))
        <div class="row">
          <span class="label">Pago</span>
          <span class="value">{{ $order->payment_method }}</span>
        </div>
      @endif
    </div>

    <div class="rule--dotted"></div>

    @php
      $productItems = $order->items->filter(fn($i) => !is_null($i->product_id));
      $serviceItems = $order->items->filter(fn($i) => is_null($i->product_id) && !is_null($i->service_id));
    @endphp

    @if($productItems->count() > 0)
      <div class="items">
        <div class="muted" style="margin-bottom:4px;">Productos</div>
        @foreach($productItems as $it)
          @php
            $q = (float)$it->quantity;
            $n = $it->product->name ?? $it->product_name ?? 'Producto';
            $u = (float)$it->unit_price;
            $a = !is_null($it->subtotal) ? (float)$it->subtotal : $q * $u;
          @endphp
          <div class="item">
            <div>
              <div class="name">{{ $n }}</div>
              <div class="muted item-details">
                {{ rtrim(rtrim(number_format($q,2,',','.'),'0'),',') }} ×
                <span class="unit">${{ number_format($u,2,',','.') }}</span>
              </div>
            </div>
            <div class="amt">${{ number_format($a,2,',','.') }}</div>
          </div>
        @endforeach
      </div>
    @endif

    @if($serviceItems->count() > 0)
      <div class="items">
        <div class="muted" style="margin:6px 14px 0;">Servicios</div>
        @foreach($serviceItems as $it)
          @php
            $q = (float)$it->quantity;
            $n = $it->service->name ?? $it->product_name ?? 'Servicio';
            $u = (float)$it->unit_price;
            $a = !is_null($it->subtotal) ? (float)$it->subtotal : $q * $u;
          @endphp
          <div class="item">
            <div>
              <div class="name">{{ $n }}</div>
              <div class="muted item-details">
                {{ rtrim(rtrim(number_format($q,2,',','.'),'0'),',') }} ×
                <span class="unit">${{ number_format($u,2,',','.') }}</span>
              </div>
            </div>
            <div class="amt">${{ number_format($a,2,',','.') }}</div>
          </div>
        @endforeach
      </div>
    @endif

    @if($productItems->count() === 0 && $serviceItems->count() === 0)
      <div class="items">
        <div class="muted" style="text-align:center;padding:12px 0;">Sin ítems</div>
      </div>
    @endif

    @if($order->items->count() > 0)
      <div class="rule--dotted"></div>

      <div class="totals">
        <div class="trow">
          <span class="label">Subtotal</span>
          <span class="tval">${{ number_format($this->subtotal, 2, ',', '.') }}</span>
        </div>

        @if(($order->discount ?? 0) > 0)
          <div class="trow">
            <span class="label">Descuento</span>
            <span class="tval">- ${{ number_format($this->discount, 2, ',', '.') }}</span>
          </div>
        @endif

        @if(($order->tax_rate ?? 0) > 0)
          <div class="trow">
            <span class="label">IVA ({{ (float)$order->tax_rate }}%)</span>
            <span class="tval">${{ number_format($this->tax, 2, ',', '.') }}</span>
          </div>
        @endif

        <div class="trow total">
          <span>Total</span>
          <span class="tval">${{ number_format($this->total, 2, ',', '.') }}</span>
        </div>
      </div>
    @endif

    @if(!empty($order->notes ?? $order->note))
      <div class="rule"></div>
      <div class="notes">
        <h4>Observaciones</h4>
        <p>{{ $order->notes ?? $order->note }}</p>
      </div>
    @endif

    @if(request('qr'))
      <div class="qr">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=192x192&data={{ urlencode(request('qr')) }}" alt="QR">
      </div>
    @endif

    <div class="footer">
      <div class="small">Gracias por su compra</div>
      <div class="small" style="margin-top:2px;">
        {{ config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : request()->getHost() }}
      </div>
    </div>
  </div>
</div>

{{-- Loader html2pdf con fallback + función global de descarga --}}
@once
<script>
(function(){
  function loadHtml2Pdf(cb){
    if (window.html2pdf) return cb();
    const tryLoad = (src, onErr) => {
      const s = document.createElement('script');
      s.src = src; s.async = true; s.onload = cb;
      s.onerror = onErr || function(){ alert('No se pudo cargar html2pdf.js'); };
      document.body.appendChild(s);
    };
    tryLoad(
      "https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js",
      () => tryLoad("https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js")
    );
  }

  window.downloadTicketPdf = function(orderId){
    const el = document.getElementById('ticket');
    if (!el) { alert('No se encontró el comprobante (#ticket).'); return; }

    loadHtml2Pdf(function(){
      const clone = el.cloneNode(true);
      clone.style.margin = '0';
      clone.style.boxShadow = 'none';
      clone.style.border = '1px solid #e2e8f0';

      const container = document.createElement('div');
      container.style.background = '#fff';
      container.appendChild(clone);

      const opt = {
        margin: [6, 6, 6, 6],
        filename: `comprobante-#${orderId}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
        jsPDF: { unit: 'mm', format: 'a5', orientation: 'portrait' },
        pagebreak: { mode: ['avoid-all'] }
      };
      window.html2pdf().set(opt).from(container).save();
    });
  };
})();
</script>
@endonce

@if(request()->boolean('print'))
  <script>addEventListener('load',()=>setTimeout(()=>print(),150));</script>
@endif
