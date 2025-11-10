@php
  $stockQty = (float) ($supply->stock_base_qty ?? 0);
  $avgCost = (float) ($supply->avg_cost_per_base ?? 0);
  $value = $stockQty * $avgCost;

  // Determinar estado del badge y colores basado en stock
  if ($stockQty <= 0) {
    $badgeClass = 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 shadow-sm';
    $badgeText = 'Sin stock';
    $badgeIcon = 'fa-circle-xmark';
    $cardGlow = 'group-hover:shadow-rose-100 dark:group-hover:shadow-rose-900/20';
    $stockColor = 'text-rose-600 dark:text-rose-400';
    $progressColor = 'bg-rose-500';
  } elseif ($stockQty < 10) {
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

  <div class="block p-4">

    {{-- Header: Nombre y Badge --}}
    <div class="flex items-start justify-between gap-3 mb-3">
      <div class="min-w-0 flex-1">
        <h3 class="font-bold text-base text-gray-900 dark:text-neutral-100 truncate
                   group-hover:text-indigo-600 dark:group-hover:text-indigo-400
                   transition-colors duration-200 mb-1.5">
          {{ $supply->name }}
        </h3>

        @if($supply->description)
          <p class="text-xs text-gray-500 dark:text-neutral-400 line-clamp-2">
            {{ $supply->description }}
          </p>
        @endif
      </div>

      <span class="{{ $badgeClass }}" role="status" aria-label="Estado: {{ $badgeText }}">
        <i class="fas {{ $badgeIcon }}" aria-hidden="true"></i>
        <span>{{ $badgeText }}</span>
      </span>
    </div>

    {{-- Stock principal --}}
    <div class="mb-3 p-3 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-neutral-800/60 dark:to-neutral-800/40 border border-gray-200/50 dark:border-neutral-700/50">
      <div class="flex items-end justify-between">
        <div>
          <div class="text-xs text-gray-600 dark:text-neutral-400 mb-1 font-medium">
            <i class="fas fa-boxes-stacked text-[10px] mr-1" aria-hidden="true"></i>Stock Disponible
          </div>
          <div class="text-2xl font-bold {{ $stockColor }} leading-none">
            {{ $supply->formatted_stock }}
            <span class="text-sm font-medium text-gray-600 dark:text-neutral-400 ml-1">{{ $supply->base_unit }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Stats Grid: Costo y valorización --}}
    <div class="grid grid-cols-2 gap-2 mb-3">
      {{-- Costo por unidad base --}}
      <div class="p-2.5 rounded-lg bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30">
        <div class="flex items-center gap-1.5 mb-1">
          <div class="w-6 h-6 rounded-md bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <i class="fas fa-dollar-sign text-[11px] text-blue-600 dark:text-blue-400" aria-hidden="true"></i>
          </div>
          <span class="text-[11px] font-medium text-blue-700 dark:text-blue-300">Costo/{{ $supply->base_unit }}</span>
        </div>
        <div class="text-base font-bold text-blue-900 dark:text-blue-100">
          $ {{ number_format($avgCost, 2, ',', '.') }}
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

    {{-- Footer: Info --}}
    <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-neutral-700">
      <span class="text-xs text-gray-500 dark:text-neutral-400 font-medium">
        <i class="fas fa-warehouse text-[10px] mr-1" aria-hidden="true"></i>
        Insumo
      </span>
      <span class="text-xs text-gray-400 dark:text-neutral-500">
        ID: {{ $supply->id }}
      </span>
    </div>
  </div>

  {{-- Efecto de brillo en hover --}}
  <div class="absolute inset-0 rounded-xl bg-gradient-to-tr from-transparent via-indigo-50/0 to-indigo-100/0
              dark:via-indigo-900/0 dark:to-indigo-800/0
              group-hover:via-indigo-50/30 group-hover:to-indigo-100/20
              dark:group-hover:via-indigo-900/20 dark:group-hover:to-indigo-800/10
              transition-all duration-300 pointer-events-none"></div>
</article>
