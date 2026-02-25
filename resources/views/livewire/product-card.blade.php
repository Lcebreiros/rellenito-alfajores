{{-- resources/views/livewire/product-card.blade.php --}}
<div class="product-card group"> {{-- ÚNICO elemento raíz --}}
  @php
    $canAdd   = (int)($currentStock ?? 0) > 0 && (bool)($isActive ?? false);
    $name     = $product->name ?? 'Producto';
    $sku      = $product->sku  ?? 'N/A';
    $priceNum = (float) ($product->price ?? 0);
    $priceLbl = '$ ' . number_format($priceNum, 2, ',', '.');

    // Imagen del producto (acepta varios campos: image, image_url, photo_path, photo_url)
    $thumb = null;
    try {
      $storage = \Illuminate\Support\Facades\Storage::disk('public');
      $resolve = function ($path) use ($storage) {
        if (!$path) return null;
        $p = (string) $path;
        if (str_starts_with($p, 'http')) return $p;
        $p = ltrim($p, '/');
        $p = preg_replace('#^(public/|storage/)#', '', $p);
        if ($storage->exists($p)) return $storage->url($p);
        return asset('storage/' . $p);
      };

      if (!$thumb && !empty($product->image_url)) $thumb = $resolve($product->image_url);
      if (!$thumb && !empty($product->image))     $thumb = $resolve($product->image);
      if (!$thumb && !empty($product->photo_path))$thumb = $resolve($product->photo_path);
      if (!$thumb && !empty($product->photo_url)) $thumb = $resolve($product->photo_url);
    } catch (\Throwable $e) {
      $thumb = null;
    }

    if(!$isActive){
      $badgeText = 'Inactivo';
      $badgeDot  = 'bg-slate-400';
      $badgeRing = 'border-slate-200/80 dark:border-slate-600';
      $badgeTxt  = 'text-slate-600 dark:text-slate-300';
    } elseif(($currentStock ?? 0) > 0){
      $badgeText = 'Stock: '.(int)$currentStock;
      $badgeDot  = 'bg-emerald-500';
      $badgeRing = 'border-emerald-200/60 dark:border-emerald-800';
      $badgeTxt  = 'text-emerald-700 dark:text-emerald-300';
    } else {
      $badgeText = 'Sin stock';
      $badgeDot  = 'bg-rose-500';
      $badgeRing = 'border-rose-200/60 dark:border-rose-800';
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
                   bg-white dark:bg-neutral-900 ring-1 ring-violet-200/40 dark:ring-neutral-800
                   p-3 md:p-4 transition-all duration-200 text-left overflow-hidden
                   {{ $canAdd
                      ? 'hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500'
                      : 'opacity-60 cursor-not-allowed' }}">

      <div class="flex items-start h-full">
        {{-- Contenido: título, SKU, stock, precio --}}
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
                         px-2 sm:px-2.5 py-[2px] text-[10px] sm:text-[11px] leading-[1.2] border 
                         {{ $badgeRing }} {{ $badgeTxt }} whitespace-nowrap">
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
