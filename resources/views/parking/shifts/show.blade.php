@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
  {{-- Header --}}
  <div class="mb-6">
    <div class="flex items-center justify-between gap-4 flex-wrap">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ route('parking.shifts.audit') }}"
             class="inline-flex items-center text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Volver a Auditoría
          </a>
        </div>
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
          Detalle de Turno #{{ $shift->id }}
        </h1>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
          {{ $shift->employee?->name ?? $shift->operator_name }}
        </p>
      </div>

      <div>
        @if($shift->status === 'open')
          <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
            Turno Abierto
          </span>
        @else
          <span class="inline-flex px-4 py-2 rounded-lg text-sm font-semibold bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
            Turno Cerrado
          </span>
        @endif
      </div>
    </div>
  </div>

  {{-- Información del Turno --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Fechas --}}
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="px-4 sm:px-6 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-900/30">
        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Horarios
        </h2>
      </div>
      <div class="p-4 sm:p-6 space-y-3 text-sm">
        <div>
          <span class="text-neutral-500 dark:text-neutral-400">Apertura:</span>
          <div class="font-semibold text-neutral-900 dark:text-neutral-100 mt-1">
            {{ $shift->started_at->format('d/m/Y H:i') }}
          </div>
        </div>
        @if($shift->ended_at)
          <div>
            <span class="text-neutral-500 dark:text-neutral-400">Cierre:</span>
            <div class="font-semibold text-neutral-900 dark:text-neutral-100 mt-1">
              {{ $shift->ended_at->format('d/m/Y H:i') }}
            </div>
          </div>
          <div>
            <span class="text-neutral-500 dark:text-neutral-400">Duración:</span>
            <div class="font-semibold text-neutral-900 dark:text-neutral-100 mt-1">
              {{ $shift->started_at->diffForHumans($shift->ended_at, true) }}
            </div>
          </div>
        @else
          <div class="text-neutral-500 dark:text-neutral-400">
            Turno en curso...
          </div>
        @endif
      </div>
    </div>

    {{-- Operario --}}
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="px-4 sm:px-6 py-3 bg-purple-50 dark:bg-purple-900/20 border-b border-purple-100 dark:border-purple-900/30">
        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
          <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
          Operario
        </h2>
      </div>
      <div class="p-4 sm:p-6 space-y-3 text-sm">
        <div>
          <span class="text-neutral-500 dark:text-neutral-400">Nombre:</span>
          <div class="font-semibold text-neutral-900 dark:text-neutral-100 mt-1">
            {{ $shift->employee?->name ?? $shift->operator_name }}
          </div>
        </div>
        @if($shift->employee)
          <div>
            <span class="text-neutral-500 dark:text-neutral-400">ID Empleado:</span>
            <div class="font-semibold text-neutral-900 dark:text-neutral-100 mt-1">
              #{{ $shift->employee_id }}
            </div>
          </div>
        @endif
      </div>
    </div>

    {{-- Turno Anterior --}}
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="px-4 sm:px-6 py-3 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-100 dark:border-amber-900/30">
        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
          <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
          </svg>
          Relación
        </h2>
      </div>
      <div class="p-4 sm:p-6 space-y-3 text-sm">
        @if($shift->previousShift)
          <div>
            <span class="text-neutral-500 dark:text-neutral-400">Turno Anterior:</span>
            <div class="font-semibold text-neutral-900 dark:text-neutral-100 mt-1">
              <a href="{{ route('parking.shifts.show', $shift->previousShift) }}"
                 class="text-blue-600 dark:text-blue-400 hover:underline">
                #{{ $shift->previous_shift_id }}
              </a>
            </div>
          </div>
          <div>
            <span class="text-neutral-500 dark:text-neutral-400">Efectivo Heredado:</span>
            <div class="font-semibold text-emerald-600 dark:text-emerald-400 mt-1">
              ${{ number_format($shift->previousShift->remaining_cash ?? 0, 0, ',', '.') }}
            </div>
          </div>
        @else
          <div class="text-neutral-500 dark:text-neutral-400">
            Primer turno registrado
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Arqueo de Caja --}}
  @if($shift->status === 'closed')
    <div class="container-glass shadow-sm overflow-hidden mb-6">
      <div class="px-4 sm:px-6 py-3 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-900/30">
        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
          <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Arqueo de Caja
        </h2>
      </div>
      <div class="p-4 sm:p-6">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Efectivo Inicial</div>
            <div class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
              ${{ number_format($shift->initial_cash, 0, ',', '.') }}
            </div>
          </div>

          <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Cobros en Efectivo</div>
            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
              ${{ number_format($stats['cash_payments'], 0, ',', '.') }}
            </div>
          </div>

          <div class="text-center p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Efectivo Esperado</div>
            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
              ${{ number_format($shift->expected_cash, 0, ',', '.') }}
            </div>
          </div>

          <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Efectivo Contado</div>
            <div class="text-lg font-bold text-purple-600 dark:text-purple-400">
              ${{ number_format($shift->cash_counted, 0, ',', '.') }}
            </div>
          </div>

          <div class="text-center p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">A Buzón</div>
            <div class="text-lg font-bold text-amber-600 dark:text-amber-400">
              ${{ number_format($shift->envelope_amount, 0, ',', '.') }}
            </div>
          </div>

          <div class="text-center p-4 {{ $shift->cash_difference >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-rose-50 dark:bg-rose-900/20' }} rounded-lg">
            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Diferencia</div>
            <div class="text-lg font-bold {{ $shift->cash_difference >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
              @if($shift->cash_difference > 0)+@endif${{ number_format($shift->cash_difference, 0, ',', '.') }}
            </div>
          </div>
        </div>

        @if($shift->remaining_cash > 0)
          <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-900/30">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-200">
                  Efectivo que quedó para el siguiente turno:
                </span>
              </div>
              <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                ${{ number_format($shift->remaining_cash, 0, ',', '.') }}
              </span>
            </div>
          </div>
        @endif

        @if($shift->closing_notes)
          <div class="mt-4 p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">Notas de Cierre:</div>
            <div class="text-sm text-neutral-700 dark:text-neutral-300">
              {{ $shift->closing_notes }}
            </div>
          </div>
        @endif
      </div>
    </div>
  @endif

  {{-- Estadísticas del Turno --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="p-4 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-neutral-600 dark:text-neutral-400">Total Movimientos</div>
            <div class="text-3xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">{{ $stats['total_movements'] }}</div>
          </div>
          <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="container-glass shadow-sm overflow-hidden">
      <div class="p-4 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-neutral-600 dark:text-neutral-400">Autos Actuales</div>
            <div class="text-3xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ $stats['current_cars'] }}</div>
          </div>
          <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center">
            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
      <div class="p-4 bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-900/20 dark:to-purple-900/20">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-neutral-600 dark:text-neutral-400">Pagos MercadoPago</div>
            <div class="text-3xl font-bold text-violet-600 dark:text-violet-400 mt-1">${{ number_format($stats['mp_payments'], 0, ',', '.') }}</div>
          </div>
          <div class="w-12 h-12 rounded-full bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center">
            <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="container-glass shadow-sm overflow-hidden">
      <div class="p-4 bg-gradient-to-br from-rose-50 to-pink-50 dark:from-rose-900/20 dark:to-pink-900/20">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-neutral-600 dark:text-neutral-400">Descuentos Aplicados</div>
            <div class="text-3xl font-bold text-rose-600 dark:text-rose-400 mt-1">${{ number_format($stats['total_discounts'], 0, ',', '.') }}</div>
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

  {{-- Movimientos del Turno --}}
  <div class="container-glass shadow-sm overflow-hidden">
    <div class="px-4 sm:px-6 py-3 bg-neutral-50 dark:bg-neutral-800/50 border-b border-neutral-200 dark:border-neutral-700">
      <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">
        Movimientos Registrados ({{ $shift->stays->count() }})
      </h2>
    </div>

    @if($shift->stays->isEmpty())
      <div class="p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">Sin movimientos</h3>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
          No se registraron movimientos en este turno
        </p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/50">
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Patente</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Vehículo</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Espacio</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Ingreso</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Egreso</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Duración</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Descuento</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Pago</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @foreach($shift->stays as $stay)
              <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition">
                <td class="px-4 py-3 text-sm">
                  <span class="font-mono font-semibold text-neutral-900 dark:text-neutral-100">
                    {{ $stay->license_plate }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                  {{ ucfirst($stay->vehicle_type) }}
                </td>
                <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                  {{ $stay->parkingSpace?->name ?? '-' }}
                </td>
                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $stay->entry_time ? $stay->entry_time->format('d/m H:i') : '-' }}
                </td>
                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $stay->exit_time ? $stay->exit_time->format('d/m H:i') : '-' }}
                </td>
                <td class="px-4 py-3 text-sm text-center">
                  @if($stay->entry_time && $stay->exit_time)
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-xs font-semibold">
                      {{ $stay->entry_time->diffForHumans($stay->exit_time, true) }}
                    </span>
                  @else
                    <span class="text-neutral-400">-</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-center">
                  @if($stay->discount)
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 text-xs font-semibold">
                      {{ $stay->discount->name }}
                    </span>
                  @else
                    <span class="text-neutral-400">-</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-center">
                  @if($stay->paymentMethods->isNotEmpty())
                    @foreach($stay->paymentMethods as $pm)
                      <span class="inline-flex items-center px-2 py-1 rounded-full {{ $pm->payment_method === 'mercadopago' ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' }} text-xs font-semibold">
                        {{ $pm->payment_method === 'mercadopago' ? 'MP' : 'Efectivo' }}
                      </span>
                    @endforeach
                  @else
                    <span class="text-neutral-400">-</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-right font-semibold text-emerald-600 dark:text-emerald-400">
                  ${{ number_format($stay->total_amount, 0, ',', '.') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
