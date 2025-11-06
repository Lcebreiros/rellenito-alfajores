@php
  $userThreshold = (float) (auth()->user()->low_stock_threshold ?? 10);

  $outOfStock = $products->getCollection()->filter(function($p) {
    $stock = (float) ($p->display_stock ?? 0);
    return $stock <= 0;
  })->count();

  $lowStock = $products->getCollection()->filter(function($p) use ($userThreshold) {
    $stock = (float) ($p->display_stock ?? 0);
    return $stock > 0 && $stock <= $userThreshold;
  })->count();

  $stats = [
    [
      'label' => 'Productos',
      'value' => number_format($totals['items']),
      'icon' => 'fa-box',
      'color' => 'indigo'
    ],
    [
      'label' => 'Unidades',
      'value' => number_format($totals['units']),
      'icon' => 'fa-layer-group',
      'color' => 'blue'
    ],
    [
      'label' => 'Valorización',
      'value' => '$ ' . number_format($totals['value'], 2, ',', '.'),
      'icon' => 'fa-sack-dollar',
      'color' => 'emerald'
    ]
  ];
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
  @foreach($stats as $stat)
    <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between">
        <div class="flex-1 min-w-0">
          <p class="text-xs text-gray-500 dark:text-neutral-400 mb-1">{{ $stat['label'] }}</p>
          <p class="text-xl font-bold text-gray-900 dark:text-neutral-100 truncate">{{ $stat['value'] }}</p>
        </div>
        <div class="flex-shrink-0 rounded-lg bg-{{ $stat['color'] }}-50 dark:bg-{{ $stat['color'] }}-500/10 p-2.5 ml-3">
          <i class="fas {{ $stat['icon'] }} text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-300" aria-hidden="true"></i>
        </div>
      </div>
    </div>
  @endforeach

  {{-- Alertas Card --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
      <div class="flex-1 min-w-0">
        <p class="text-xs text-gray-500 dark:text-neutral-400 mb-1">Alertas</p>
        <div class="text-xs sm:text-sm text-gray-700 dark:text-neutral-300 space-y-1">
          <div class="flex items-center">
            <span class="inline-block w-2 h-2 rounded-full bg-rose-500 mr-1.5 flex-shrink-0"></span>
            <span class="truncate">Sin stock: <strong>{{ $outOfStock }}</strong></span>
          </div>
          <div class="flex items-center">
            <span class="inline-block w-2 h-2 rounded-full bg-amber-500 mr-1.5 flex-shrink-0"></span>
            <span class="truncate">Bajo: <strong>{{ $lowStock }}</strong></span>
          </div>
        </div>
      </div>
      <div class="flex-shrink-0 rounded-lg bg-rose-50 dark:bg-rose-500/10 p-2.5 ml-3">
        <i class="fas fa-triangle-exclamation text-rose-600 dark:text-rose-300" aria-hidden="true"></i>
      </div>
    </div>
  </div>
</div>
