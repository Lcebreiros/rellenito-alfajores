{{-- resources/views/livewire/product-card.blade.php --}}
<div class="product-card group"> {{-- ÚNICO elemento raíz --}}
  @php
    $canAdd   = (int)($currentStock ?? 0) > 0 && (bool)($isActive ?? false);
    $name     = $product->name ?? 'Producto';
    $sku      = $product->sku  ?? 'N/A';
    $priceNum = (float) ($product->price ?? 0);
    $priceLbl = '$ ' . number_format($priceNum, 2, ',', '.');

    $thumb    = isset($product->photo_path) && $product->photo_path
                  ? \Illuminate\Support\Facades\Storage::url($product->photo_path)
                  : null;

    if(!$isActive){
      $badgeText = 'Inactivo';
      $badgeDot  = 'bg-slate-400';
      $badgeRing = 'ring-slate-200/80 dark:ring-slate-600';
      $badgeTxt  = 'text-slate-600 dark:text-slate-300';
    } elseif(($currentStock ?? 0) > 0){
      $badgeText = 'Stock: '.(int)$currentStock;
      $badgeDot  = 'bg-emerald-500';
      $badgeRing = 'ring-emerald-200/60 dark:ring-emerald-800';
      $badgeTxt  = 'text-emerald-700 dark:text-emerald-300';
    } else {
      $badgeText = 'Sin stock';
      $badgeDot  = 'bg-rose-500';
      $badgeRing = 'ring-rose-200/60 dark:ring-rose-800';
      $badgeTxt  = 'text-rose-700 dark:text-rose-300';
    }
  @endphp

  @if($displayMode === 'card')
    <button type="button"
            wire:click="add"
            wire:loading.attr="disabled"
            wire:target="add"
            @disabled(!$canAdd)
            aria-label="Agregar {{ $name }} por {{ $priceLbl }}"
            class="relative w-full rounded-2xl bg-white dark:bg-neutral-900 ring-1 ring-slate-200/70 dark:ring-neutral-800
                   p-3 sm:p-4 transition-all duration-200 text-left
                   {{ $canAdd
                      ? 'hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500'
                      : 'opacity-60 cursor-not-allowed' }}">

      <div class="flex items-start gap-3 sm:gap-4">
        {{-- Thumb --}}
        <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl overflow-hidden ring-1 ring-slate-200/70 dark:ring-neutral-700 bg-slate-50 dark:bg-neutral-800 grid place-items-center shrink-0">
          @if($thumb)
            <img src="{{ $thumb }}" alt="Imagen {{ $name }}" class="h-full w-full object-cover">
          @else
            <svg class="w-5 h-5 text-slate-400 dark:text-neutral-300" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <rect x="4" y="6" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
              <path d="M8 12l2.8 2.8L15 10l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          @endif
        </div>

        {{-- Contenido --}}
        <div class="min-w-0 flex-1">
          {{-- Título (SIEMPRE visible; sin truncate) --}}
          <h3 class="text-sm sm:text-[15px] font-medium text-slate-900 dark:text-white leading-snug whitespace-normal break-words">
            {{ $name }}
          </h3>

          {{-- SKU --}}
          <p class="mt-0.5 text-[11px] text-slate-500 dark:text-neutral-400">
            SKU: {{ $sku }}
          </p>

          {{-- Fila inferior: badge + precio (en línea aparte del título) --}}
          <div class="mt-2 flex items-center justify-between">
            <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] ring-1 {{ $badgeRing }} {{ $badgeTxt }}">
              <span class="h-1.5 w-1.5 rounded-full {{ $badgeDot }}"></span>
              <span>{{ $badgeText }}</span>
            </span>

            <div class="shrink-0 text-sm sm:text-base font-semibold text-slate-900 dark:text-white">
              {{ $priceLbl }}
            </div>
          </div>
        </div>
      </div>

      {{-- Overlay de carga --}}
      <div wire:loading wire:target="add"
           class="absolute inset-0 rounded-2xl bg-white/60 dark:bg-neutral-900/40 grid place-items-center">
        <svg class="h-5 w-5 animate-spin text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V2A10 10 0 002 12h2z"/>
        </svg>
      </div>
    </button>

  @elseif($displayMode === 'button')
    {{-- Variante botón --}}
    <button type="button"
            wire:click="add"
            wire:loading.attr="disabled"
            wire:target="add"
            @disabled(!((int)($currentStock ?? 0) > 0 && (bool)($isActive ?? false)))
            class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium
                   focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500
                   {{ ((int)($currentStock ?? 0) > 0 && (bool)($isActive ?? false))
                      ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                      : 'bg-slate-200 text-slate-500 cursor-not-allowed dark:bg-neutral-700 dark:text-neutral-400' }} {{ $buttonClass }}">
      <span wire:loading.remove wire:target="add">{{ $buttonText }}</span>
      <span wire:loading wire:target="add" class="inline-flex items-center gap-2">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V2A10 10 0 002 12h2z"/>
        </svg>
        Agregando…
      </span>
    </button>

  @else
    {{-- Variante compacta --}}
    <button type="button"
            wire:click="addOne"
            wire:loading.attr="disabled"
            wire:target="addOne"
            @disabled(!((int)($currentStock ?? 0) > 0 && (bool)($isActive ?? false)))
            class="inline-flex items-center justify-center rounded-lg px-3 py-1.5 text-sm font-medium
                   focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500
                   {{ ((int)($currentStock ?? 0) > 0 && (bool)($isActive ?? false))
                      ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                      : 'bg-slate-200 text-slate-500 cursor-not-allowed dark:bg-neutral-700 dark:text-neutral-400' }} {{ $buttonClass }}">
      <span wire:loading.remove wire:target="addOne">{{ $buttonText }}</span>
      <span wire:loading wire:target="addOne" class="inline-flex items-center gap-2">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V2A10 10 0 002 12h2z"/>
        </svg>
      </span>
    </button>
  @endif
</div>
