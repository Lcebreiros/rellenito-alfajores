@php
  $statusOptions = [
    '' => 'Todos',
    'in' => 'En stock',
    'low' => 'Bajo (≤ mín.)',
    'out' => 'Sin stock'
  ];

  $sortOptions = [
    'name' => 'Nombre',
    'stock' => 'Stock',
    'value' => 'Valorización'
  ];
@endphp

<div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm p-4 mb-6 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10">
  {{-- Filtros de estado y búsqueda --}}
  <div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-3">
    <div class="flex flex-wrap items-center gap-2">
      <span class="text-sm font-medium text-gray-700 dark:text-neutral-300 flex items-center py-2">
        <i class="fas fa-filter text-gray-500 dark:text-neutral-400 mr-2" aria-hidden="true"></i>
        <span>Estado:</span>
      </span>

      @foreach($statusOptions as $key => $label)
        @php $isActive = $currentStatus === $key; @endphp
        <a href="{{ request()->fullUrlWithQuery(['status' => $key ?: null, 'page' => null]) }}"
           class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                  {{ $isActive
                      ? 'bg-indigo-600 text-white shadow-sm'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700' }}"
           aria-current="{{ $isActive ? 'true' : 'false' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>

    {{-- Búsqueda --}}
    <div class="lg:ml-auto w-full lg:w-72">
      <form method="GET" class="flex gap-2" role="search">
        <div class="relative flex-1">
          <label for="search-input" class="sr-only">Buscar productos</label>
          <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-neutral-500 text-sm pointer-events-none" aria-hidden="true"></i>
          <input type="text"
                 id="search-input"
                 name="q"
                 value="{{ $currentQuery }}"
                 placeholder="Buscar nombre / SKU / precio…"
                 class="w-full pl-9 pr-3 py-2 rounded-lg text-sm
                        border-gray-300 focus:border-indigo-500 focus:ring-indigo-500
                        dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-100 dark:placeholder-neutral-400">
        </div>
        <input type="hidden" name="status" value="{{ $currentStatus }}">
        <input type="hidden" name="order_by" value="{{ $currentOrderBy }}">
        <input type="hidden" name="dir" value="{{ $currentDir }}">
        @if($branchId)
          <input type="hidden" name="branch_id" value="{{ $branchId }}">
        @endif
        <button type="submit"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                       bg-indigo-600 text-white hover:bg-indigo-700
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
          Buscar
        </button>
      </form>
    </div>
  </div>

  {{-- Ordenamiento --}}
  <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100 dark:border-neutral-700">
    <span class="text-xs font-medium text-gray-500 dark:text-neutral-400">Ordenar por:</span>

    @foreach($sortOptions as $key => $label)
      @php
        $isActive = $currentOrderBy === $key;
        $newDir = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';
      @endphp
      <a href="{{ request()->fullUrlWithQuery(['order_by' => $key, 'dir' => $newDir, 'page' => null]) }}"
         class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors
                {{ $isActive
                    ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300'
                    : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700' }}"
         aria-current="{{ $isActive ? 'true' : 'false' }}">
        <span>{{ $label }}</span>
        @if($isActive)
          <i class="fas fa-arrow-{{ $currentDir === 'asc' ? 'up' : 'down' }}" aria-hidden="true"></i>
        @endif
      </a>
    @endforeach

    <a href="{{ route('stock.index') }}"
       class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium
              bg-gray-100 text-gray-700 hover:bg-gray-200
              dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700
              transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500">
      <i class="fas fa-eraser" aria-hidden="true"></i>
      <span>Limpiar filtros</span>
    </a>
  </div>
</div>
