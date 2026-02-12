@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
    <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="2"/>
    </svg>
    Nuevo producto
  </h1>
@endsection

@section('content')
<div class="max-w-2xl mx-auto px-3 sm:px-6">

  {{-- Mensajes --}}
  @if(session('ok'))
    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800
                dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800
                dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data"
        class="rounded-2xl border border-neutral-200 bg-white shadow-sm overflow-hidden
               dark:border-neutral-800 dark:bg-neutral-900">
    @csrf

    {{-- Encabezado de tarjeta --}}
    <div class="px-6 py-5 border-b border-neutral-200/70 dark:border-neutral-800/70 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Datos del producto</h2>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Completa la información básica y opcionalmente una imagen.</p>
      </div>
    </div>

    <div class="p-6 space-y-6">

      {{-- Nombre --}}
      <div>
        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
          Nombre <span class="text-rose-600">*</span>
        </label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="100"
               class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400
                      focus:border-indigo-500 focus:ring-indigo-500
                      dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
        <div class="mt-1 flex items-center justify-between">
          <p class="text-xs text-neutral-500 dark:text-neutral-400">Máx. 100 caracteres</p>
          <p class="text-xs text-neutral-500 dark:text-neutral-400"><span id="nameCount">{{ strlen(old('name','')) }}</span>/100</p>
        </div>
        @error('name')
          <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      {{-- SKU --}}
      <div>
        <label for="sku" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
          SKU <span class="text-rose-600">*</span>
        </label>
        <input id="sku" type="text" name="sku" value="{{ old('sku') }}" required maxlength="50"
               class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 uppercase tracking-wider
                      text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                      dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
        <div class="mt-1 flex items-center justify-between">
          <p class="text-xs text-neutral-500 dark:text-neutral-400">Máx. 50 caracteres</p>
          <p class="text-xs text-neutral-500 dark:text-neutral-400"><span id="skuCount">{{ strlen(old('sku','')) }}</span>/50</p>
        </div>
        @error('sku')
          <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      {{-- Código de barras (manual o escaneo) --}}
      <div>
        <div class="flex items-end gap-3">
          <div class="flex-1">
            <label for="barcode" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
              Código de barras (opcional)
            </label>
            <input id="barcode" type="text" name="barcode" value="{{ old('barcode') }}" maxlength="64"
                   placeholder="EAN/UPC/QR"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5
                          text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                          dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
          
        </div>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Podés escribir o pegar el código.</p>

        
      </div>

      {{-- Usa stock --}}
      <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-neutral-50 p-4
                  dark:border-neutral-800 dark:bg-neutral-950/40">
        <div>
          <label for="uses_stock" class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Usa stock</label>
          <p class="text-xs text-neutral-500 dark:text-neutral-400">Desactivalo si el producto se prepara al momento (ej: pizzas, comidas). Solo consumirá insumos.</p>
        </div>

        <label class="inline-flex items-center">
          <input id="uses_stock" type="checkbox" name="uses_stock" value="1" class="peer sr-only"
                 {{ old('uses_stock', true) ? 'checked' : '' }}
                 onchange="document.getElementById('stockField').style.display = this.checked ? '' : 'none'">
          <span class="relative h-6 w-11 rounded-full bg-neutral-300 transition-colors duration-300
                       after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-transform
                       peer-checked:bg-indigo-600 peer-checked:after:translate-x-5
                       dark:bg-neutral-700 dark:peer-checked:bg-indigo-500"></span>
        </label>
      </div>

      {{-- Precio y Stock --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label for="price" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
            Precio (ARS) <span class="text-rose-600">*</span>
          </label>
          <input id="price" type="number" name="price" value="{{ old('price') }}" min="0" step="0.01" required
                 class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-right
                        text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500"
                 placeholder="0,00">
          @error('price')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
          @enderror
        </div>

        <div id="stockField" style="{{ old('uses_stock', true) ? '' : 'display:none' }}">
          <label for="stock" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
            Stock <span class="text-rose-600">*</span>
          </label>
          <input id="stock" type="number" name="stock" value="{{ old('stock', 0) }}" min="0" step="1"
                 class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-right
                        text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500"
                 placeholder="0">
          @error('stock')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
          @enderror
        </div>
      </div>

      {{-- Foto (opcional) + preview --}}
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
          Foto (opcional)
        </label>

        <label for="image"
               class="block cursor-pointer rounded-lg border-2 border-dashed border-neutral-300 p-5
                      text-center hover:border-indigo-400 transition-colors
                      dark:border-neutral-700 dark:hover:border-indigo-500">
          <div class="flex flex-col items-center gap-2">
            <svg class="w-7 h-7 text-neutral-400 dark:text-neutral-300" viewBox="0 0 24 24" fill="none">
              <path d="M4 7a2 2 0 0 1 2-2h2l1-1h6l1 1h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z" stroke="currentColor" stroke-width="1.6"/>
              <path d="M12 9a3.5 3.5 0 1 0 0 7a3.5 3.5 0 0 0 0-7Z" stroke="currentColor" stroke-width="1.6"/>
            </svg>
            <div class="text-sm">
              <span class="font-medium text-indigo-600 dark:text-indigo-400">Haz clic para subir</span>
              <span class="text-neutral-500 dark:text-neutral-400"> o arrastra y suelta</span>
            </div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">PNG, JPG o GIF (hasta 2MB)</p>
          </div>
          <input id="image" name="image" type="file" accept="image/*" class="sr-only">
        </label>

        <div id="imagePreviewWrap" class="mt-3 hidden">
          <div class="rounded-lg border border-neutral-200 p-2 bg-neutral-50
                      dark:border-neutral-800 dark:bg-neutral-950/40">
            <img id="imagePreview" class="max-h-48 mx-auto rounded-md object-contain" alt="Previsualización">
          </div>
        </div>

        @error('image')
          <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      {{-- Activo --}}
      <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-neutral-50 p-4
                  dark:border-neutral-800 dark:bg-neutral-950/40">
        <div>
          <label for="is_active" class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Activo</label>
          <p class="text-xs text-neutral-500 dark:text-neutral-400">Habilita el producto para aparecer en listados.</p>
        </div>

        <label class="inline-flex items-center">
          <input id="is_active" type="checkbox" name="is_active" value="1" class="peer sr-only"
                 {{ old('is_active', true) ? 'checked' : '' }}>
          <span class="relative h-6 w-11 rounded-full bg-neutral-300 transition-colors duration-300
                       after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-transform
                       peer-checked:bg-indigo-600 peer-checked:after:translate-x-5
                       dark:bg-neutral-700 dark:peer-checked:bg-indigo-500"></span>
        </label>
      </div>
      @error('is_active')
        <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
      @enderror

      {{-- Nota sobre Insumos --}}
      <div class="rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 p-4">
        <div class="flex items-start gap-3">
          <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div class="text-sm text-blue-800 dark:text-blue-300">
            <p class="font-medium mb-1">Insumos y Receta</p>
            <p>Después de crear el producto, podrás asignarle insumos (emboltorios, etiquetas, etc.) desde la vista de edición.
               Los insumos se descuentan automáticamente del stock al vender el producto.</p>
          </div>
        </div>
      </div>

      {{-- Acciones --}}
      <div class="pt-4 mt-4 border-t border-neutral-200 dark:border-neutral-800 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="{{ route('products.index') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg border border-neutral-300 px-5 py-2.5 text-neutral-700 hover:bg-neutral-50
                  dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
            <path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Cancelar
        </a>

        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2.5 text-white
                       hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
            <path d="M5 12h14M12 5v14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Guardar
        </button>
      </div>
    </div>
  </form>
</div>

{{-- Scripts mínimos: contadores + preview de imagen --}}
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const nameInput = document.getElementById('name');
    const skuInput  = document.getElementById('sku');
    const nameCount = document.getElementById('nameCount');
    const skuCount  = document.getElementById('skuCount');

    function updateCounter(input, counter) { counter.textContent = input.value.length; }
    if (nameInput && nameCount) nameInput.addEventListener('input', () => updateCounter(nameInput, nameCount));
    if (skuInput  && skuCount ) skuInput .addEventListener('input', () => updateCounter(skuInput , skuCount ));

    // Preview imagen
    const fileInput = document.getElementById('image');
    const wrap = document.getElementById('imagePreviewWrap');
    const img  = document.getElementById('imagePreview');

    if (fileInput) {
      fileInput.addEventListener('change', () => {
        const file = fileInput.files?.[0];
        if (!file) { wrap.classList.add('hidden'); img.src = ''; return; }
        const reader = new FileReader();
        reader.onload = e => {
          img.src = e.target.result;
          wrap.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
      });
    }

    // Lookup por código de barras (cuando el usuario escribe/pega)
    const barcodeInput = document.getElementById('barcode');
    const resultBox = document.getElementById('barcodeResult');
    const statusEl = document.getElementById('barcodeStatus');
    const productEl = document.getElementById('barcodeProduct');

    async function lookupBarcode(code) {
      if (!resultBox) return;
      if (!code) return;
      resultBox.classList.remove('hidden');
      statusEl.textContent = 'Buscando…';
      productEl.classList.add('hidden');
      try {
        const url = new URL(@json(route('products.lookup')), window.location.origin);
        url.searchParams.set('barcode', code);
        const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        const data = await res.json();
        if (!data.ok) throw new Error('Error en búsqueda');
        if (!data.found) {
          statusEl.textContent = 'No se encontró un producto con este código. Podés crearlo con este código.';
          productEl.classList.add('hidden');
        } else {
          const p = data.product;
          statusEl.textContent = 'Producto encontrado. Podés usar estos datos o modificarlos.';
          productEl.innerHTML = `
            <div class="flex items-center gap-3">
              ${p.image_url ? `<img src="${p.image_url}" class=\"w-12 h-12 rounded object-cover\" alt=\"\">` : ''}
              <div>
                <div class="font-medium">${p.name}</div>
                <div class="text-neutral-500 text-xs">SKU: ${p.sku ?? '—'} · Precio: $${(p.price ?? 0).toFixed(2)}</div>
              </div>
            </div>
            <div class="mt-2">
              <button type="button" id="useFoundBtn" class="inline-flex items-center gap-2 rounded bg-neutral-100 px-3 py-1.5 text-sm hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700">Usar estos datos</button>
            </div>`;
          productEl.classList.remove('hidden');
          // Autorrelleno al click
          setTimeout(() => {
            const useBtn = document.getElementById('useFoundBtn');
            if (useBtn) {
              useBtn.addEventListener('click', () => {
                document.getElementById('name').value = p.name ?? '';
                document.getElementById('sku').value = p.sku ?? '';
                document.getElementById('price').value = (p.price ?? 0);
              });
            }
          }, 0);
        }
      } catch (e) {
        statusEl.textContent = 'No se pudo completar la búsqueda.';
        productEl.classList.add('hidden');
      }
    }

    let lookupTimer;
    barcodeInput?.addEventListener('input', (e) => {
      clearTimeout(lookupTimer);
      const v = e.target.value.trim();
      if (v.length < 6) return; // evitar spam
      lookupTimer = setTimeout(() => lookupBarcode(v), 400);
    });

    // Escaneo por cámara con html5-qrcode si está disponible
    const scanBtn = document.getElementById('scanBtn');
    let scannerInstance = null;
    let scannerOpen = false;

    function ensureScannerContainer() {
      let el = document.getElementById('qr-scanner');
      if (!el) {
        el = document.createElement('div');
        el.id = 'qr-scanner';
        el.className = 'fixed inset-0 bg-black/70 z-50 hidden';
        el.innerHTML = `
          <div class=\"absolute inset-0 grid place-items-center p-4\">
            <div class=\"w-full max-w-md rounded-xl bg-white dark:bg-neutral-900 p-3 shadow-lg\">
              <div class=\"flex items-center justify-between mb-2\">
                <div class=\"text-sm font-medium\">Escanear código</div>
                <button type=\"button\" id=\"closeScan\" class=\"text-sm px-2 py-1 rounded border border-neutral-300 dark:border-neutral-700\">Cerrar</button>
              </div>
              <div id=\"qr-reader\" class=\"rounded overflow-hidden\"></div>
            </div>
          </div>`;
        document.body.appendChild(el);
      }
      return el;
    }

    async function startScan() {
      const container = ensureScannerContainer();
      container.classList.remove('hidden');
      scannerOpen = true;
      // Cerrar
      container.querySelector('#closeScan').onclick = stopScan;
      // Html5Qrcode
      const runHtml5 = () => {
        try {
          const Html5Qrcode = window.Html5Qrcode;
          if (!Html5Qrcode) return false;
          const target = document.getElementById('qr-reader');
          scannerInstance = new Html5Qrcode('qr-reader');
          const config = { fps: 10, qrbox: 250, aspectRatio: 1.7778, formatsToSupport: [
            Html5QrcodeSupportedFormats.QR_CODE,
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8,
            Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E,
            Html5QrcodeSupportedFormats.CODE_128,
          ]};
          scannerInstance.start({ facingMode: 'environment' }, config, (decodedText) => {
            if (!scannerOpen) return;
            barcodeInput.value = decodedText;
            lookupBarcode(decodedText);
            stopScan();
          });
          return true;
        } catch (e) {
          return false;
        }
      };

      if (!runHtml5()) {
        // Cargar script on-demand y reintentar una vez
        const s = document.createElement('script');
        s.src = 'https://unpkg.com/html5-qrcode';
        s.async = true;
        s.onload = () => runHtml5();
        document.head.appendChild(s);
      }
    }

    async function stopScan() {
      const container = document.getElementById('qr-scanner');
      container?.classList.add('hidden');
      scannerOpen = false;
      try { await scannerInstance?.stop(); } catch (_) {}
      try { await scannerInstance?.clear(); } catch (_) {}
      scannerInstance = null;
    }

    scanBtn?.addEventListener('click', () => startScan());
  });
</script>
@endsection
