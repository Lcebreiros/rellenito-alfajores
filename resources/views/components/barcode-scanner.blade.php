  @props([
    'id' => null,
    'buttonLabel' => null,
  ])
  @php
    $cid = $id ?? ('bc_' . \Illuminate\Support\Str::random(6));
    $btnLabel = $buttonLabel ?? __('products.scanner_title');
  @endphp

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
    {{ $btnLabel }}
  </button>

  <div id="{{ $cid }}_modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" data-close="1"></div>
    <div class="absolute inset-x-0 top-10 mx-auto w-full max-w-lg px-3">
      <div class="rounded-2xl bg-white p-4 shadow-lg ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-neutral-800">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">{{ __('products.scanner_title') }}</h2>
          <button type="button" id="{{ $cid }}_close" class="rounded border px-2 py-1 text-sm dark:border-neutral-700">{{ __('products.scanner_close') }}</button>
        </div>
        <div class="mt-3 space-y-3">
          <div class="flex items-end gap-2">
            <div class="flex-1">
              <label for="{{ $cid }}_barcode" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ __('products.scanner_barcode_label') }}</label>
              <input id="{{ $cid }}_barcode" type="text" maxlength="64" placeholder="EAN/UPC/QR"
                    class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-neutral-800 placeholder-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                            dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder-neutral-500">
            </div>
            <button type="button" id="{{ $cid }}_scan" class="h-[38px] inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M3 7V5a2 2 0 0 1 2-2h2M3 17v2a2 2 0 0 0 2 2h2M17 3h2a2 2 0 0 1 2 2v2M19 17v2a2 2 0 0 1-2 2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              {{ __('products.scanner_scan_btn') }}
            </button>
            <button type="button" id="{{ $cid }}_lookup" class="h-[38px] inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
              {{ __('products.scanner_lookup_btn') }}
            </button>
          </div>
          <div id="{{ $cid }}_result" class="hidden rounded-lg border border-neutral-200 p-3 text-sm dark:border-neutral-800">
            <div id="{{ $cid }}_status" class="text-neutral-700 dark:text-neutral-300">—</div>
            <form id="{{ $cid }}_form" method="POST" action="{{ route('products.store') }}" class="mt-3 space-y-3 hidden">
              @csrf
              <input type="hidden" name="barcode"    id="{{ $cid }}_form_barcode">
              <input type="hidden" name="sku"        id="{{ $cid }}_form_sku">
              <input type="hidden" name="is_active"  value="1">
              <input type="hidden" name="uses_stock" value="1">
              <input type="hidden" name="_existing_product_id" id="{{ $cid }}_existing_id">
              <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ __('products.scanner_field_name') }}</label>
                <input name="name" id="{{ $cid }}_form_name" type="text" required maxlength="100" class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-neutral-800 placeholder-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ __('products.scanner_field_price') }}</label>
                  <input name="price" id="{{ $cid }}_form_price" type="number" step="0.01" min="0" required class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-neutral-800 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                </div>
                <div>
                  <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ __('products.scanner_field_stock') }}</label>
                  <input name="stock" id="{{ $cid }}_form_stock" type="number" step="1" min="1" value="1" required class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-neutral-800 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                </div>
              </div>
              <div class="pt-1 flex items-center justify-end gap-2">
                <button type="submit" id="{{ $cid }}_submit_btn" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                  {{ __('products.scanner_create_btn') }}
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
      const formStock = document.getElementById(@json($cid . '_form_stock'));
      const existingIdField = document.getElementById(@json($cid . '_existing_id'));
      const submitBtn = document.getElementById(@json($cid . '_submit_btn'));

      if (!openBtn || !modal) return;

      const open = () => { modal.classList.remove('hidden'); setTimeout(()=>barcodeInput?.focus(), 0); }
      const close = () => { modal.classList.add('hidden'); resetUI(); }
      const resetUI = () => {
        resultBox.classList.add('hidden');
        form.classList.add('hidden');
        statusEl.textContent = '—';
        formName.value = '';
        formPrice.value = '';
        formStock.value = '1';
        existingIdField.value = '';
        barcodeInput.value = '';
        submitBtn.textContent = @json(__('products.scanner_create_btn'));
      }
      openBtn.addEventListener('click', open);
      closeBtn.addEventListener('click', close);
      modal.addEventListener('click', (e)=>{ if (e.target.dataset.close) close(); });

      // ── HID scanner: siempre activo, igual que la cámara ──
      window.addEventListener('hid-barcode', (e) => {
        const code = e.detail?.code;
        if (!code) return;
        if (modal.classList.contains('hidden')) open();
        barcodeInput.value = code;
        lookupExternal(code);
      });

  async function lookupExternal(code){
    if (!code) return;
    resultBox.classList.remove('hidden');
    statusEl.innerHTML = '<span class="animate-pulse">{{ __('products.scanner_searching_ext') }}</span>';
    form.classList.add('hidden');

    try {
      // 1️⃣ Primero buscar en BD local
      const localUrl = new URL(@json(route('products.lookup')), window.location.origin);
      localUrl.searchParams.set('barcode', code);

      const localRes = await fetch(localUrl.toString(), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      if (localRes.ok) {
        const localData = await localRes.json();

        if (localData.found && localData.product) {
          const p = localData.product;

          statusEl.innerHTML = `
            <div class="flex items-center gap-2 text-sm">
              <span class="text-blue-600 dark:text-blue-400 text-lg">📦</span>
              <div>
                <div class="font-medium text-blue-700 dark:text-blue-300">${p.name}</div>
                <div class="text-xs text-neutral-500 dark:text-neutral-400 animate-pulse">{{ __('scanner.products_found_opening') }}</div>
              </div>
            </div>
          `;

          setTimeout(() => {
            close();
            window.location.href = '/products/' + p.id + '/edit';
          }, 700);
          return;
        }
      }

      // 2️⃣ Si no existe localmente, buscar en APIs externas
      statusEl.innerHTML = '<span class="animate-pulse">{{ __('products.scanner_searching_ext') }}</span>';

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

      if (data.found && data.product && data.product.name) {
        const productName = data.product.name;
        const productBrand = data.product.brand;
        const source = data.product.source || 'base de datos';
        const imageUrl = data.product.image_url;

        statusEl.innerHTML = `
          <div class="flex items-start gap-3">
            ${imageUrl ? `
              <img src="${imageUrl}"
                  alt="${productName}"
                  class="w-20 h-20 object-contain rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white"
                  onerror="this.style.display='none'">
            ` : ''}
            <div class="flex-1">
              <div class="flex items-start gap-2">
                <span class="text-green-600 dark:text-green-400 text-lg">✅</span>
                <div>
                  <div class="font-medium text-green-700 dark:text-green-300">{{ __('products.scanner_recognized') }}</div>
                  <div class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
                    {{ str_replace(':source', '${source}', __('products.scanner_found_in')) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;

        formName.value = productBrand && productBrand.toLowerCase() !== productName.toLowerCase()
          ? `${productBrand} ${productName}`
          : productName;

        if (imageUrl) {
          let imageField = document.getElementById(@json($cid . '_form_image'));
          if (!imageField) {
            imageField = document.createElement('input');
            imageField.type = 'hidden';
            imageField.name = 'external_image_url';
            imageField.id = @json($cid . '_form_image');
            form.appendChild(imageField);
          }
          imageField.value = imageUrl;
        }

        formSku.value = code;
        formBarcode.value = code;
        existingIdField.value = '';
        formStock.value = '1';
        submitBtn.textContent = @json(__('products.scanner_create_btn'));
        form.classList.remove('hidden');
        setTimeout(() => formPrice.focus(), 100);

      } else {
        // No encontrado en ninguna base → abrir página de crear producto con código precargado
        statusEl.innerHTML = `
          <div class="flex items-center gap-2 text-sm">
            <span class="text-amber-600 dark:text-amber-400 text-lg">➕</span>
            <div>
              <div class="font-medium text-amber-700 dark:text-amber-300">{{ __('products.scanner_not_found') }}</div>
              <div class="text-xs text-neutral-500 dark:text-neutral-400 animate-pulse">{{ __('scanner.products_found_opening') }}</div>
            </div>
          </div>
        `;
        setTimeout(() => {
          close();
          window.location.href = '/products/create?barcode=' + encodeURIComponent(code);
        }, 500);
      }

    } catch (e) {
      console.error('Error en lookup:', e);

      statusEl.innerHTML = `
        <div class="flex items-start gap-2">
          <span class="text-red-600 dark:text-red-400 text-lg">⚠️</span>
          <div>
            <div class="font-medium text-red-700 dark:text-red-300">{{ __('products.scanner_error') }}</div>
            <div class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
              ${e.message}. {{ __('products.scanner_error_hint') }}
            </div>
          </div>
        </div>
      `;

      formName.value = '';
      formSku.value = code;
      formBarcode.value = code;
      existingIdField.value = '';
      formStock.value = '1';
      submitBtn.textContent = @json(__('products.scanner_create_btn'));
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
                <div class="text-sm font-medium">{{ __('products.scanner_scan_title') }}</div>
                <button type="button" id="${@json($cid)}_qr_close" class="rounded border px-2 py-1 text-sm dark:border-neutral-700">{{ __('products.scanner_close') }}</button>
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
