@extends('layouts.app')

@section('header')
  <div class="flex items-center gap-3">
    <a href="{{ route('expenses.index') }}" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Servicios de Terceros</h1>
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

  <!-- Formulario de nuevo servicio -->
  <div class="mb-6 bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Agregar Servicio de Tercero</h2>

    <form method="POST" action="{{ route('expenses.third-party.store') }}" class="space-y-4">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Nombre del Servicio <span class="text-rose-500">*</span>
          </label>
          <input type="text" name="service_name" required
                 class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                 placeholder="Ej: Hosting web">
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Proveedor
          </label>
          <input type="text" name="provider_name"
                 class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                 placeholder="Ej: Empresa XYZ">
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
            Frecuencia de Pago <span class="text-rose-500">*</span>
          </label>
          <select name="frequency" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
            <option value="mensual">Mensual</option>
            <option value="anual">Anual</option>
            <option value="semanal">Semanal</option>
            <option value="diaria">Diaria</option>
            <option value="unica">Única</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            Próxima Fecha de Pago
          </label>
          <input type="date" name="next_payment_date"
                 class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
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
          Agregar Servicio
        </button>
      </div>
    </form>
  </div>

  <!-- Lista de servicios -->
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
      <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Servicios Registrados</h2>
    </div>

    @if($services->count())
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-neutral-50 dark:bg-neutral-900/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Servicio</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Proveedor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Costo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Frecuencia</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Costo Anual</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Próximo Pago</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Estado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @foreach($services as $service)
              <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
                <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">{{ $service->service_name }}</td>
                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $service->provider_name ?? '-' }}
                </td>
                <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-medium tabular-nums">
                  ${{ number_format($service->cost, 2, ',', '.') }}
                </td>
                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400 capitalize">
                  {{ $service->frequency }}
                </td>
                <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-medium tabular-nums">
                  ${{ number_format($service->annualized_cost, 2, ',', '.') }}
                </td>
                <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $service->next_payment_date ? $service->next_payment_date->format('d/m/Y') : '-' }}
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                               {{ $service->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
                    {{ $service->is_active ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <form method="POST" action="{{ route('expenses.third-party.destroy', $service) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('¿Estás seguro de eliminar este servicio?')"
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
        No hay servicios registrados aún
      </div>
    @endif
  </div>

</div>
@endsection
