@if($supplies->count())
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-neutral-50 dark:bg-neutral-900/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Insumo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Unidad Base</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Stock</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Costo Promedio</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Valor Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
          @foreach($supplies->take(10) as $supply)
            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-medium">
                {{ $supply->name }}
                @if($supply->description)
                  <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ Str::limit($supply->description, 50) }}</p>
                @endif
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                  {{ $supply->base_unit }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400 tabular-nums">
                {{ number_format($supply->stock_base_qty, 2, ',', '.') }} {{ $supply->base_unit }}
              </td>
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-medium tabular-nums">
                ${{ number_format($supply->avg_cost_per_base, 4, ',', '.') }}/{{ $supply->base_unit }}
              </td>
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-semibold tabular-nums">
                ${{ number_format($supply->stock_base_qty * $supply->avg_cost_per_base, 2, ',', '.') }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if($supplies->count() > 10)
      <div class="px-6 py-3 bg-neutral-50 dark:bg-neutral-900/50 border-t border-neutral-200 dark:border-neutral-700">
        <p class="text-sm text-neutral-600 dark:text-neutral-400 text-center">
          Mostrando 10 de {{ $supplies->count() }} insumos.
          <a href="{{ route('expenses.supplies') }}" class="text-amber-600 dark:text-amber-400 hover:underline">Ver todos</a>
        </p>
      </div>
    @endif
  </div>
@else
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 px-6 py-12 text-center">
    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    <h3 class="mt-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">No hay insumos registrados</h3>
    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Comienza creando tu primer insumo.</p>
  </div>
@endif
