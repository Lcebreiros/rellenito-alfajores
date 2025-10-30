@php
  $user = auth()->user();
  $showSelector = ($user && (method_exists($user, 'isCompany') && $user->isCompany()))
                  || ($user && method_exists($user, 'isMaster') && $user->isMaster())
                  || (!empty($availableBranches) && count($availableBranches) >= 1);
@endphp

@if($showSelector)
  <div class="relative">
    <select onchange="window.location.href = this.value"
            aria-label="Seleccionar sucursal"
            class="appearance-none bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-600
                   text-gray-900 dark:text-neutral-100 text-sm rounded-lg
                   focus:ring-indigo-500 focus:border-indigo-500
                   px-4 py-2 pr-10 cursor-pointer">

      @if($user && ((method_exists($user, 'isCompany') && $user->isCompany()) || (method_exists($user, 'isMaster') && $user->isMaster())))
        <option value="{{ route('stock.index', array_merge(request()->query(), ['branch_id' => null])) }}"
                {{ (!$branchId && $isCompanyView) ? 'selected' : '' }}>
          📊 Vista Consolidada{{ $user->isCompany() ? ' (' . $user->name . ')' : '' }}
        </option>
      @endif

      @if($user->isMaster() && !$isCompanyView && !$branchId)
        <option value="{{ route('stock.index', array_merge(request()->query(), ['branch_id' => null])) }}"
                selected>
          🌐 Vista Global
        </option>
      @endif

      @foreach($availableBranches as $branch)
        <option value="{{ route('stock.index', array_merge(request()->query(), ['branch_id' => $branch['id']])) }}"
                {{ $branchId == $branch['id'] ? 'selected' : '' }}>
          🏪 {{ $branch['name'] }}
          @if(isset($branch['company_name']) && $user->isMaster())
            ({{ $branch['company_name'] }})
          @endif
        </option>
      @endforeach

      @if(empty($availableBranches) && ($user && (method_exists($user, 'isCompany') && $user->isCompany())))
        <option disabled>(Sin sucursales)</option>
      @endif
    </select>
    <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" aria-hidden="true"></i>
  </div>
@endif
