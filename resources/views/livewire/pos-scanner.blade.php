<div
    x-data="{
        handleBarcode(code) {
            if (!code || code.length < 3) return;
            $wire.scan(code);
            clearTimeout(this._scanTimer);
            this._scanTimer = setTimeout(() => $wire.resetStatus(), 2500);
        },
        init() {
            this.$refs.scanInput?.focus();
        }
    }"
    x-on:hid-barcode.window="handleBarcode($event.detail.code)"
    class="flex items-center gap-2">

  {{-- Input --}}
  <div class="relative flex-1">
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
      x-ref="scanInput"
      type="text"
      placeholder="{{ __('scanner.pos_ready') }}"
      autocomplete="off"
      x-on:keydown.enter.prevent="handleBarcode($el.value.trim()); $el.value = ''; $el.focus()"
      class="w-full rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900
             pl-8 pr-3 py-1.5 text-xs text-neutral-700 dark:text-neutral-200 placeholder-neutral-400
             focus:outline-none focus:ring-2 focus:ring-indigo-400/40 transition"
    >
  </div>

  {{-- Status badge inline --}}
  @if($status !== 'idle')
    <div class="shrink-0 flex items-center gap-1 rounded-lg px-2 py-1.5 text-[11px] font-medium transition-all
             {{ match($status) {
                 'found'     => 'bg-emerald-50 border border-emerald-200 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-400',
                 'not_found' => 'bg-rose-50 border border-rose-200 text-rose-700 dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-400',
                 'no_stock'  => 'bg-amber-50 border border-amber-200 text-amber-700 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-400',
                 'error'     => 'bg-rose-50 border border-rose-200 text-rose-700 dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-400',
                 'scanning'  => 'bg-blue-50 border border-blue-200 text-blue-700 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400',
                 default     => 'bg-neutral-50 border border-neutral-200 text-neutral-500 dark:bg-neutral-800/50 dark:border-neutral-700 dark:text-neutral-400',
             } }}">

      @if($status === 'scanning')
        <svg class="w-3 h-3 shrink-0 animate-pulse" fill="none" viewBox="0 0 24 24">
          <rect x="3" y="5" width="2" height="14" fill="currentColor"/>
          <rect x="7" y="5" width="1" height="14" fill="currentColor"/>
          <rect x="10" y="5" width="2" height="14" fill="currentColor"/>
          <rect x="14" y="5" width="1" height="14" fill="currentColor"/>
          <rect x="17" y="5" width="2" height="14" fill="currentColor"/>
        </svg>
      @elseif($status === 'found')
        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
      @elseif($status === 'no_stock')
        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
      @else
        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      @endif

      <span class="truncate max-w-[140px]">
        @switch($status)
          @case('scanning') {{ __('scanner.pos_scanning') }} @break
          @case('found') {{ $productName }} @break
          @case('not_found') {{ __('scanner.pos_not_found', ['code' => $lastCode]) }} @break
          @case('no_stock') {{ __('scanner.pos_no_stock', ['name' => $productName]) }} @break
          @case('error') {{ __('scanner.pos_error') }} @break
        @endswitch
      </span>

      @if($status === 'not_found' && $lastCode)
        <a href="{{ route('products.create', ['barcode' => $lastCode]) }}" target="_blank"
           class="ml-1 shrink-0 rounded px-1 py-0.5 text-[10px] font-semibold bg-rose-600 hover:bg-rose-700 text-white transition-colors">
          +
        </a>
      @endif

      <span wire:loading wire:target="scan" class="ml-1 shrink-0">
        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
      </span>
    </div>
  @endif

</div>
