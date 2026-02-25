@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
  {{-- Header --}}
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Historial de Movimientos</h1>
    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
      Consulta las entradas y salidas de vehículos por día
    </p>
  </div>

  {{-- Filtros de fecha --}}
  <div class="container-glass shadow-sm overflow-hidden mb-6">
    <div class="px-4 sm:px-6 py-3 bg-neutral-50 dark:bg-neutral-800/50 border-b border-neutral-200 dark:border-neutral-700">
      <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Filtrar por período</h2>
    </div>
    <div class="p-4 sm:p-6">
      <form method="GET" action="{{ route('parking.shifts.my-history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Desde</label>
          <input type="date" name="from_date" value="{{ $fromDate }}"
                 class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Hasta</label>
          <input type="date" name="to_date" value="{{ $toDate }}"
                 class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
        </div>

        <div class="flex items-end">
          <button type="submit"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            Filtrar
          </button>
        </div>

        <div class="flex items-end">
          <a href="{{ route('parking.shifts.my-history', ['from_date' => $fromDate, 'to_date' => $toDate, 'download' => '1', 'format' => 'excel']) }}"
             class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Descargar Excel
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- Estadísticas totales --}}
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="p-4 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-neutral-600 dark:text-neutral-400">Días</div>
            <div class="text-3xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">{{ $totalStats['days_count'] }}</div>
          </div>
          <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="container-glass shadow-sm overflow-hidden">
      <div class="p-4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-neutral-600 dark:text-neutral-400">Total Entradas</div>
            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $totalStats['total_entries'] }}</div>
          </div>
          <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
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
            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">${{ number_format($totalStats['total_income'], 0, ',', '.') }}</div>
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
      <div class="p-4 bg-gradient-to-br from-rose-50 to-red-50 dark:from-rose-900/20 dark:to-red-900/20">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-neutral-600 dark:text-neutral-400">Descuentos</div>
            <div class="text-3xl font-bold text-rose-600 dark:text-rose-400 mt-1">${{ number_format($totalStats['total_discounts'], 0, ',', '.') }}</div>
          </div>
          <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center">
            <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Movimientos por día --}}
  @if($movementsByDate->isEmpty())
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">No hay movimientos</h3>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
          No se encontraron movimientos en el período seleccionado
        </p>
      </div>
    </div>
  @else
    @foreach($movementsByDate as $date => $movements)
      @php
        $dateObj = \Carbon\Carbon::parse($date);
        $dayTotal = $movements->sum('total_amount');
        $dayDiscounts = $movements->sum('discount_amount');
        $dayCount = $movements->count();
      @endphp

      <div class="container-glass shadow-sm overflow-hidden mb-4" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
        {{-- Header del día --}}
        <button @click="open = !open" type="button"
                class="w-full px-4 sm:px-6 py-4 bg-gradient-to-r from-neutral-50 to-neutral-100 dark:from-neutral-800/50 dark:to-neutral-800/30 border-b border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800/60 transition">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div class="text-left">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                  {{ $dateObj->format('d/m/Y') }}
                </h3>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $dateObj->locale('es')->isoFormat('dddd') }} - {{ $dayCount }} movimientos
                </p>
              </div>
            </div>

            <div class="flex items-center gap-6">
              <div class="text-right hidden md:block">
                <div class="text-sm text-neutral-600 dark:text-neutral-400">Total del día</div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                  ${{ number_format($dayTotal, 0, ',', '.') }}
                </div>
              </div>

              <svg class="w-5 h-5 text-neutral-400 transition-transform"
                   :class="open ? 'rotate-180' : ''"
                   fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>
        </button>

        {{-- Tabla de movimientos --}}
        <div x-show="open" x-transition class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/30">
                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Patente</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Tipo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Entrada</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Salida</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Duración</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Cochera</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Total</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Desc.</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Final</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
              @foreach($movements as $movement)
                @php
                  $duration = $movement->entry_at->diff($movement->exit_at);
                  $durationFormatted = sprintf('%dh %dm', $duration->h + ($duration->days * 24), $duration->i);
                  $finalAmount = $movement->total_amount - $movement->discount_amount;
                @endphp
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition">
                  <td class="px-4 py-3">
                    <span class="text-sm font-mono font-bold text-neutral-900 dark:text-neutral-100">
                      {{ $movement->license_plate }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                    {{ $movement->vehicle_type ?? 'Auto' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                    {{ $movement->entry_at->format('H:i') }}
                  </td>
                  <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                    {{ $movement->exit_at->format('H:i') }}
                  </td>
                  <td class="px-4 py-3 text-sm font-medium text-cyan-600 dark:text-cyan-400">
                    {{ $durationFormatted }}
                  </td>
                  <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                    {{ $movement->parkingSpace->name ?? '-' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-right font-semibold text-neutral-900 dark:text-neutral-100">
                    ${{ number_format($movement->total_amount, 0, ',', '.') }}
                  </td>
                  <td class="px-4 py-3 text-sm text-right text-rose-600 dark:text-rose-400">
                    @if($movement->discount_amount > 0)
                      ${{ number_format($movement->discount_amount, 0, ',', '.') }}
                    @else
                      -
                    @endif
                  </td>
                  <td class="px-4 py-3 text-sm text-right font-bold text-emerald-600 dark:text-emerald-400">
                    ${{ number_format($finalAmount, 0, ',', '.') }}
                  </td>
                </tr>
              @endforeach

              {{-- Totales del día --}}
              <tr class="bg-neutral-100 dark:bg-neutral-800/50 font-semibold">
                <td colspan="6" class="px-4 py-3 text-sm text-right text-neutral-900 dark:text-neutral-100">
                  Total del día ({{ $dayCount }} movimientos):
                </td>
                <td class="px-4 py-3 text-sm text-right text-neutral-900 dark:text-neutral-100">
                  ${{ number_format($dayTotal, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-sm text-right text-rose-600 dark:text-rose-400">
                  ${{ number_format($dayDiscounts, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-sm text-right font-bold text-emerald-600 dark:text-emerald-400">
                  ${{ number_format($dayTotal - $dayDiscounts, 0, ',', '.') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    @endforeach
  @endif
</div>
@endsection
