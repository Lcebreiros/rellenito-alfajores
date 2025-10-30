<div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 p-4 mb-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100 flex items-center">
      <i class="fas fa-building text-indigo-600 dark:text-indigo-400 mr-2"></i>
      Stock por Sucursal
    </h3>
    <div class="text-sm text-gray-500 dark:text-neutral-400">
      Total: <span class="font-semibold text-gray-900 dark:text-neutral-100">{{ number_format($companyTotal) }}</span> unidades
    </div>
  </div>

  @if(!empty($branchList))
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($branchList as $branch)
        @php
          $branchStock = $branchStocks[$branch['id']] ?? 0;
          $percentage = $companyTotal > 0 ? ($branchStock / $companyTotal) * 100 : 0;
        @endphp
        <a href="{{ route('stock.index', array_merge(request()->query(), ['branch_id' => $branch['id']])) }}"
           class="block p-4 rounded-lg border border-gray-200 dark:border-neutral-700
                  hover:border-indigo-300 dark:hover:border-indigo-600
                  hover:shadow-md transition-all duration-200 group
                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <div class="flex items-center justify-between mb-2">
            <h4 class="font-medium text-gray-900 dark:text-neutral-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
              {{ $branch['name'] }}
            </h4>
            <i class="fas fa-external-link-alt text-xs text-gray-400 group-hover:text-indigo-500 transition-colors" aria-hidden="true"></i>
          </div>

          <div class="flex items-center justify-between text-sm mb-3">
            <span class="text-gray-500 dark:text-neutral-400">Stock:</span>
            <span class="font-semibold text-gray-900 dark:text-neutral-100">{{ number_format($branchStock) }}</span>
          </div>

          <div class="space-y-1">
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-neutral-400">
              <span>Participación</span>
              <span class="font-medium">{{ number_format($percentage, 1) }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
              <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-2 rounded-full transition-all duration-500"
                   style="width: {{ min($percentage, 100) }}%"
                   role="progressbar"
                   aria-valuenow="{{ $percentage }}"
                   aria-valuemin="0"
                   aria-valuemax="100"></div>
            </div>
          </div>
        </a>
      @endforeach
    </div>
  @else
    <div class="text-center py-8">
      <i class="fas fa-store text-gray-300 dark:text-neutral-600 text-3xl mb-3" aria-hidden="true"></i>
      <p class="text-gray-500 dark:text-neutral-400">No hay sucursales registradas</p>
    </div>
  @endif
</div>
