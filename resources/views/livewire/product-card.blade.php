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
            class="relative w-full h-full min-h-[100px] max-h-[160px] rounded-2xl 
                   bg-white dark:bg-neutral-900 ring-1 ring-slate-200/70 dark:ring-neutral-800
                   p-3 md:p-4 transition-all duration-200 text-left overflow-hidden
                   {{ $canAdd
                      ? 'hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500'
                      : 'opacity-60 cursor-not-allowed' }}">

      <div class="flex items-start gap-2 sm:gap-3 md:gap-4 h-full">
        {{-- Thumb - Responsive sizes --}}
        <div class="h-10 w-10 xs:h-12 xs:w-12 sm:h-14 sm:w-14 md:h-16 md:w-16 
                    rounded-lg md:rounded-xl overflow-hidden ring-1 ring-slate-200/70 dark:ring-neutral-700 
                    bg-slate-50 dark:bg-neutral-800 grid place-items-center flex-none">
          @if($thumb)
            <img src="{{ $thumb }}" alt="Imagen {{ $name }}" class="h-full w-full object-cover">
          @else
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-400 dark:text-neutral-300" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <rect x="4" y="6" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
              <path d="M8 12l2.8 2.8L15 10l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          @endif
        </div>

        {{-- Contenido - TODO en UNA COLUMNA: título, SKU, stock, precio (precio debajo del stock) --}}
        <div class="min-w-0 flex-1 flex flex-col justify-between h-full py-0.5">
          {{-- Sección superior: Título y SKU --}}
          <div class="min-w-0">
            {{-- Título con límite de líneas --}}
            <h3 class="text-xs sm:text-sm md:text-[15px] lg:text-base font-medium 
                       text-slate-900 dark:text-white leading-tight
                       line-clamp-2 overflow-hidden text-ellipsis">
              {{ $name }}
            </h3>

            {{-- SKU con truncate para casos extremos --}}
            <p class="mt-0.5 text-[10px] sm:text-[11px] text-slate-500 dark:text-neutral-400 
                      truncate max-w-full">
              SKU: {{ $sku }}
            </p>
          </div>

          {{-- Sección inferior: BADGE (stock) y PRECIO debajo en la misma columna --}}
          <div class="mt-2 flex flex-col items-start gap-1 min-w-0">
            {{-- Badge responsive (stock/inactivo/sin stock) --}}
            <span class="inline-flex items-center gap-1 sm:gap-1.5 rounded-full 
                         px-1.5 sm:px-2 py-0.5 text-[10px] sm:text-[11px] ring-1 
                         {{ $badgeRing }} {{ $badgeTxt }}">
              <span class="h-1 w-1 sm:h-1.5 sm:w-1.5 rounded-full {{ $badgeDot }}"></span>
              <span class="truncate max-w-[120px]">{{ $badgeText }}</span>
            </span>

            {{-- Precio (siempre debajo del badge, dentro de la misma columna) --}}
            <div class="text-sm font-semibold text-slate-900 dark:text-white mt-1">
              {{ $priceLbl }}
            </div>
          </div>
        </div>
      </div>

      {{-- Overlay de carga --}}
      <div wire:loading wire:target="add"
           class="absolute inset-0 rounded-2xl bg-white/60 dark:bg-neutral-900/40 
                  backdrop-blur-[2px] grid place-items-center z-10">
        <svg class="h-4 w-4 sm:h-5 sm:w-5 animate-spin text-indigo-600 dark:text-indigo-400" 
             viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V2A10 10 0 002 12h2z"/>
        </svg>
      </div>
    </button>

  @elseif($displayMode === 'button')
    {{-- Variante botón - Mejorada responsive --}}
    <button type="button"
            wire:click="add"
            wire:loading.attr="disabled"
            wire:target="add"
            @disabled(!((int)($currentStock ?? 0) > 0 && (bool)($isActive ?? false)))
            class="inline-flex items-center justify-center gap-1.5 sm:gap-2 
                   rounded-lg px-3 sm:px-4 py-1.5 sm:py-2 
                   text-xs sm:text-sm font-medium min-w-0 max-w-full
                   focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500
                   {{ ((int)($currentStock ?? 0) > 0 && (bool)($isActive ?? false))
                      ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                      : 'bg-slate-200 text-slate-500 cursor-not-allowed dark:bg-neutral-700 dark:text-neutral-400' }} {{ $buttonClass }}">
      <span wire:loading.remove wire:target="add" class="truncate">{{ $buttonText }}</span>
      <span wire:loading wire:target="add" class="inline-flex items-center gap-1.5 sm:gap-2 min-w-0">
        <svg class="h-3 w-3 sm:h-4 sm:w-4 animate-spin shrink-0" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V2A10 10 0 002 12h2z"/>
        </svg>
        <span class="truncate">Agregando…</span>
      </span>
    </button>

  @else
    {{-- Variante compacta - Optimizada --}}
    <button type="button"
            wire:click="addOne"
            wire:loading.attr="disabled"
            wire:target="addOne"
            @disabled(!((int)($currentStock ?? 0) > 0 && (bool)($isActive ?? false)))
            class="inline-flex items-center justify-center gap-1 sm:gap-1.5 
                   rounded-lg px-2 sm:px-3 py-1 sm:py-1.5 
                   text-xs sm:text-sm font-medium min-w-0 max-w-full
                   focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500
                   {{ ((int)($currentStock ?? 0) > 0 && (bool)($isActive ?? false))
                      ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                      : 'bg-slate-200 text-slate-500 cursor-not-allowed dark:bg-neutral-700 dark:text-neutral-400' }} {{ $buttonClass }}">
      <span wire:loading.remove wire:target="addOne" class="truncate">{{ $buttonText }}</span>
      <span wire:loading wire:target="addOne" class="inline-flex items-center gap-1 min-w-0">
        <svg class="h-3 w-3 animate-spin shrink-0" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V2A10 10 0 002 12h2z"/>
        </svg>
        <span class="truncate text-[10px] sm:text-xs">Agregando…</span>
      </span>
    </button>
  @endif

  {{-- Estilos adicionales para line-clamp si no los tienes --}}
  <style>
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    /* Breakpoint adicional para pantallas muy pequeñas */
    @media (min-width: 475px) {
      .xs\:h-12 { height: 3rem; }
      .xs\:w-12 { width: 3rem; }
    }
  </style>
</div>
