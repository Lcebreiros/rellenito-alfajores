@extends('layouts.app')

@section('header')
  <div class="flex items-center gap-3">
    <a href="{{ route('expenses.index') }}" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Gastos de Servicios</h1>
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

  <!-- Formulario de nuevo gasto -->
  <div class="mb-6 bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Agregar Gasto de Servicio</h2>

    <form method="POST" action="{{ route('expenses.services.store') }}" class="space-y-4">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Servicio (opcional)
          </label>
          <select name="service_id" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
            <option value="">Sin servicio asociado</option>
            @foreach($services as $service)
              <option value="{{ $service->id }}">{{ $service->name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Nombre del Gasto <span class="text-rose-500">*</span>
          </label>
          <input type="text" name="expense_name" required
                 class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                 placeholder="Ej: Material para limpieza">
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Costo <span class="text-rose-500">*</span>
          </label>
          <input type="number" name="cost" required step="0.01" min="0"
                 class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                 placeholder="0.00">
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Tipo de Gasto <span class="text-rose-500">*</span>
          </label>
          <select name="expense_type" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
            <option value="material">Material</option>
            <option value="mano_obra">Mano de obra</option>
            <option value="herramienta">Herramienta</option>
            <option value="otro">Otro</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
          Descripción (opcional)
        </label>
        <textarea name="description" rows="2"
                  class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                  placeholder="Detalles adicionales..."></textarea>
      </div>

      <div class="flex justify-end">
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Agregar Gasto
        </button>
      </div>
    </form>
  </div>

  <!-- Lista de gastos -->
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
      <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Gastos Registrados</h2>
    </div>

    @if($expenses->count())
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-neutral-50 dark:bg-neutral-900/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Gasto</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Servicio</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Tipo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Costo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Estado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @foreach($expenses as $expense)
              <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
                <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">{{ $expense->expense_name }}</td>
                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $expense->service ? $expense->service->name : '-' }}
                </td>
                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400 capitalize">
                  {{ str_replace('_', ' ', $expense->expense_type) }}
                </td>
                <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-medium tabular-nums">
                  ${{ number_format($expense->cost, 2, ',', '.') }}
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                               {{ $expense->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
                    {{ $expense->is_active ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <form method="POST" action="{{ route('expenses.services.destroy', $expense) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('¿Estás seguro de eliminar este gasto?')"
                            class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300">
                      Eliminar
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
        No hay gastos registrados aún
      </div>
    @endif
  </div>

</div>
@endsection
