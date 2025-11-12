@extends('layouts.app')

@section('header')
  <div class="flex items-center gap-3">
    <a href="{{ route('expenses.index') }}" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Insumos</h1>
  </div>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6">

  @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm
                dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm
                dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <!-- Información sobre insumos -->
  <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 p-4">
    <div class="flex items-start gap-3">
      <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div class="text-sm text-blue-800 dark:text-blue-300 flex-1">
        <p class="font-medium mb-1">¿Qué son los insumos?</p>
        <p>Los insumos son materiales como emboltorios, etiquetas, cajas, etc. que se utilizan al vender productos o servicios.
           Cuando realizas una venta, el sistema automáticamente descuenta los insumos del stock. Asígnalos desde las vistas de crear/editar productos o servicios.</p>
      </div>
      <a href="{{ route('suppliers.index') }}"
         class="flex-shrink-0 inline-flex items-center px-3 py-1.5 bg-white dark:bg-neutral-800 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-100 dark:hover:bg-neutral-700 transition text-xs font-medium border border-blue-300 dark:border-blue-700">
        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Gestionar Proveedores
      </a>
    </div>
  </div>

  <!-- Formulario de nuevo insumo (con compra opcional) -->
  <div class="mb-6 bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Agregar Insumo</h2>

    <form method="POST" action="{{ route('expenses.supplies.store') }}" class="space-y-4">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Nombre <span class="text-rose-500">*</span>
          </label>
          <input type="text" name="name" required
                 class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                 placeholder="Ej: Emboltorio de papel">
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Proveedor
          </label>
          <select name="supplier_id" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
            <option value="">Sin proveedor</option>
            @foreach($suppliers as $supplier)
              <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Unidad Base <span class="text-rose-500">*</span>
          </label>
          <select name="base_unit" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
            <option value="u">Unidad (u)</option>
            <option value="g">Gramos (g)</option>
            <option value="ml">Mililitros (ml)</option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Descripción
          </label>
          <textarea name="description" rows="2"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                    placeholder="Descripción opcional del insumo"></textarea>
        </div>
      </div>

      <!-- Compra inicial (opcional) -->
      <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-4">
        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Compra inicial (opcional)</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              Cantidad
            </label>
            <input type="number" step="0.001" min="0" name="qty"
                   class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                   placeholder="Ej: 1000">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              Unidad
            </label>
            <select name="unit" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
              <optgroup label="Masa">
                <option value="g">g</option>
                <option value="kg">kg</option>
              </optgroup>
              <optgroup label="Volumen">
                <option value="ml">ml</option>
                <option value="l">l</option>
                <option value="cm3">cm3</option>
              </optgroup>
              <optgroup label="Unidad">
                <option value="u">u</option>
              </optgroup>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              Precio total ($)
            </label>
            <input type="number" step="0.01" min="0" name="total_cost"
                   class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                   placeholder="Ej: 2500">
          </div>
        </div>
        <p class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">
          Si completas estos campos, se registrará una compra y se calcularán el stock y el costo promedio por unidad base, igual que en la calculadora.
        </p>
      </div>

      <div class="flex justify-end">
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700 transition">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          Agregar Insumo
        </button>
      </div>
    </form>
  </div>

  <!-- Listado de insumos -->
  <div class="rounded-lg border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900 overflow-hidden">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800">
      <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Insumos Registrados</h3>
      <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
        Total: {{ $supplies->count() }} insumos
      </p>
    </div>

    @if($supplies->isEmpty())
      <div class="px-6 py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No hay insumos registrados</h3>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
          Comienza agregando insumos usando el formulario de arriba.
        </p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-800">
          <thead class="bg-neutral-50 dark:bg-neutral-800/50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Nombre
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Proveedor
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Unidad Base
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Stock
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Costo Promedio
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Valor Total
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Estado
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-neutral-900 divide-y divide-neutral-200 dark:divide-neutral-800">
            @foreach($supplies as $supply)
              <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                    {{ $supply->name }}
                  </div>
                  @if($supply->description)
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">
                      {{ Str::limit($supply->description, 40) }}
                    </div>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $supply->supplier?->name ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400">
                  {{ strtoupper($supply->base_unit) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span class="font-medium text-neutral-900 dark:text-neutral-100 tabular-nums">
                    {{ $supply->formatted_stock }}
                  </span>
                  <span class="text-neutral-500 dark:text-neutral-400">
                    {{ strtoupper($supply->base_unit) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400 tabular-nums">
                  ${{ number_format($supply->avg_cost_per_base, 2, ',', '.') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-neutral-100 tabular-nums">
                  ${{ number_format($supply->stock_base_qty * $supply->avg_cost_per_base, 2, ',', '.') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  @if($supply->stock_base_qty > 0)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                      En Stock
                    </span>
                  @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                      Sin Stock
                    </span>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <button onclick="editSupply({{ $supply->id }}, '{{ $supply->name }}', '{{ $supply->base_unit }}', '{{ $supply->description }}', {{ $supply->supplier_id ?? 'null' }})"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                    <form method="POST" action="{{ route('expenses.supplies.destroy', $supply) }}" class="inline"
                          onsubmit="return confirm('¿Eliminar este insumo?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Resumen -->
      <div class="px-6 py-4 bg-neutral-50 dark:bg-neutral-800/50 border-t border-neutral-200 dark:border-neutral-800">
        <div class="flex items-center justify-between text-sm">
          <span class="text-neutral-600 dark:text-neutral-400">
            Valor total en stock:
          </span>
          <span class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
            ${{ number_format($supplies->sum(fn($s) => $s->stock_base_qty * $s->avg_cost_per_base), 2, ',', '.') }}
          </span>
        </div>
      </div>
    @endif
  </div>

</div>

<!-- Modal de edición -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xl max-w-md w-full mx-4">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
      <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Editar Insumo</h3>
    </div>
    <form id="editForm" method="POST">
      @csrf
      @method('PUT')
      <div class="px-6 py-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Nombre <span class="text-rose-500">*</span>
          </label>
          <input type="text" id="edit_name" name="name" required
                 class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
        </div>
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Proveedor
          </label>
          <select id="edit_supplier_id" name="supplier_id" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
            <option value="">Sin proveedor</option>
            @foreach($suppliers as $supplier)
              <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Unidad Base <span class="text-rose-500">*</span>
          </label>
          <select id="edit_base_unit" name="base_unit" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
            <option value="u">Unidad (u)</option>
            <option value="g">Gramos (g)</option>
            <option value="ml">Mililitros (ml)</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Descripción
          </label>
          <textarea id="edit_description" name="description" rows="2"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"></textarea>
        </div>
      </div>
      <div class="px-6 py-4 bg-neutral-50 dark:bg-neutral-800/50 flex justify-end gap-3">
        <button type="button" onclick="closeEditModal()"
                class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-neutral-100">
          Cancelar
        </button>
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-amber-600 text-sm font-medium text-white hover:bg-amber-700">
          Guardar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function editSupply(id, name, base_unit, description, supplier_id) {
  document.getElementById('edit_name').value = name;
  document.getElementById('edit_base_unit').value = base_unit;
  document.getElementById('edit_description').value = description;
  document.getElementById('edit_supplier_id').value = supplier_id || '';
  document.getElementById('editForm').action = `/expenses/supplies/${id}`;
  document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
  document.getElementById('editModal').classList.add('hidden');
}

// Cerrar modal al hacer clic fuera
document.getElementById('editModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeEditModal();
  }
});
</script>
@endsection
