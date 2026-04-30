@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
    <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="2"/>
    </svg>
    {{ __('products.new') }}
  </h1>
@endsection

@section('content')
<div class="max-w-2xl mx-auto px-3 sm:px-6">

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

    <div class="px-6 py-5 border-b border-neutral-200/70 dark:border-neutral-800/70 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('products.form.card_title') }}</h2>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('products.form.card_subtitle') }}</p>
      </div>
    </div>

    <div class="p-6 space-y-6">

      {{-- Nombre --}}
      <div>
        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
          {{ __('products.form.name') }} <span class="text-rose-600">*</span>
        </label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="100"
               class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400
                      focus:border-indigo-500 focus:ring-indigo-500
                      dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
        <div class="mt-1 flex items-center justify-between">
          <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('products.form.name_max') }}</p>
          <p class="text-xs text-neutral-500 dark:text-neutral-400"><span id="nameCount">{{ strlen(old('name','')) }}</span>/100</p>
        </div>
        @error('name')
          <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      {{-- SKU --}}
      <div>
        <label for="sku" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
          {{ __('products.form.sku') }} <span class="text-rose-600">*</span>
        </label>
        <input id="sku" type="text" name="sku" value="{{ old('sku') }}" required maxlength="50"
               class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 uppercase tracking-wider
                      text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                      dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
        <div class="mt-1 flex items-center justify-between">
          <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('products.form.sku_max') }}</p>
          <p class="text-xs text-neutral-500 dark:text-neutral-400"><span id="skuCount">{{ strlen(old('sku','')) }}</span>/50</p>
        </div>
        @error('sku')
          <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      {{-- Código de barras --}}
      <div>
        <div class="flex items-end gap-3">
          <div class="flex-1">
            <label for="barcode" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
              {{ __('products.form.barcode_opt') }}
            </label>
            <input id="barcode" type="text" name="barcode" value="{{ old('barcode') }}" maxlength="64"
                   placeholder="EAN/UPC/QR"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5
                          text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                          dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
        </div>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ __('products.form.barcode_hint') }}</p>
      </div>

      {{-- Usa stock --}}
      <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-neutral-50 p-4
                  dark:border-neutral-800 dark:bg-neutral-950/40">
        <div>
          <label for="uses_stock" class="text-sm font-medium text-neutral-800 dark:text-neutral-100">{{ __('products.form.uses_stock') }}</label>
          <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('products.form.uses_stock_hint') }}</p>
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
            {{ __('products.form.price') }} <span class="text-rose-600">*</span>
          </label>
          <div class="flex">
            <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-neutral-300 bg-neutral-50 text-neutral-600 text-sm
                         dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
              {{ currency_symbol() }}
            </span>
            <input id="price" type="number" name="price" value="{{ old('price') }}" min="0" step="0.01" required
                   class="flex-1 rounded-none rounded-r-lg border-neutral-300 bg-white px-4 py-2.5 text-right
                          text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                          dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500"
                   placeholder="0,00">
          </div>
          @error('price')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
          @enderror
        </div>

        <div id="stockField" style="{{ old('uses_stock', true) ? '' : 'display:none' }}">
          <label for="stock" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
            {{ __('products.form.stock') }} <span class="text-rose-600">*</span>
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

      {{-- Foto --}}
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
          {{ __('products.form.photo') }}
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
              <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ __('products.form.photo_upload') }}</span>
              <span class="text-neutral-500 dark:text-neutral-400">{{ __('products.form.photo_drag') }}</span>
            </div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('products.form.photo_types') }}</p>
          </div>
          <input id="image" name="image" type="file" accept="image/*" class="sr-only">
        </label>

        <div id="imagePreviewWrap" class="mt-3 hidden">
          <div class="rounded-lg border border-neutral-200 p-2 bg-neutral-50
                      dark:border-neutral-800 dark:bg-neutral-950/40">
            <img id="imagePreview" class="max-h-48 mx-auto rounded-md object-contain" alt="Preview">
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
          <label for="is_active" class="text-sm font-medium text-neutral-800 dark:text-neutral-100">{{ __('products.form.active') }}</label>
          <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('products.form.active_hint') }}</p>
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
            <p class="font-medium mb-1">{{ __('products.form.supplies_hint_title') }}</p>
            <p>{{ __('products.form.supplies_hint') }}</p>
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
          {{ __('products.cancel') }}
        </a>

        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2.5 text-white
                       hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
            <path d="M5 12h14M12 5v14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          {{ __('products.save') }}
        </button>
      </div>
    </div>
  </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const nameInput = document.getElementById('name');
    const skuInput  = document.getElementById('sku');
    const nameCount = document.getElementById('nameCount');
    const skuCount  = document.getElementById('skuCount');

    function updateCounter(input, counter) { counter.textContent = input.value.length; }
    if (nameInput && nameCount) nameInput.addEventListener('input', () => updateCounter(nameInput, nameCount));
    if (skuInput  && skuCount ) skuInput .addEventListener('input', () => updateCounter(skuInput , skuCount ));

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

    const barcodeInput = document.getElementById('barcode');
    const resultBox = document.getElementById('barcodeResult');
    const statusEl = document.getElementById('barcodeStatus');
    const productEl = document.getElementById('barcodeProduct');

    async function lookupBarcode(code) {
      if (!resultBox) return;
      if (!code) return;
      resultBox.classList.remove('hidden');
      statusEl.textContent = @json(__('products.barcode_searching'));
      productEl.classList.add('hidden');
      try {
        const url = new URL(@json(route('products.lookup')), window.location.origin);
        url.searchParams.set('barcode', code);
        const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        const data = await res.json();
        if (!data.ok) throw new Error('Error en búsqueda');
        if (!data.found) {
          statusEl.textContent = @json(__('products.barcode_not_found'));
          productEl.classList.add('hidden');
        } else {
          const p = data.product;
          statusEl.textContent = @json(__('products.barcode_found'));
          productEl.innerHTML = `
            <div class="flex items-center gap-3">
              ${p.image_url ? `<img src="${p.image_url}" class=\"w-12 h-12 rounded object-cover\" alt=\"\">` : ''}
              <div>
                <div class="font-medium">${p.name}</div>
                <div class="text-neutral-500 text-xs">SKU: ${p.sku ?? '—'} · {{ __('products.barcode_price_label') }} $${(p.price ?? 0).toFixed(2)}</div>
              </div>
            </div>
            <div class="mt-2">
              <button type="button" id="useFoundBtn" class="inline-flex items-center gap-2 rounded bg-neutral-100 px-3 py-1.5 text-sm hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700">{{ __('products.barcode_use_btn') }}</button>
            </div>`;
          productEl.classList.remove('hidden');
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
      if (v.length < 6) return;
      lookupTimer = setTimeout(() => lookupBarcode(v), 400);
    });
  });
</script>
@endsection
