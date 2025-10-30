@props([
  'id' => null,
  'buttonLabel' => 'Agregar por código',
])
@php($cid = $id ?? ('bc_' . \Illuminate\Support\Str::random(6)))

<button type="button" id="{{ $cid }}_open"
  class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50
         dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <rect x="3" y="5" width="2" height="14" fill="currentColor"/>
    <rect x="7" y="5" width="1" height="14" fill="currentColor"/>
    <rect x="10" y="5" width="2" height="14" fill="currentColor"/>
    <rect x="14" y="5" width="1" height="14" fill="currentColor"/>
    <rect x="17" y="5" width="2" height="14" fill="currentColor"/>
  </svg>
  {{ $buttonLabel }}
</button>

<div id="{{ $cid }}_modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/50" data-close="1"></div>
  <div class="absolute inset-x-0 top-10 mx-auto w-full max-w-lg px-3">
    <div class="rounded-2xl bg-white p-4 shadow-lg ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-neutral-800">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Agregar por código</h2>
        <button type="button" id="{{ $cid }}_close" class="rounded border px-2 py-1 text-sm dark:border-neutral-700">Cerrar</button>
      </div>
      <div class="mt-3 space-y-3">
        <div class="flex items-end gap-2">
          <div class="flex-1">
            <label for="{{ $cid }}_barcode" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Código de barras</label>
            <input id="{{ $cid }}_barcode" type="text" maxlength="64" placeholder="EAN/UPC/QR"
                   class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-neutral-800 placeholder-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                          dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder-neutral-500">
          </div>
          <button type="button" id="{{ $cid }}_scan" class="h-[38px] inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M3 7V5a2 2 0 0 1 2-2h2M3 17v2a2 2 0 0 0 2 2h2M17 3h2a2 2 0 0 1 2 2v2M19 17v2a2 2 0 0 1-2 2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Escanear
          </button>
          <button type="button" id="{{ $cid }}_lookup" class="h-[38px] inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Buscar
          </button>
        </div>
        <div id="{{ $cid }}_result" class="hidden rounded-lg border border-neutral-200 p-3 text-sm dark:border-neutral-800">
          <div id="{{ $cid }}_status" class="text-neutral-700 dark:text-neutral-300">—</div>
          <form id="{{ $cid }}_form" method="POST" action="{{ route('products.store') }}" class="mt-3 space-y-3 hidden">
            @csrf
            <input type="hidden" name="barcode" id="{{ $cid }}_form_barcode">
            <input type="hidden" name="sku" id="{{ $cid }}_form_sku">
            <input type="hidden" name="stock" value="0">
            <input type="hidden" name="is_active" value="1">
            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Nombre</label>
              <input name="name" id="{{ $cid }}_form_name" type="text" required maxlength="100" class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-neutral-800 placeholder-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
            </div>
            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Precio</label>
              <input name="price" id="{{ $cid }}_form_price" type="number" step="0.01" min="0" required class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-neutral-800 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
            </div>
            <div class="pt-1 flex items-center justify-end gap-2">
              <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Crear producto
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@once
@push('scripts')
<script>
  (function(){
    const openBtn = document.getElementById(@json($cid . '_open'));
    const modal = document.getElementById(@json($cid . '_modal'));
    const closeBtn = document.getElementById(@json($cid . '_close'));
    const barcodeInput = document.getElementById(@json($cid . '_barcode'));
    const lookupBtn = document.getElementById(@json($cid . '_lookup'));
    const scanBtn = document.getElementById(@json($cid . '_scan'));
    const resultBox = document.getElementById(@json($cid . '_result'));
    const statusEl = document.getElementById(@json($cid . '_status'));
    const form = document.getElementById(@json($cid . '_form'));
    const formName = document.getElementById(@json($cid . '_form_name'));
    const formPrice = document.getElementById(@json($cid . '_form_price'));
    const formSku = document.getElementById(@json($cid . '_form_sku'));
    const formBarcode = document.getElementById(@json($cid . '_form_barcode'));

    if (!openBtn || !modal) return;

    const open = () => { modal.classList.remove('hidden'); setTimeout(()=>barcodeInput?.focus(), 0); }
    const close = () => { modal.classList.add('hidden'); resetUI(); }
    const resetUI = () => {
      resultBox.classList.add('hidden');
      form.classList.add('hidden');
      statusEl.textContent = '—';
      formName.value = '';
      formPrice.value = '';
      barcodeInput.value = '';
    }
    openBtn.addEventListener('click', open);
    closeBtn.addEventListener('click', close);
    modal.addEventListener('click', (e)=>{ if (e.target.dataset.close) close(); });

    async function lookupExternal(code){
  if (!code) return;
  resultBox.classList.remove('hidden');
  statusEl.innerHTML = '<span class="animate-pulse">🔍 Buscando en bases de datos...</span>';
  form.classList.add('hidden');
  
  try {
    console.log('Buscando código:', code);
    
    const url = new URL(@json(route('products.lookup.external')), window.location.origin);
    url.searchParams.set('barcode', code);
    
    const res = await fetch(url.toString(), { 
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });
    
    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }
    
    const data = await res.json();
    console.log('Respuesta:', data);
    
    if (data.found && data.product && data.product.name) {
      // ✅ Producto encontrado en APIs externas
      const productName = data.product.name;
      const productBrand = data.product.brand;
      const source = data.product.source || 'base de datos';
      
      statusEl.innerHTML = `
        <div class="flex items-start gap-2">
          <span class="text-green-600 dark:text-green-400 text-lg">✅</span>
          <div>
            <div class="font-medium text-green-700 dark:text-green-300">Producto reconocido</div>
            <div class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
              Encontrado en ${source}. Revisá y completá el precio.
            </div>
          </div>
        </div>
      `;
      
      // Combinar marca + nombre si hay marca
      formName.value = productBrand && productBrand.toLowerCase() !== productName.toLowerCase()
        ? `${productBrand} ${productName}`
        : productName;
      
    } else {
      // ❌ No encontrado
      statusEl.innerHTML = `
        <div class="flex items-start gap-2">
          <span class="text-amber-600 dark:text-amber-400 text-lg">ℹ️</span>
          <div>
            <div class="font-medium text-amber-700 dark:text-amber-300">No encontrado en bases públicas</div>
            <div class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
              Podés crear el producto manualmente completando los datos.
            </div>
          </div>
        </div>
      `;
      
      // Dejar el nombre vacío para que lo complete manualmente
      formName.value = '';
    }
    
    // Siempre llenar estos campos
    formSku.value = code;
    formBarcode.value = code;
    form.classList.remove('hidden');
    
    // Focus en el campo de nombre si está vacío
    if (!formName.value) {
      setTimeout(() => formName.focus(), 100);
    } else {
      setTimeout(() => formPrice.focus(), 100);
    }
    
  } catch (e) {
    console.error('Error en lookup:', e);
    
    statusEl.innerHTML = `
      <div class="flex items-start gap-2">
        <span class="text-red-600 dark:text-red-400 text-lg">⚠️</span>
        <div>
          <div class="font-medium text-red-700 dark:text-red-300">Error de conexión</div>
          <div class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
            ${e.message}. Cargá el producto manualmente.
          </div>
        </div>
      </div>
    `;
    
    formName.value = '';
    formSku.value = code;
    formBarcode.value = code;
    form.classList.remove('hidden');
    setTimeout(() => formName.focus(), 100);
  }
}
    lookupBtn.addEventListener('click', ()=>{
      const code = barcodeInput.value.trim();
      if (!code) { barcodeInput.focus(); return; }
      lookupExternal(code);
    });

    // Scanner layer
    let scanLayer = null, scanner = null, scannerOpen = false;
    function ensureLayer(){
      if (scanLayer) return scanLayer;
      scanLayer = document.createElement('div');
      scanLayer.className = 'fixed inset-0 z-[60] hidden';
      scanLayer.innerHTML = `
        <div class="absolute inset-0 bg-black/60" data-close="1"></div>
        <div class="absolute inset-x-0 top-10 mx-auto w-full max-w-md px-3">
          <div class="rounded-2xl bg-white p-3 shadow-lg ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-neutral-800">
            <div class="flex items-center justify-between mb-2">
              <div class="text-sm font-medium">Escanear código</div>
              <button type="button" id="${@json($cid)}_qr_close" class="rounded border px-2 py-1 text-sm dark:border-neutral-700">Cerrar</button>
            </div>
            <div id="${@json($cid)}_qr_reader" class="rounded overflow-hidden"></div>
          </div>
        </div>`;
      document.body.appendChild(scanLayer);
      scanLayer.addEventListener('click', (e)=>{ if (e.target.dataset.close) stopScan(); });
      scanLayer.querySelector('#' + @json($cid) + '_qr_close').onclick = stopScan;
      return scanLayer;
    }
    function runScanner(){
      try {
        const Html5Qrcode = window.Html5Qrcode;
        const Html5QrcodeSupportedFormats = window.Html5QrcodeSupportedFormats;
        if (!Html5Qrcode || !Html5QrcodeSupportedFormats) return false;
        scanner = new Html5Qrcode(@json($cid . '_qr_reader'));
        const config = { fps: 10, qrbox: 250, aspectRatio: 1.7778, formatsToSupport: [
          Html5QrcodeSupportedFormats.QR_CODE,
          Html5QrcodeSupportedFormats.EAN_13,
          Html5QrcodeSupportedFormats.EAN_8,
          Html5QrcodeSupportedFormats.UPC_A,
          Html5QrcodeSupportedFormats.UPC_E,
          Html5QrcodeSupportedFormats.CODE_128,
        ]};
        scanner.start({ facingMode: 'environment' }, config, (decodedText)=>{
          if (!scannerOpen) return;
          barcodeInput.value = decodedText;
          stopScan();
          lookupExternal(decodedText);
        });
        return true;
      } catch (e) { return false; }
    }
    function startScan(){
      ensureLayer().classList.remove('hidden');
      scannerOpen = true;
      if (!runScanner()){
        const s = document.createElement('script');
        s.src = 'https://unpkg.com/html5-qrcode';
        s.async = true;
        s.onload = () => runScanner();
        document.head.appendChild(s);
      }
    }
    async function stopScan(){
      scannerOpen = false;
      ensureLayer().classList.add('hidden');
      try { await scanner?.stop(); } catch(_){}
      try { await scanner?.clear(); } catch(_){}
      scanner = null;
    }
    scanBtn.addEventListener('click', startScan);
  })();
</script>
@endpush
@endonce