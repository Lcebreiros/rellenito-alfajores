@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
  {{-- Header --}}
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Auditoría de Turnos</h1>
    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
      Panel de control y auditoría de todos los turnos
    </p>
  </div>

  {{-- Estadísticas generales --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="p-4 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-neutral-600 dark:text-neutral-400">Total Turnos</div>
            <div class="text-3xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">{{ $stats['total_shifts'] }}</div>
          </div>
          <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="container-glass shadow-sm overflow-hidden">
      <div class="p-4 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-neutral-600 dark:text-neutral-400">Ingresos Totales</div>
            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">${{ number_format($stats['total_income'], 0, ',', '.') }}</div>
          </div>
          <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="container-glass shadow-sm overflow-hidden">
      <div class="p-4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-neutral-600 dark:text-neutral-400">Movimientos</div>
            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $stats['total_movements'] }}</div>
          </div>
          <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Filtros --}}
  <div class="container-glass shadow-sm overflow-hidden mb-6">
    <div class="px-4 sm:px-6 py-3 bg-neutral-50 dark:bg-neutral-800/50 border-b border-neutral-200 dark:border-neutral-700">
      <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Filtros</h2>
    </div>
    <div class="p-4 sm:p-6">
      <form method="GET" action="{{ route('parking.shifts.audit') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Operario</label>
          <select name="employee_id" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            <option value="">Todos</option>
            @foreach($employees as $emp)
              <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                {{ $emp->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Estado</label>
          <select name="status" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            <option value="">Todos</option>
            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Abiertos</option>
            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Cerrados</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Desde</label>
          <input type="date" name="date_from" value="{{ request('date_from') }}"
                 class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Hasta</label>
          <input type="date" name="date_to" value="{{ request('date_to') }}"
                 class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
        </div>

        <div class="flex items-end gap-2">
          <button type="submit"
                  class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            Filtrar
          </button>
          @if(request()->hasAny(['employee_id', 'status', 'date_from', 'date_to']))
            <a href="{{ route('parking.shifts.audit') }}"
               class="inline-flex items-center justify-center rounded-lg border border-neutral-300 dark:border-neutral-700 px-3 py-2 text-sm font-semibold text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- Lista de turnos --}}
  @if($shifts->isEmpty())
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">No se encontraron turnos</h3>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
          Prueba ajustando los filtros de búsqueda
        </p>
      </div>
    </div>
  @else
    {{-- Tabla responsive --}}
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/50">
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Turno</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Operario</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Inicio</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Cierre</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Movimientos</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Total</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Diferencia</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @foreach($shifts as $shift)
              <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition">
                <td class="px-4 py-3 text-sm">
                  <div class="flex items-center gap-2">
                    @if($shift->status === 'open')
                      <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    @else
                      <div class="w-2 h-2 rounded-full bg-neutral-400"></div>
                    @endif
                    <span class="font-medium text-neutral-900 dark:text-neutral-100">#{{ $shift->id }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                  {{ $shift->employee?->name ?? $shift->operator_name }}
                </td>
                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $shift->started_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $shift->ended_at ? $shift->ended_at->format('d/m/Y H:i') : '-' }}
                </td>
                <td class="px-4 py-3 text-sm text-center">
                  <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-semibold">
                    {{ $shift->total_movements }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-right font-semibold text-emerald-600 dark:text-emerald-400">
                  ${{ number_format($shift->incomes_total, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-sm text-right font-semibold {{ $shift->cash_difference >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                  @if($shift->status === 'closed')
                    @if($shift->cash_difference > 0)+@endif${{ number_format($shift->cash_difference, 0, ',', '.') }}
                  @else
                    -
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-center">
                  <a href="{{ route('parking.shifts.show', $shift) }}"
                     class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Ver
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- Paginación --}}
    @if($shifts->hasPages())
      <div class="mt-6">
        {{ $shifts->links() }}
      </div>
    @endif
  @endif
</div>
@endsection
