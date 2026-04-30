@if($suppliers->count())
  <div class="space-y-4">
    @foreach($suppliers as $supplier)
      <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        <!-- Header del proveedor -->
        <div class="px-6 py-4 bg-neutral-50 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-700">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                {{ $supplier->name }}
                @if(!$supplier->is_active)
                  <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-200 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300">
                    {{ __('expenses.status_inactive') }}
                  </span>
                @endif
              </h3>
              <div class="flex items-center gap-4 mt-1 text-xs text-neutral-600 dark:text-neutral-400">
                <span>{{ $supplier->supplies_count }} {{ __('expenses.supplies_count_label') }}</span>
                <span>•</span>
                <span>{{ $supplier->expenses_count }} {{ __('expenses.expenses_count_label') }}</span>
                @if($supplier->expenses->count() > 0)
                  <span>•</span>
                  <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                    {{ __('expenses.annual_total_label') }} ${{ number_format($supplier->expenses->sum(fn($e) => $e->annualized_cost), 2, ',', '.') }}
                  </span>
                @endif
              </div>
            </div>
            <a href="{{ route('suppliers.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
              {{ __('expenses.view_all') }}
            </a>
          </div>
        </div>

        <!-- Gastos del proveedor -->
        @if($supplier->expenses->count() > 0)
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                <tr>
                  <th class="px-6 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.col_description') }}</th>
                  <th class="px-6 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.col_product') }}</th>
                  <th class="px-6 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.col_cost') }}</th>
                  <th class="px-6 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.col_qty') }}</th>
                  <th class="px-6 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.col_frequency') }}</th>
                  <th class="px-6 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.col_annual_cost') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach($supplier->expenses->take(5) as $expense)
                  <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30">
                    <td class="px-6 py-3 text-sm text-neutral-900 dark:text-neutral-100">
                      {{ $expense->description ?? '-' }}
                    </td>
                    <td class="px-6 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                      {{ $expense->product ? $expense->product->name : '-' }}
                    </td>
                    <td class="px-6 py-3 text-sm text-neutral-900 dark:text-neutral-100 font-medium tabular-nums">
                      ${{ number_format($expense->cost, 2, ',', '.') }}
                    </td>
                    <td class="px-6 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                      {{ $expense->quantity }} {{ $expense->unit }}
                    </td>
                    <td class="px-6 py-3">
                      <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 capitalize">
                        {{ $expense->frequency }}
                      </span>
                    </td>
                    <td class="px-6 py-3 text-sm text-neutral-900 dark:text-neutral-100 font-semibold tabular-nums">
                      ${{ number_format($expense->annualized_cost, 2, ',', '.') }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @if($supplier->expenses->count() > 5)
            <div class="px-6 py-2 bg-neutral-50 dark:bg-neutral-800/50 border-t border-neutral-200 dark:border-neutral-700 text-center">
              <p class="text-xs text-neutral-600 dark:text-neutral-400">
                {{ __('expenses.showing_of_pre') }} 5 {{ __('expenses.showing_of_mid') }} {{ $supplier->expenses->count() }} {{ __('expenses.expenses_count_label') }}.
                <a href="{{ route('expenses.suppliers') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('expenses.view_all') }}</a>
              </p>
            </div>
          @endif
        @else
          <div class="px-6 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">
            {{ __('expenses.no_supplier_expenses') }}
          </div>
        @endif
      </div>
    @endforeach
  </div>
@else
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 px-6 py-12 text-center">
    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
    </svg>
    <h3 class="mt-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ __('expenses.no_suppliers') }}</h3>
    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
      {{ __('expenses.create_first_supplier_pre') }} <a href="{{ route('suppliers.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('expenses.create_first_supplier') }}</a>.
    </p>
  </div>
@endif
