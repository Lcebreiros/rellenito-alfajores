@if($supplierExpenses->count())
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-neutral-50 dark:bg-neutral-900/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Proveedor</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Producto</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Costo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Cantidad</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Frecuencia</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Costo Anual</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
          @foreach($supplierExpenses->take(10) as $expense)
            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-medium">
                {{ $expense->supplier_name }}
                @if($expense->description)
                  <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ Str::limit($expense->description, 50) }}</p>
                @endif
              </td>
              <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                {{ $expense->product ? $expense->product->name : '-' }}
              </td>
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-medium tabular-nums">
                ${{ number_format($expense->cost, 2, ',', '.') }}
              </td>
              <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                {{ $expense->quantity }} {{ $expense->unit }}
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 capitalize">
                  {{ $expense->frequency }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-semibold tabular-nums">
                ${{ number_format($expense->annualized_cost, 2, ',', '.') }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if($supplierExpenses->count() > 10)
      <div class="px-6 py-3 bg-neutral-50 dark:bg-neutral-900/50 border-t border-neutral-200 dark:border-neutral-700">
        <p class="text-sm text-neutral-600 dark:text-neutral-400 text-center">
          Mostrando 10 de {{ $supplierExpenses->count() }} gastos.
          <a href="{{ route('expenses.suppliers') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Ver todos</a>
        </p>
      </div>
    @endif
  </div>
@else
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 px-6 py-12 text-center">
    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    <h3 class="mt-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">No hay gastos de proveedores</h3>
    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Comienza creando tu primer gasto de proveedor.</p>
  </div>
@endif
