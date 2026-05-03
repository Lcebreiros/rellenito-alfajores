{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">{{ __('products.title') }}</h1>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6">

  {{-- Mensajes --}}
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm
                dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm
                dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  {{-- Barra de acciones --}}
  <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center gap-2 text-sm">
      <span class="card-glass inline-flex items-center px-2.5 py-1.5 text-neutral-600 dark:text-neutral-300">
        {{ __('products.showing', ['first' => $products->firstItem(), 'last' => $products->lastItem(), 'total' => $products->total()]) }}
      </span>
    </div>

    <div class="flex items-center gap-2">
      {{-- Input HID: recibe scanner físico y busca el producto --}}
      <div
        x-data="{
          open: false,
          code: '',
          loading: false,
          result: null,

          init() {
            window.addEventListener('hid-barcode', (e) => {
              const modalEl = document.querySelector('[id$=\'_modal\']:not(.hidden)');
              if (modalEl) return;
              // Si hay un modal de Alpine abierto (ej. quick-create), ignorar
              if (document.querySelector('[data-barcode-modal]')?.offsetParent !== null) return;
              this.lookup(e.detail.code);
            });
          },

          async lookup(c) {
            if (!c || !c.trim()) return;
            this.code    = c.trim();
            this.open    = true;
            this.loading = true;
            this.result  = null;
            try {
              const res  = await fetch(`{{ route('products.lookup') }}?barcode=${encodeURIComponent(this.code)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
              });
              this.result = await res.json();
              if (this.result.found) {
                // Navegar directamente al producto
                setTimeout(() => { window.location.href = '/products/' + this.result.product.id + '/edit'; }, 600);
              } else {
                // Abrir modal de creación rápida
                this.open = false;
                this.code = '';
                window.dispatchEvent(new CustomEvent('barcode-create-product', { detail: { code: c.trim() } }));
              }
            } catch(e) {
              this.result = { error: true };
            } finally {
              this.loading = false;
            }
          }
        }"
        class="relative"
      >
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center">
            <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" viewBox="0 0 24 24">
              <rect x="3" y="5" width="2" height="14" fill="currentColor"/>
              <rect x="7" y="5" width="1" height="14" fill="currentColor"/>
              <rect x="10" y="5" width="2" height="14" fill="currentColor"/>
              <rect x="14" y="5" width="1" height="14" fill="currentColor"/>
              <rect x="17" y="5" width="2" height="14" fill="currentColor"/>
            </svg>
          </div>
          <input
            type="text"
            x-model="code"
            placeholder="{{ __('scanner.products_placeholder') }}"
            autocomplete="off"
            @keydown.enter.prevent="lookup(code)"
            class="w-44 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900
                   pl-8 pr-3 py-1.5 text-xs text-neutral-700 dark:text-neutral-200 placeholder-neutral-400
                   focus:outline-none focus:ring-2 focus:ring-neutral-400/30 transition"
          >
        </div>

        {{-- Panel resultado inline (solo para "encontrado") --}}
        <div
          x-show="open && result"
          x-transition
          @click.outside="open = false; code = ''; result = null"
          class="absolute left-0 top-full mt-1 z-50 w-72 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-xl p-3"
        >
          {{-- Loading --}}
          <div x-show="loading" class="flex items-center gap-2 text-xs text-neutral-500 animate-pulse">
            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            {{ __('scanner.products_searching') }}
          </div>
          {{-- Encontrado --}}
          <template x-if="result && result.found && !loading">
            <div class="flex items-center gap-3">
              <template x-if="result.product.image_url">
                <img :src="result.product.image_url" class="w-10 h-10 rounded object-contain border border-neutral-100">
              </template>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-100 truncate" x-text="result.product.name"></div>
                <div class="text-xs text-neutral-500" x-text="'$' + parseFloat(result.product.price).toFixed(2) + ' · SKU: ' + (result.product.sku || '—')"></div>
              </div>
              <span class="shrink-0 text-xs text-emerald-600 dark:text-emerald-400 font-medium animate-pulse">{{ __('scanner.products_found_opening') }}</span>
            </div>
          </template>
        </div>
      </div>

      <x-barcode-scanner />

      <form method="GET" class="hidden sm:flex items-center gap-2">
        <div class="relative">
          <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
            <path d="M21 21l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <input name="q" value="{{ request('q') }}" placeholder="{{ __('products.search_placeholder') }}"
                 class="input-enhanced w-60 pl-9">
        </div>
        @if(isset($authUser) && method_exists($authUser,'isMaster') && $authUser->isMaster())
          <input type="number" name="user_id" value="{{ request('user_id') }}" min="1" placeholder="User ID"
                 class="input-enhanced w-32" />
        @endif
        <button class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
          {{ __('products.search') }}
        </button>
      </form>

      <a href="{{ route('products.create') }}"
         class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        {{ __('products.new') }}
      </a>
    </div>
  </div>

  @if($products->count())
    <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      @foreach($products as $product)
        @php
          $image = null;
          if (!empty($product->image)) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)) {
              $image = \Illuminate\Support\Facades\Storage::url($product->image);
            }
          }
          $priceLabel = '$ ' . number_format((float) $product->price, 2, ',', '.');
          $isActive = (bool)($product->is_active ?? true);
          $stock = (int)($product->stock ?? 0);
        @endphp

        <div class="group overflow-hidden container-glass shadow-sm
                    hover:shadow-md hover:border-indigo-200/60 transition
                    dark:hover:border-indigo-500/30">

          {{-- Imagen --}}
          <div class="relative aspect-[4/3] bg-neutral-100 dark:bg-neutral-800">
            @if($image)
              <img src="{{ $image }}" alt="{{ $product->name }}"
                   class="h-full w-full object-cover">
            @else
              <div class="absolute inset-0 grid place-items-center">
                <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-300" viewBox="0 0 24 24" fill="none">
                  <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
                  <path d="M7 15l3-3 3 3 4-4 2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            @endif

            <div class="absolute left-3 top-3 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium
                        {{ $isActive
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                            : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
              {{ $isActive ? __('products.status_active') : __('products.status_inactive') }}
            </div>
          </div>

          {{-- Info --}}
          <div class="p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h2 class="text-sm sm:text-base font-medium text-neutral-900 dark:text-neutral-100 line-clamp-2">
                  {{ $product->name }}
                </h2>
                <div class="mt-0.5 text-[11px] text-neutral-500 dark:text-neutral-400">SKU: {{ $product->sku }}</div>
                @if(isset($authUser) && method_exists($authUser,'isMaster') && $authUser->isMaster())
                  @php
                    $owner = $product->user;
                    $companyName = $product->company?->name;
                    $chain = null;
                    if ($owner && $owner->representable_type === \App\Models\Branch::class) {
                        $branchName = optional($owner->representable)->name;
                        $chain = trim(($companyName ?: 'Empresa') . ' → ' . ($branchName ?: 'Sucursal'));
                    } elseif ($owner && method_exists($owner,'isCompany') && $owner->isCompany()) {
                        $chain = $owner->name;
                    } else {
                        $chain = $companyName ?: ($owner?->name ?? 'N/D');
                    }
                  @endphp
                  <div class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">
                    {{ __('products.user_label') }}: #{{ $product->user_id }} — {{ $product->user?->name ?? 'N/D' }}
                    @if(!empty($chain))
                      <span class="ml-1 text-neutral-400">({{ $chain }})</span>
                    @endif
                  </div>
                @endif
              </div>

              <span class="shrink-0 text-[11px] sm:text-xs rounded-full px-2 py-0.5 font-medium
                           {{ $stock > 0
                              ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                              : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' }}">
                {{ $stock > 0 ? __('products.stock_label', ['count' => $stock]) : __('products.no_stock') }}
              </span>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
              <span class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
                {{ $priceLabel }}
              </span>

              <div class="flex flex-wrap items-center gap-2 mt-2 sm:mt-0">
                <a href="{{ route('products.edit', $product) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-1.5 text-sm font-medium text-neutral-700 hover:bg-neutral-50
                          dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition-colors whitespace-nowrap">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  {{ __('products.edit') }}
                </a>

                <a href="{{ route('products.show', $product) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-1.5 text-sm font-medium text-neutral-700 hover:bg-neutral-50
                          dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition-colors whitespace-nowrap">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 8v4l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  {{ __('products.details') }}
                </a>
              </div>
            </div>

          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-6">
      {{ $products->withQueryString()->links() }}
    </div>
  @else
    <x-empty-state
      icon="box"
      :title="__('products.empty_title')"
      :description="__('products.empty_description')"
      :action-url="route('products.create')"
      :action-text="__('products.empty_action')"
      action-icon="plus"
    />
  @endif
</div>

{{-- ── Modal de creación rápida por código de barras ─────────────────────── --}}
<div
  data-barcode-modal
  x-data="{
    open: false,
    barcode: '',
    name: '',
    price: '',
    stock: '1',

    init() {
      window.addEventListener('barcode-create-product', (e) => {
        this.barcode = e.detail?.code ?? '';
        this.name    = '';
        this.price   = '';
        this.stock   = '1';
        this.open    = true;
        this.$nextTick(() => this.$refs.nameInput?.focus());
      });
    },

    close() { this.open = false; }
  }"
  x-show="open"
  x-cloak
  @keydown.escape.window="if(open) close()"
  class="fixed inset-0 z-50 flex items-center justify-center p-4"
  style="background: rgba(0,0,0,0.5)"
>
  <div
    class="relative bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl w-full max-w-md border border-neutral-200 dark:border-neutral-800"
    @click.outside="close()"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
  >
    {{-- Header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-neutral-200 dark:border-neutral-800">
      <div>
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">
          <svg class="inline w-4 h-4 mr-1 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <rect x="3" y="5" width="2" height="14" fill="currentColor" stroke="none"/>
            <rect x="7" y="5" width="1" height="14" fill="currentColor" stroke="none"/>
            <rect x="10" y="5" width="2" height="14" fill="currentColor" stroke="none"/>
            <rect x="14" y="5" width="1" height="14" fill="currentColor" stroke="none"/>
            <rect x="17" y="5" width="2" height="14" fill="currentColor" stroke="none"/>
          </svg>
          {{ __('scanner.modal_title') }}
        </h3>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
          {{ __('scanner.modal_subtitle') }} <code class="font-mono text-indigo-600 dark:text-indigo-400" x-text="barcode"></code>
        </p>
      </div>
      <button @click="close()" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    {{-- Info --}}
    <div class="px-5 pt-4">
      <div class="flex items-start gap-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 px-3 py-2">
        <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
        </svg>
        <p class="text-xs text-amber-700 dark:text-amber-300">{{ __('scanner.modal_not_found_info') }}</p>
      </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('products.store') }}" class="p-5 space-y-4">
      @csrf
      <input type="hidden" name="barcode"    :value="barcode">
      <input type="hidden" name="sku"        :value="barcode">
      <input type="hidden" name="is_active"  value="1">
      <input type="hidden" name="uses_stock" value="1">

      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
          {{ __('scanner.modal_name') }} <span class="text-rose-500">*</span>
        </label>
        <input
          x-ref="nameInput"
          type="text"
          name="name"
          x-model="name"
          required
          maxlength="100"
          autocomplete="off"
          class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800
                 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100
                 focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
        >
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
            {{ __('scanner.modal_price') }} <span class="text-rose-500">*</span>
          </label>
          <input
            type="number"
            name="price"
            x-model="price"
            min="0"
            step="0.01"
            required
            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800
                   px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100
                   focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('scanner.modal_stock') }}</label>
          <input
            type="number"
            name="stock"
            x-model="stock"
            min="0"
            step="1"
            class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800
                   px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100
                   focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
          >
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-1">
        <button
          type="button"
          @click="close()"
          class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-200
                 border border-neutral-300 dark:border-neutral-700 rounded-lg
                 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors"
        >
          {{ __('scanner.modal_cancel') }}
        </button>
        <button
          type="submit"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white
                 bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
          </svg>
          {{ __('scanner.modal_create') }}
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
