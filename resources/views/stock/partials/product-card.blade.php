@php
  $totalStock = (int) ($product->total_stock ?? 0);
  $branchStock = (int) ($product->stock_in_branch ?? 0);
  $displayStock = (int) ($product->display_stock ?? ($branchId ? $branchStock : $totalStock));
  $minStock = (int) ($product->min_stock ?? 0);
  $price = (float) ($product->price ?? 0);
  $value = $price * $displayStock;

  // Obtener umbral configurado por el usuario
  $userThreshold = auth()->user()?->low_stock_threshold ?? 5;

  // Usar el menor entre el mínimo del producto y el umbral del usuario para mostrar alerta
  $alertThreshold = $minStock > 0 ? min($minStock, $userThreshold) : $userThreshold;

  // Calcular porcentaje de stock vs umbral (para barra de progreso)
  $stockPercentage = $alertThreshold > 0 ? min(($displayStock / ($alertThreshold * 2)) * 100, 100) : 100;

  // Determinar estado del badge y colores basado en el umbral del usuario
  if ($displayStock <= 0) {
    $badgeClass = 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 shadow-sm';
    $badgeText = 'Sin stock';
    $badgeIcon = 'fa-circle-xmark';
    $cardGlow = 'group-hover:shadow-rose-100 dark:group-hover:shadow-rose-900/20';
    $stockColor = 'text-rose-600 dark:text-rose-400';
    $progressColor = 'bg-rose-500';
  } elseif ($displayStock <= $userThreshold) {
    // Usar umbral del usuario para determinar "stock bajo"
    $badgeClass = 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 shadow-sm';
    $badgeText = 'Bajo';
    $badgeIcon = 'fa-triangle-exclamation';
    $cardGlow = 'group-hover:shadow-amber-100 dark:group-hover:shadow-amber-900/20';
    $stockColor = 'text-amber-600 dark:text-amber-400';
    $progressColor = 'bg-amber-500';
  } else {
    $badgeClass = 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 shadow-sm';
    $badgeText = 'OK';
    $badgeIcon = 'fa-circle-check';
    $cardGlow = 'group-hover:shadow-emerald-100 dark:group-hover:shadow-emerald-900/20';
    $stockColor = 'text-emerald-600 dark:text-emerald-400';
    $progressColor = 'bg-emerald-500';
  }
@endphp

<article class="group relative rounded-xl border border-gray-200 dark:border-neutral-700
               bg-white dark:bg-neutral-900
               hover:shadow-xl {{ $cardGlow }}
               hover:border-indigo-300 dark:hover:border-indigo-600
               hover:-translate-y-1
               transition-all duration-300 ease-out
               overflow-hidden">

  {{-- Barra superior de estado (decorativa) --}}
  <div class="absolute top-0 left-0 right-0 h-1 {{ $progressColor }} opacity-60"></div>

  <a href="{{ route('stock.show', $product->id) }}"
     class="block p-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-xl relative z-10">

    {{-- Header: Nombre y Badge --}}
    <div class="flex items-start justify-between gap-3 mb-3">
      <div class="min-w-0 flex-1">
        <h3 class="font-bold text-base text-gray-900 dark:text-neutral-100 truncate
                   group-hover:text-indigo-600 dark:group-hover:text-indigo-400
                   transition-colors duration-200 mb-1.5">
          {{ $product->name }}
        </h3>

        <div class="flex items-center gap-2 flex-wrap">
          {{-- SKU con icono --}}
          <span class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-neutral-400">
            <i class="fas fa-barcode text-[10px]" aria-hidden="true"></i>
            <span class="font-mono">{{ $product->sku ?? 'N/A' }}</span>
          </span>

          {{-- Indicador de origen --}}
          @if(isset($product->created_by_type))
            @if($product->created_by_type === 'branch')
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium
                           bg-purple-50 text-purple-700 border border-purple-200
                           dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800">
                <i class="fas fa-store text-[9px]" aria-hidden="true"></i>
                <span>Sucursal</span>
              </span>
            @else
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium
                           bg-blue-50 text-blue-700 border border-blue-200
                           dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800">
                <i class="fas fa-building text-[9px]" aria-hidden="true"></i>
                <span>Empresa</span>
              </span>
            @endif
          @endif
        </div>
      </div>

      <span class="{{ $badgeClass }}" role="status" aria-label="Estado: {{ $badgeText }}">
        <i class="fas {{ $badgeIcon }}" aria-hidden="true"></i>
        <span>{{ $badgeText }}</span>
      </span>
    </div>

    {{-- Stock principal con barra de progreso --}}
    <div class="mb-3 p-3 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-neutral-800/60 dark:to-neutral-800/40 border border-gray-200/50 dark:border-neutral-700/50">
      <div class="flex items-end justify-between mb-2">
        <div>
          <div class="text-xs text-gray-600 dark:text-neutral-400 mb-1 font-medium">
            @if($branchId && $totalStock != $branchStock)
              <i class="fas fa-store text-[10px] mr-1" aria-hidden="true"></i>Stock en Sucursal
            @else
              <i class="fas fa-boxes-stacked text-[10px] mr-1" aria-hidden="true"></i>Stock Disponible
            @endif
          </div>
          <div class="text-2xl font-bold {{ $stockColor }} leading-none">
            {{ number_format($displayStock) }}
          </div>
        </div>

        @if($minStock > 0)
          <div class="text-right">
            <div class="text-[10px] text-gray-500 dark:text-neutral-500 uppercase tracking-wide mb-0.5">Mínimo</div>
            <div class="text-sm font-semibold text-gray-700 dark:text-neutral-300">
              {{ number_format($minStock) }}
            </div>
          </div>
        @endif
      </div>

      {{-- Barra de progreso visual --}}
      @if($minStock > 0)
        <div class="relative w-full h-2 bg-gray-200 dark:bg-neutral-700 rounded-full overflow-hidden">
          <div class="absolute inset-0 {{ $progressColor }} rounded-full transition-all duration-500"
               style="width: {{ $stockPercentage }}%"
               role="progressbar"
               aria-valuenow="{{ $stockPercentage }}"
               aria-valuemin="0"
               aria-valuemax="100">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent to-white/20"></div>
          </div>
        </div>
        <div class="flex justify-between items-center mt-1">
          <span class="text-[10px] text-gray-500 dark:text-neutral-500">
            {{ $displayStock > $minStock ? 'Stock suficiente' : 'Requiere reposición' }}
          </span>
          @if($displayStock > 0 && $minStock > 0)
            <span class="text-[10px] font-medium {{ $stockColor }}">
              {{ number_format($stockPercentage, 0) }}%
            </span>
          @endif
        </div>
      @endif

      {{-- Info adicional de stock total --}}
      @if($branchId && $totalStock != $branchStock)
        <div class="mt-2 pt-2 border-t border-gray-200 dark:border-neutral-700 flex items-center justify-between">
          <span class="text-[11px] text-gray-500 dark:text-neutral-400">
            <i class="fas fa-warehouse text-[9px] mr-1" aria-hidden="true"></i>Stock Total
          </span>
          <span class="text-xs font-semibold text-gray-700 dark:text-neutral-300">
            {{ number_format($totalStock) }}
          </span>
        </div>
      @endif
    </div>

    {{-- Stats Grid: Precio y valorización --}}
    <div class="grid grid-cols-2 gap-2 mb-3">
      {{-- Precio unitario --}}
      <div class="p-2.5 rounded-lg bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30">
        <div class="flex items-center gap-1.5 mb-1">
          <div class="w-6 h-6 rounded-md bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <i class="fas fa-dollar-sign text-[11px] text-blue-600 dark:text-blue-400" aria-hidden="true"></i>
          </div>
          <span class="text-[11px] font-medium text-blue-700 dark:text-blue-300">Precio Unit.</span>
        </div>
        <div class="text-base font-bold text-blue-900 dark:text-blue-100">
          $ {{ number_format($price, 2, ',', '.') }}
        </div>
      </div>

      {{-- Valorización --}}
      <div class="p-2.5 rounded-lg bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30">
        <div class="flex items-center gap-1.5 mb-1">
          <div class="w-6 h-6 rounded-md bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
            <i class="fas fa-sack-dollar text-[11px] text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
          </div>
          <span class="text-[11px] font-medium text-emerald-700 dark:text-emerald-300">Valorización</span>
        </div>
        <div class="text-base font-bold text-emerald-900 dark:text-emerald-100">
          $ {{ number_format($value, 2, ',', '.') }}
        </div>
      </div>
    </div>

    {{-- Footer: Botones de acción --}}
    <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-200 dark:border-neutral-700">
      <div class="flex items-center gap-1 text-indigo-600 dark:text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        <span class="text-xs font-semibold">Ver detalles</span>
        <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i>
      </div>

      @if($displayStock > 0)
        <button type="button"
                onclick="event.stopPropagation(); event.preventDefault(); Livewire.dispatch('openDiscountModal', { productId: {{ $product->id }} })"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold
                       bg-gradient-to-r from-rose-600 to-rose-700
                       hover:from-rose-700 hover:to-rose-800
                       text-white rounded-lg shadow-md hover:shadow-lg
                       focus:ring-2 focus:ring-rose-500 focus:ring-offset-2
                       transform hover:-translate-y-0.5 transition-all">
          <i class="fas fa-minus-circle text-xs"></i>
          <span>Descontar</span>
        </button>
      @endif
    </div>
  </a>

  {{-- Efecto de brillo en hover --}}
  <div class="absolute inset-0 rounded-xl bg-gradient-to-tr from-transparent via-indigo-50/0 to-indigo-100/0
              dark:via-indigo-900/0 dark:to-indigo-800/0
              group-hover:via-indigo-50/30 group-hover:to-indigo-100/20
              dark:group-hover:via-indigo-900/20 dark:group-hover:to-indigo-800/10
              transition-all duration-300 pointer-events-none -z-10"></div>
</article>
