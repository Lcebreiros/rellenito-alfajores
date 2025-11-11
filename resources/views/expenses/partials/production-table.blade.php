@if($productionExpenses->count())
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-neutral-50 dark:bg-neutral-900/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Gasto</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Producto</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Costo/Unidad</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Cantidad</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
          @foreach($productionExpenses->take(10) as $expense)
            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-medium">
                {{ $expense->expense_name }}
                @if($expense->description)
                  <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ Str::limit($expense->description, 50) }}</p>
                @endif
              </td>
              <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                {{ $expense->product ? $expense->product->name : '-' }}
              </td>
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-medium tabular-nums">
                ${{ number_format($expense->cost_per_unit, 2, ',', '.') }}
              </td>
              <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                {{ $expense->quantity }} {{ $expense->unit }}
              </td>
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-semibold tabular-nums">
                ${{ number_format($expense->total_cost, 2, ',', '.') }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if($productionExpenses->count() > 10)
      <div class="px-6 py-3 bg-neutral-50 dark:bg-neutral-900/50 border-t border-neutral-200 dark:border-neutral-700">
        <p class="text-sm text-neutral-600 dark:text-neutral-400 text-center">
          Mostrando 10 de {{ $productionExpenses->count() }} gastos.
          <a href="{{ route('expenses.production') }}" class="text-orange-600 dark:text-orange-400 hover:underline">Ver todos</a>
        </p>
      </div>
    @endif
  </div>
@else
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 px-6 py-12 text-center">
    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
    </svg>
    <h3 class="mt-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">No hay gastos de producción</h3>
    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Comienza creando tu primer gasto de producción.</p>
  </div>
@endif
