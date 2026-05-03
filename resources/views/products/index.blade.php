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
      {{-- Input HID: recibe scanner físico + abre modal de scanner con el código --}}
      <div
        x-data="{
          open: false,
          code: '',
          init() {
            // HID scanner global: auto-procesa el código si no hay modal abierto
            window.addEventListener('hid-barcode', (e) => {
              const modalEl = document.querySelector('[id$=\'_modal\']:not(.hidden)');
              if (modalEl) return; // ya lo maneja el modal abierto
              this.code = e.detail.code;
              this.open = true;
              this.$nextTick(() => this.$refs.hidInput?.select());
            });
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
            x-ref="hidInput"
            x-model="code"
            type="text"
            placeholder="{{ __('scanner.products_placeholder') }}"
            autocomplete="off"
            @keydown.enter.prevent="if(code.trim()) { open = true; }"
            class="w-44 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900
                   pl-8 pr-3 py-1.5 text-xs text-neutral-700 dark:text-neutral-200 placeholder-neutral-400
                   focus:outline-none focus:ring-2 focus:ring-neutral-400/30 transition"
          >
        </div>

        {{-- Panel resultado inline --}}
        <div
          x-show="open && code.trim()"
          x-transition
          @click.outside="open = false; code = ''"
          class="absolute left-0 top-full mt-1 z-50 w-80 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-xl p-3"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-neutral-600 dark:text-neutral-300">
              {{ __('scanner.products_result_title') }}: <code class="font-mono" x-text="code"></code>
            </span>
            <button @click="open = false; code = ''" class="text-neutral-400 hover:text-neutral-600 text-xs">✕</button>
          </div>
          {{-- Carga el resultado usando el endpoint existente --}}
          <div
            x-init="$watch('open', async (val) => {
              if (!val || !code.trim()) return;
              const res = await fetch(`{{ route('products.lookup') }}?barcode=${encodeURIComponent(code)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
              });
              const data = await res.json();
              $dispatch('hid-products-result', { data, code });
            })"
            x-on:hid-products-result.window="
              const { data, code: c } = $event.detail;
              if (data.found) {
                $el.innerHTML = `<div class='flex items-center gap-3'>
                  ${data.product.image_url ? `<img src='${data.product.image_url}' class='w-10 h-10 rounded object-contain border border-neutral-100'>` : ''}
                  <div class='flex-1 min-w-0'>
                    <div class='text-sm font-semibold text-neutral-800 dark:text-neutral-100 truncate'>${data.product.name}</div>
                    <div class='text-xs text-neutral-500'>$${parseFloat(data.product.price).toFixed(2)} · SKU: ${data.product.sku || '—'}</div>
                  </div>
                  <a href='/products/${data.product.id}/edit' class='shrink-0 rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1.5 text-xs font-semibold'>{{ __('scanner.products_edit') }}</a>
                </div>`;
              } else {
                $el.innerHTML = `<div class='text-xs text-neutral-500 mb-2'>{{ __('scanner.products_not_found') }}</div>
                  <a href='/products/create?barcode=${encodeURIComponent(c)}' class='inline-flex items-center gap-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 text-xs font-semibold'>+ {{ __('scanner.products_create') }}</a>`;
              }
            "
          >
            <div class="flex items-center gap-2 text-xs text-neutral-500 animate-pulse">
              <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
              {{ __('scanner.products_searching') }}
            </div>
          </div>
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
@endsection
