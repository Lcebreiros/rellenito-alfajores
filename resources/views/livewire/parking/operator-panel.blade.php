<div>
  {{-- Gestión de Turno --}}
  @if(!$currentShift)
    {{-- No hay turno abierto --}}
    <div class="container-glass shadow-sm overflow-hidden mb-4 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20">
      <div class="p-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100 mb-2">No hay turno abierto</h3>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
          Debes abrir un turno antes de registrar movimientos
        </p>

        @if($previousShift)
          <div class="inline-block bg-white dark:bg-neutral-800 rounded-lg px-4 py-2 mb-4 text-sm">
            <span class="text-neutral-600 dark:text-neutral-400">Efectivo del turno anterior:</span>
            <span class="ml-2 font-bold text-emerald-600 dark:text-emerald-400">
              ${{ number_format($previousShift->remaining_cash, 2, ',', '.') }}
            </span>
          </div>
        @endif

        <button wire:click="showOpenShiftForm" type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 text-base font-semibold transition shadow-lg">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Abrir Turno
        </button>
      </div>
    </div>
  @else
    {{-- Turno activo --}}
    <div class="container-glass shadow-sm overflow-hidden mb-4">
      <div class="px-4 sm:px-6 py-3 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-900/30">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
              <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
              Turno Activo
            </h2>
            <p class="text-sm text-neutral-700 dark:text-neutral-300 font-medium">
              Operario: {{ $currentShift->operator_name }}
            </p>
            <p class="text-xs text-neutral-600 dark:text-neutral-400">
              Inicio: {{ $currentShift->started_at->format('d/m/Y H:i') }}
            </p>
          </div>
          <button wire:click="showCloseShiftForm" type="button"
                  class="inline-flex items-center gap-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Cerrar Turno
          </button>
        </div>
      </div>

      @if($shiftStats)
        <div class="p-4 sm:p-6">
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            {{-- Movimientos --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-100 dark:border-blue-900/30">
              <div class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase">Movimientos</div>
              <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
                {{ $shiftStats['total_movements'] }}
              </div>
            </div>

            {{-- Autos Actuales --}}
            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3 border border-orange-100 dark:border-orange-900/30">
              <div class="text-xs font-medium text-orange-600 dark:text-orange-400 uppercase">Autos Actuales</div>
              <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
                {{ $shiftStats['current_cars'] }}
              </div>
            </div>

            {{-- Ingresos totales --}}
            <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3 border border-emerald-100 dark:border-emerald-900/30">
              <div class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Total Ingresado</div>
              <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
                ${{ number_format($shiftStats['total_income'], 2, ',', '.') }}
              </div>
            </div>

            {{-- Efectivo en Caja --}}
            <div class="bg-cyan-50 dark:bg-cyan-900/20 rounded-lg p-3 border border-cyan-100 dark:border-cyan-900/30">
              <div class="text-xs font-medium text-cyan-600 dark:text-cyan-400 uppercase">Efectivo en Caja</div>
              <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
                ${{ number_format($shiftStats['total_cash_in_box'], 2, ',', '.') }}
              </div>
            </div>

            {{-- Mercado Pago --}}
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-100 dark:border-purple-900/30">
              <div class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase">Mercado Pago</div>
              <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
                ${{ number_format($shiftStats['mp_payments'], 2, ',', '.') }}
              </div>
            </div>
          </div>

          {{-- Movimientos de Caja --}}
          <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4">
            <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Movimientos de Caja</h4>
            <div class="flex gap-3">
              <button wire:click="openCashMovementModal('ingreso')" type="button"
                      class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 text-sm font-semibold transition shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Ingreso de Caja
              </button>
              <button wire:click="openCashMovementModal('egreso')" type="button"
                      class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 text-sm font-semibold transition shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
                Egreso de Caja
              </button>
            </div>
          </div>
        </div>
      @endif
    </div>
  @endif

  {{-- Campo Scanner Siempre Activo - OCULTO POR AHORA (sin impresora) --}}
  @if(false)
  <div class="container-glass shadow-sm overflow-hidden mb-4">
    <div class="px-4 sm:px-6 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-900/30">
      <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
        </svg>
        Scanner 3nstar
      </h2>
      <p class="text-xs text-neutral-600 dark:text-neutral-400">Campo siempre activo - Escanea ticket para egreso o escribe patente para nuevo ingreso</p>
    </div>
    <div class="p-4 sm:p-6">
      <input
        type="text"
        id="scanner-input"
        wire:model="scannerInput"
        wire:keydown.enter="processScannerInput"
        autocomplete="off"
        autofocus
        class="block w-full rounded-lg border-2 border-blue-300 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 px-4 py-3 text-lg font-mono focus:border-blue-500 focus:ring-blue-500 dark:text-neutral-100"
        placeholder="Escanea el código de barras del ticket o escribe patente...">
      <p class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">
        Al escanear un ticket se abre automáticamente el modal de cobro. Si escribes texto se copia a &quot;Patente&quot; abajo.
      </p>
    </div>
  </div>
  @endif

  {{-- Mensajes Flash --}}
  @if (session()->has('success'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 2000)"
         x-show="show"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('success') }}
    </div>
  @endif

  @if (session()->has('error'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      {{ session('error') }}
    </div>
  @endif

  {{-- Ingreso / Egreso - Input Unificado con dos botones --}}
  <div class="container-glass shadow-sm overflow-hidden mb-4">
    <div class="px-4 sm:px-6 py-3 bg-gradient-to-r from-cyan-50 to-emerald-50 dark:from-cyan-900/20 dark:to-emerald-900/20 border-b border-cyan-100 dark:border-cyan-900/30">
      <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
        <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Ingreso / Egreso
      </h2>
      <p class="text-xs text-neutral-600 dark:text-neutral-400">Escanea o escribe la patente y selecciona la operación</p>
    </div>
    <div class="p-4 sm:p-6">
      <div class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Patente</label>
          <input type="text" wire:model="licensePlate" maxlength="15"
                 class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5 text-base font-mono font-bold uppercase"
                 placeholder="ABC123" autofocus>
          @error('licensePlate')
            <span class="text-red-600 text-xs">{{ $message }}</span>
          @enderror
        </div>
        <div class="w-40">
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Tipo</label>
          <select wire:model="vehicleType"
                  class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5 text-sm">
            <option value="Auto">Auto</option>
            <option value="Camioneta">Camioneta</option>
            <option value="Moto">Moto</option>
            <option value="Camión">Camión</option>
          </select>
          @error('vehicleType')
            <span class="text-red-600 text-xs">{{ $message }}</span>
          @enderror
        </div>
        <div class="flex items-end gap-2">
          <button type="button" wire:click="checkForEntry"
                  class="inline-flex items-center justify-center gap-2 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2.5 text-sm font-semibold transition shadow-md"
                  style="color: white !important; background-color: rgb(8, 145, 178) !important;">
            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 4v16m8-8H4" stroke-linecap="round"/>
            </svg>
            <span class="text-white">Ingreso</span>
          </button>
          <button type="button" wire:click="checkForExit"
                  class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 text-sm font-semibold transition shadow-md"
                  style="color: white !important; background-color: rgb(16, 185, 129) !important;">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
            </svg>
            <span class="text-white">Egreso</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Lista de Movimientos --}}
  @if($currentShift)
  <div class="container-glass shadow-sm overflow-hidden">
    <div class="px-4 sm:px-6 py-3 bg-neutral-50 dark:bg-neutral-800/50 border-b border-neutral-200 dark:border-neutral-700">
      <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Movimientos</h2>
      <p class="text-xs text-neutral-600 dark:text-neutral-400">
        <span class="inline-flex items-center gap-1">
          <span class="w-2 h-2 rounded-full bg-amber-500"></span>
          Pendientes (incluye estadías largas de turnos anteriores)
        </span>
        <span class="mx-2">•</span>
        <span class="inline-flex items-center gap-1">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          Completados de este turno
        </span>
      </p>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-neutral-100 dark:bg-neutral-800 text-xs uppercase">
          <tr>
            <th class="px-4 py-3 text-left text-neutral-700 dark:text-neutral-300 font-semibold">Patente</th>
            <th class="px-4 py-3 text-left text-neutral-700 dark:text-neutral-300 font-semibold">Tipo</th>
            <th class="px-4 py-3 text-left text-neutral-700 dark:text-neutral-300 font-semibold">Ingreso</th>
            <th class="px-4 py-3 text-left text-neutral-700 dark:text-neutral-300 font-semibold">Egreso</th>
            <th class="px-4 py-3 text-left text-neutral-700 dark:text-neutral-300 font-semibold">Total</th>
            <th class="px-4 py-3 text-left text-neutral-700 dark:text-neutral-300 font-semibold">Estado</th>
            <th class="px-4 py-3 text-center text-neutral-700 dark:text-neutral-300 font-semibold">Acción</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
          @forelse($recentMovements as $movement)
            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition">
              <td class="px-4 py-3 text-sm font-mono font-bold text-neutral-900 dark:text-neutral-100">
                {{ $movement->license_plate }}
              </td>
              <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                {{ $movement->vehicle_type ?? 'Auto' }}
              </td>
              <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                {{ $movement->entry_at->format('d/m H:i') }}
              </td>
              <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                {{ $movement->exit_at ? $movement->exit_at->format('d/m H:i') : '-' }}
              </td>
              <td class="px-4 py-3 text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                @if($movement->status === 'closed')
                  ${{ number_format($movement->total_amount, 0, ',', '.') }}
                @else
                  -
                @endif
              </td>
              <td class="px-4 py-3">
                @if($movement->status === 'open')
                  <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200">
                    <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                    Pendiente
                  </span>
                @else
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200">
                    Completado
                  </span>
                @endif
              </td>
              <td class="px-4 py-3 text-center">
                @if($movement->status === 'open')
                  <button wire:click="processExit({{ $movement->id }})" type="button"
                          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                    Egreso
                  </button>
                @else
                  <span class="text-xs text-neutral-500 dark:text-neutral-400">-</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-8 text-center">
                <div class="flex flex-col items-center gap-2">
                  <svg class="w-12 h-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                  </svg>
                  <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Sin movimientos aún</p>
                  <p class="text-xs text-neutral-500 dark:text-neutral-400">Los ingresos que registres aparecerán aquí</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @else
  <div class="container-glass shadow-sm overflow-hidden">
    <div class="px-4 sm:px-6 py-8 text-center">
      <div class="mx-auto w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 mb-2">Abre un turno para comenzar</h3>
      <p class="text-sm text-neutral-600 dark:text-neutral-400">
        Los movimientos se mostrarán aquí una vez que inicies tu turno
      </p>
    </div>
  </div>
  @endif

  {{-- Modal de Egreso/Cobro --}}
  @if($showExitModal && $exitStay)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
      <div class="relative z-10 w-full max-w-2xl">
        <div class="rounded-2xl bg-white shadow-xl ring-1 ring-black/5 dark:bg-neutral-900 dark:ring-white/10">
          {{-- Header --}}
          <div class="flex items-start justify-between gap-3 border-b border-neutral-200 px-5 py-4 dark:border-neutral-800">
            <div>
              <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-50">Cobro de Estacionamiento</h3>
              <p class="text-sm text-neutral-600 dark:text-neutral-400">Patente: <span class="font-semibold">{{ $exitData['license_plate'] ?? '' }}</span></p>
            </div>
            <button type="button" wire:click="cancelExit" class="rounded-lg p-1 text-neutral-500 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800">
              ✕
            </button>
          </div>

          {{-- Body --}}
          <div class="px-5 py-4 space-y-4">
            {{-- Información del vehículo --}}
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-neutral-500 dark:text-neutral-400">Tipo de vehículo:</span>
                <span class="ml-2 font-semibold text-neutral-900 dark:text-neutral-100">{{ $exitData['vehicle_type'] ?? 'N/D' }}</span>
              </div>
              <div>
                <span class="text-neutral-500 dark:text-neutral-400">Cochera:</span>
                <span class="ml-2 font-semibold text-neutral-900 dark:text-neutral-100">{{ $exitData['space_name'] ?? '-' }}</span>
              </div>
              <div>
                <span class="text-neutral-500 dark:text-neutral-400">Entrada:</span>
                <span class="ml-2 text-neutral-700 dark:text-neutral-200">{{ $exitData['entry_at'] ?? '' }}</span>
              </div>
              <div>
                <span class="text-neutral-500 dark:text-neutral-400">Salida:</span>
                <span class="ml-2 text-neutral-700 dark:text-neutral-200">{{ $exitData['exit_at'] ?? '' }}</span>
              </div>
              <div class="col-span-2">
                <span class="text-neutral-500 dark:text-neutral-400">Duración:</span>
                <span class="ml-2 font-semibold text-lg text-cyan-600 dark:text-cyan-400">{{ $exitData['duration_formatted'] ?? '' }}</span>
              </div>
            </div>

            {{-- Bonificación de restaurante --}}
            @if($discounts->count() > 0)
              <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Bonificación de restaurante</label>
                <select wire:model.live="selectedDiscountId"
                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  <option value="">Sin bonificación</option>
                  @foreach($discounts as $discount)
                    <option value="{{ $discount->id }}">
                      {{ $discount->name }}
                      @if($discount->type === 'free_minutes')
                        ({{ $discount->value }} min gratis)
                      @elseif($discount->type === 'percentage')
                        ({{ $discount->value }}% desc.)
                      @elseif($discount->type === 'fixed_amount')
                        (${{ number_format($discount->value, 2) }} desc.)
                      @endif
                    </option>
                  @endforeach
                </select>
              </div>
            @endif

            {{-- Mercado Pago --}}
            <div class="flex items-center">
              <input type="checkbox" id="mp-checkbox" wire:model="useMercadoPago"
                     class="h-4 w-4 rounded border-neutral-300 text-cyan-600 focus:ring-cyan-500">
              <label for="mp-checkbox" class="ml-2 text-sm text-neutral-700 dark:text-neutral-200">
                Pago con Mercado Pago
              </label>
            </div>

            {{-- Desglose de precio --}}
            @if(isset($exitData['breakdown']) && is_array($exitData['breakdown']))
              <div class="border-t border-neutral-200 dark:border-neutral-700 pt-3 space-y-1 text-sm">
                @foreach($exitData['breakdown'] as $item)
                  @if(($item['label'] ?? '') !== 'discount')
                    @php
                      // Traducir labels al español
                      $labelTranslations = [
                        'hour_price' => 'Hora',
                        'half_day_price' => 'Medio día',
                        'day_price' => 'Día',
                        'week_price' => 'Semana',
                        'month_price' => 'Mes',
                        'fraction' => 'Fracción',
                        'initial_block' => 'Bloque inicial',
                      ];
                      $labelKey = $item['label'] ?? '';
                      $translatedLabel = $labelTranslations[$labelKey] ?? ucfirst(str_replace('_', ' ', $labelKey));
                    @endphp
                    <div class="flex justify-between">
                      <span class="text-neutral-600 dark:text-neutral-400">
                        @if(isset($item['qty']) && $item['qty'] > 1)
                          {{ $item['qty'] }}x {{ $translatedLabel }}
                        @else
                          {{ $translatedLabel }}
                        @endif
                        @if(isset($item['minutes']))
                          ({{ $item['minutes'] }} min)
                        @endif
                      </span>
                      <span class="text-neutral-700 dark:text-neutral-200">
                        @if(isset($item['qty']) && $item['qty'] > 1)
                          ${{ number_format(($item['price'] ?? 0) * $item['qty'], 0, ',', '.') }}
                        @else
                          ${{ number_format($item['price'] ?? 0, 0, ',', '.') }}
                        @endif
                      </span>
                    </div>
                  @endif
                @endforeach
                @if(($exitData['discount_amount'] ?? 0) > 0)
                  <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-semibold">
                    <span>Descuento aplicado</span>
                    <span>-${{ number_format($exitData['discount_amount'], 0, ',', '.') }}</span>
                  </div>
                @endif
              </div>
            @endif

            {{-- Total --}}
            <div class="border-t-2 border-neutral-300 dark:border-neutral-700 pt-3">
              <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Total a cobrar:</span>
                <span class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">
                  ${{ number_format($exitData['total'] ?? 0, 0, ',', '.') }}
                </span>
              </div>
            </div>
          </div>

          {{-- Footer --}}
          <div class="flex items-center justify-end gap-3 border-t border-neutral-200 px-5 py-4 dark:border-neutral-800">
            <button type="button" wire:click="cancelExit"
                    class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
              Cancelar
            </button>
            <button type="button" wire:click="confirmExit"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Cobrar y Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- Modal Abrir Turno --}}
  @if($showOpenShiftModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
      <div class="relative z-10 w-full max-w-md">
        <div class="rounded-2xl bg-white shadow-xl ring-1 ring-black/5 dark:bg-neutral-900 dark:ring-white/10">
          {{-- Header --}}
          <div class="flex items-start justify-between gap-3 border-b border-neutral-200 px-5 py-4 dark:border-neutral-800">
            <div>
              <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-50">Abrir Turno</h3>
              <p class="text-sm text-neutral-600 dark:text-neutral-400">Registra el efectivo inicial con el que abres caja</p>
            </div>
            <button type="button" wire:click="cancelOpenShift" class="rounded-lg p-1 text-neutral-500 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800">
              ✕
            </button>
          </div>

          {{-- Body --}}
          <div class="px-5 py-4 space-y-4">
            @if($previousShift)
              <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-100 dark:border-blue-900/30">
                <div class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">Turno Anterior</div>
                <div class="text-xs text-blue-700 dark:text-blue-300">
                  <div>Operador: {{ $previousShift->operator_name }}</div>
                  <div>Cerrado: {{ $previousShift->ended_at ? $previousShift->ended_at->format('d/m/Y H:i') : '-' }}</div>
                  <div class="font-semibold mt-2">Efectivo que quedó en caja: ${{ number_format($previousShift->remaining_cash, 2, ',', '.') }}</div>
                </div>
              </div>
            @endif

            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                Operario *
              </label>
              <select wire:model="employeeId" required
                      class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                <option value="">Selecciona un empleado</option>
                @foreach(($employees ?? collect()) as $emp)
                  <option value="{{ $emp->id }}">{{ trim($emp->first_name.' '.$emp->last_name) ?: $emp->first_name }}</option>
                @endforeach
              </select>
              @error('employeeId')
                <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
              @enderror
              <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                Selecciona el empleado que abrirá el turno
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                Efectivo inicial en caja *
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500 dark:text-neutral-400">$</span>
                <input type="number" wire:model="initialCash" step="0.01" min="0" required
                       class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 pl-8 pr-3 py-2.5 text-lg font-semibold text-right">
              </div>
              @error('initialCash')
                <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
              @enderror
              <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                Ingresa el monto exacto con el que cuentas al abrir la caja
              </p>
            </div>
          </div>

          {{-- Footer --}}
          <div class="flex items-center justify-end gap-3 border-t border-neutral-200 px-5 py-4 dark:border-neutral-800">
            <button type="button" wire:click="cancelOpenShift"
                    class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
              Cancelar
            </button>
            <button type="button" wire:click="openShift"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Confirmar y Abrir
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- Modal Cerrar Turno --}}
  @if($showCloseShiftModal && $currentShift)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
      <div class="relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="rounded-2xl bg-white shadow-xl ring-1 ring-black/5 dark:bg-neutral-900 dark:ring-white/10">
          {{-- Header --}}
          <div class="flex items-start justify-between gap-3 border-b border-neutral-200 px-5 py-4 dark:border-neutral-800">
            <div>
              <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-50">Cerrar Turno</h3>
              <p class="text-sm text-neutral-600 dark:text-neutral-400">Arqueo de caja y cierre del turno</p>
            </div>
            <button type="button" wire:click="cancelCloseShift" class="rounded-lg p-1 text-neutral-500 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800">
              ✕
            </button>
          </div>

          {{-- Body --}}
          <div class="px-5 py-4 space-y-4">
            {{-- Resumen del turno --}}
            @if($shiftStats)
              <div class="bg-neutral-50 dark:bg-neutral-800/50 rounded-lg p-4 space-y-3">
                <h4 class="font-semibold text-neutral-900 dark:text-neutral-100">Resumen del Turno</h4>

                <div class="grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <span class="text-neutral-500 dark:text-neutral-400">Movimientos:</span>
                    <span class="ml-2 font-semibold text-neutral-900 dark:text-neutral-100">{{ $shiftStats['total_movements'] }}</span>
                  </div>
                  <div>
                    <span class="text-neutral-500 dark:text-neutral-400">Duración:</span>
                    <span class="ml-2 font-semibold text-neutral-900 dark:text-neutral-100">{{ number_format($shiftStats['duration_hours'], 1) }}h</span>
                  </div>
                  <div>
                    <span class="text-neutral-500 dark:text-neutral-400">Total ingresado:</span>
                    <span class="ml-2 font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format($shiftStats['total_income'], 2, ',', '.') }}</span>
                  </div>
                  <div>
                    <span class="text-neutral-500 dark:text-neutral-400">Descuentos:</span>
                    <span class="ml-2 font-semibold text-rose-600 dark:text-rose-400">${{ number_format($shiftStats['total_discounts'], 2, ',', '.') }}</span>
                  </div>
                </div>

                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-3 grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <span class="text-neutral-500 dark:text-neutral-400">Cobros en efectivo:</span>
                    <span class="ml-2 font-semibold text-neutral-900 dark:text-neutral-100">${{ number_format($shiftStats['cash_payments'], 2, ',', '.') }}</span>
                  </div>
                  <div>
                    <span class="text-neutral-500 dark:text-neutral-400">Cobros por MP:</span>
                    <span class="ml-2 font-semibold text-neutral-900 dark:text-neutral-100">${{ number_format($shiftStats['mp_payments'], 2, ',', '.') }}</span>
                  </div>
                </div>

                {{-- Movimientos de Caja --}}
                @if($shiftStats['cash_movements_count'] > 0)
                  <div class="border-t border-neutral-200 dark:border-neutral-700 pt-3">
                    <h5 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Movimientos de Caja ({{ $shiftStats['cash_movements_count'] }})</h5>
                    <div class="space-y-2 max-h-32 overflow-y-auto">
                      @foreach($shiftStats['cash_movements'] as $movement)
                        <div class="bg-neutral-100 dark:bg-neutral-700/30 rounded p-2 text-xs">
                          <div class="flex justify-between items-start">
                            <div class="flex-1">
                              <span class="font-semibold {{ $movement['type'] === 'ingreso' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $movement['type'] === 'ingreso' ? '+ Ingreso' : '- Egreso' }}
                              </span>
                              <span class="text-neutral-700 dark:text-neutral-300 ml-1">{{ $movement['description'] }}</span>
                            </div>
                            <span class="font-bold text-neutral-900 dark:text-neutral-100 ml-2">
                              ${{ number_format($movement['amount'], 2, ',', '.') }}
                            </span>
                          </div>
                          @if($movement['notes'])
                            <div class="text-neutral-500 dark:text-neutral-400 mt-1">{{ $movement['notes'] }}</div>
                          @endif
                        </div>
                      @endforeach
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm mt-2">
                      <div>
                        <span class="text-emerald-600 dark:text-emerald-400">Total ingresos:</span>
                        <span class="ml-2 font-semibold text-emerald-700 dark:text-emerald-300">+${{ number_format($shiftStats['cash_ingresos'], 2, ',', '.') }}</span>
                      </div>
                      <div>
                        <span class="text-rose-600 dark:text-rose-400">Total egresos:</span>
                        <span class="ml-2 font-semibold text-rose-700 dark:text-rose-300">-${{ number_format($shiftStats['cash_egresos'], 2, ',', '.') }}</span>
                      </div>
                    </div>
                  </div>
                @endif

                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-100 dark:border-blue-900/30">
                  <div class="text-sm">
                    <span class="text-blue-700 dark:text-blue-300">Efectivo inicial:</span>
                    <span class="ml-2 font-semibold text-blue-900 dark:text-blue-100">${{ number_format($shiftStats['initial_cash'], 2, ',', '.') }}</span>
                  </div>
                  @if($shiftStats['cash_movements_count'] > 0)
                    <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                      + Cobros: ${{ number_format($shiftStats['cash_payments'], 2, ',', '.') }}
                      + Ingresos: ${{ number_format($shiftStats['cash_ingresos'], 2, ',', '.') }}
                      - Egresos: ${{ number_format($shiftStats['cash_egresos'], 2, ',', '.') }}
                    </div>
                  @endif
                  <div class="text-sm mt-1">
                    <span class="text-blue-700 dark:text-blue-300">Efectivo esperado en caja:</span>
                    <span class="ml-2 font-bold text-lg text-blue-900 dark:text-blue-100">${{ number_format($shiftStats['expected_cash'], 2, ',', '.') }}</span>
                  </div>
                </div>
              </div>
            @endif

            {{-- Arqueo de caja --}}
            <div class="space-y-3">
              <h4 class="font-semibold text-neutral-900 dark:text-neutral-100">Arqueo de Caja</h4>

              <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                  Efectivo contado en caja *
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500 dark:text-neutral-400">$</span>
                  <input type="number" wire:model="actualCash" step="0.01" min="0" required
                         class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 pl-8 pr-3 py-2.5 text-lg font-semibold text-right">
                </div>
                @error('actualCash')
                  <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                @enderror
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                  Cuenta todo el efectivo que hay en caja en este momento
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                  Efectivo a depositar en buzón *
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500 dark:text-neutral-400">$</span>
                  <input type="number" wire:model="envelopeAmount" step="0.01" min="0" required
                         class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 pl-8 pr-3 py-2.5 text-lg font-semibold text-right">
                </div>
                @error('envelopeAmount')
                  <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                @enderror
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                  Monto que se depositará en el buzón/caja fuerte
                </p>
              </div>

              @if($actualCash > 0 && $envelopeAmount >= 0)
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3 border border-emerald-100 dark:border-emerald-900/30">
                  <div class="text-sm font-medium text-emerald-900 dark:text-emerald-100">
                    Efectivo que quedará para el próximo turno:
                    <span class="text-lg font-bold ml-2">${{ number_format($actualCash - $envelopeAmount, 2, ',', '.') }}</span>
                  </div>
                </div>
              @endif

              <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                  Notas (opcional)
                </label>
                <textarea wire:model="closeNotes" rows="2"
                          class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                          placeholder="Observaciones del turno..."></textarea>
              </div>
            </div>
          </div>

          {{-- Footer --}}
          <div class="flex items-center justify-end gap-3 border-t border-neutral-200 px-5 py-4 dark:border-neutral-800">
            <button type="button" wire:click="cancelCloseShift"
                    class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
              Cancelar
            </button>
            <button type="button" wire:click="closeShift"
                    class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Cerrar Turno
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- Modal de Warning - Ingreso Duplicado --}}
  @if($showDuplicateWarning && $duplicateStay)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
      <div class="relative z-10 w-full max-w-lg">
        <div class="rounded-2xl bg-white shadow-xl ring-1 ring-black/5 dark:bg-neutral-900 dark:ring-white/10">
          {{-- Header --}}
          <div class="flex items-start justify-between gap-3 border-b border-amber-200 px-5 py-4 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20">
            <div class="flex items-center gap-3">
              <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-amber-900 dark:text-amber-100">Movimiento Duplicado</h3>
                <p class="text-sm text-amber-700 dark:text-amber-300">Ya existe un movimiento abierto con esta patente</p>
              </div>
            </div>
            <button type="button" wire:click="cancelDuplicateWarning" class="rounded-lg p-1 text-neutral-500 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800">
              ✕
            </button>
          </div>

          {{-- Body --}}
          <div class="px-5 py-4 space-y-4">
            <div class="bg-neutral-50 dark:bg-neutral-800/50 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
              <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                  <span class="text-neutral-500 dark:text-neutral-400">Patente:</span>
                  <span class="ml-2 font-bold text-neutral-900 dark:text-neutral-100 font-mono">{{ $duplicateStay->license_plate }}</span>
                </div>
                <div>
                  <span class="text-neutral-500 dark:text-neutral-400">Entrada:</span>
                  <span class="ml-2 font-semibold text-neutral-900 dark:text-neutral-100">{{ $duplicateStay->entry_at->format('d/m H:i') }}</span>
                </div>
                <div class="col-span-2">
                  <span class="text-neutral-500 dark:text-neutral-400">Tiempo transcurrido:</span>
                  <span class="ml-2 font-semibold text-cyan-600 dark:text-cyan-400">
                    {{ $duplicateStay->entry_at->diffForHumans() }}
                  </span>
                </div>
              </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-100 dark:border-blue-900/30">
              <p class="text-sm text-blue-900 dark:text-blue-100 font-medium mb-2">¿Qué deseas hacer?</p>
              <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-disc list-inside">
                <li><strong>Marcar Egreso:</strong> Si el vehículo está saliendo ahora</li>
                <li><strong>Crear Ingreso Duplicado:</strong> Si es un vehículo diferente con la misma patente (patente clonada)</li>
              </ul>
            </div>
          </div>

          {{-- Footer --}}
          <div class="flex items-center justify-end gap-3 border-t border-neutral-200 px-5 py-4 dark:border-neutral-800">
            <button type="button" wire:click="cancelDuplicateWarning"
                    class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
              Cancelar
            </button>
            <button type="button" wire:click="exitDuplicateStay"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
              </svg>
              Marcar Egreso
            </button>
            <button type="button" wire:click="createDuplicateEntry"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 4v16m8-8H4" stroke-linecap="round"/>
              </svg>
              Crear Ingreso Duplicado
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- Modal de Movimiento de Caja --}}
  @if($showCashMovementModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
      <div class="relative z-10 w-full max-w-lg">
        <div class="rounded-2xl bg-white shadow-xl ring-1 ring-black/5 dark:bg-neutral-900 dark:ring-white/10">
          {{-- Header --}}
          <div class="flex items-start justify-between gap-3 border-b border-neutral-200 px-5 py-4 dark:border-neutral-800">
            <div>
              <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-50">
                {{ $cashMovementType === 'ingreso' ? 'Ingreso de Caja' : 'Egreso de Caja' }}
              </h3>
              <p class="text-sm text-neutral-600 dark:text-neutral-400">
                {{ $cashMovementType === 'ingreso' ? 'Registrar dinero que ingresa a la caja' : 'Registrar dinero que sale de la caja' }}
              </p>
            </div>
            <button type="button" wire:click="cancelCashMovement" class="rounded-lg p-1 text-neutral-500 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800">
              ✕
            </button>
          </div>

          {{-- Body --}}
          <div class="px-5 py-4 space-y-4">
            {{-- Monto --}}
            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                Monto *
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500 dark:text-neutral-400">$</span>
                <input type="number" wire:model="cashMovementAmount" step="0.01" min="0.01" required
                       class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 pl-8 pr-3 py-2.5 text-lg font-semibold text-right">
              </div>
              @error('cashMovementAmount')
                <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
              @enderror
            </div>

            {{-- Descripción --}}
            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                Descripción *
              </label>
              <input type="text" wire:model="cashMovementDescription" maxlength="255" required
                     class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                     placeholder="{{ $cashMovementType === 'ingreso' ? 'Ej: Cambio traído por el operador' : 'Ej: Retiro para gastos operativos' }}">
              @error('cashMovementDescription')
                <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
              @enderror
            </div>

            {{-- Notas (opcional) --}}
            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                Notas (opcional)
              </label>
              <textarea wire:model="cashMovementNotes" rows="3" maxlength="500"
                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                        placeholder="Información adicional sobre este movimiento..."></textarea>
              @error('cashMovementNotes')
                <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
              @enderror
            </div>

            {{-- Información adicional --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-100 dark:border-blue-900/30">
              <p class="text-xs text-blue-900 dark:text-blue-100">
                <strong>Importante:</strong> Este movimiento {{ $cashMovementType === 'ingreso' ? 'sumará' : 'restará' }}
                al efectivo esperado en caja y aparecerá en el reporte de cierre del turno.
              </p>
            </div>
          </div>

          {{-- Footer --}}
          <div class="flex items-center justify-end gap-3 border-t border-neutral-200 px-5 py-4 dark:border-neutral-800">
            <button type="button" wire:click="cancelCashMovement"
                    class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
              Cancelar
            </button>
            <button type="button" wire:click="saveCashMovement"
                    class="inline-flex items-center gap-2 rounded-lg {{ $cashMovementType === 'ingreso' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }} px-4 py-2 text-sm font-semibold text-white">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Guardar {{ $cashMovementType === 'ingreso' ? 'Ingreso' : 'Egreso' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- Modal de Selección - Múltiples Movimientos --}}
  @if($showMultipleMovementsModal && count($multipleMovements) > 0)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
      <div class="relative z-10 w-full max-w-2xl">
        <div class="rounded-2xl bg-white shadow-xl ring-1 ring-black/5 dark:bg-neutral-900 dark:ring-white/10">
          {{-- Header --}}
          <div class="flex items-start justify-between gap-3 border-b border-neutral-200 px-5 py-4 dark:border-neutral-800">
            <div>
              <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-50">Múltiples Movimientos Abiertos</h3>
              <p class="text-sm text-neutral-600 dark:text-neutral-400">
                Hay {{ count($multipleMovements) }} movimientos abiertos con la patente <span class="font-bold">{{ $licensePlate }}</span>
              </p>
            </div>
            <button type="button" wire:click="cancelMultipleMovements" class="rounded-lg p-1 text-neutral-500 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800">
              ✕
            </button>
          </div>

          {{-- Body --}}
          <div class="px-5 py-4">
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
              Selecciona cuál movimiento deseas cerrar:
            </p>

            <div class="space-y-3 max-h-96 overflow-y-auto">
              @foreach($multipleMovements as $movement)
                <button type="button" wire:click="selectMovementForExit({{ $movement->id }})"
                        class="w-full text-left p-4 rounded-lg border-2 border-neutral-200 hover:border-cyan-500 hover:bg-cyan-50 dark:border-neutral-700 dark:hover:border-cyan-600 dark:hover:bg-cyan-900/20 transition-all">
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200">
                          <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                          Pendiente
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">
                          Cochera: {{ $movement->parkingSpace?->name ?? 'Sin asignar' }}
                        </span>
                      </div>
                      <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        <div>
                          <span class="text-neutral-500 dark:text-neutral-400">Entrada:</span>
                          <span class="ml-2 font-semibold text-neutral-900 dark:text-neutral-100">
                            {{ $movement->entry_at->format('d/m/Y H:i') }}
                          </span>
                        </div>
                        <div>
                          <span class="text-neutral-500 dark:text-neutral-400">Tiempo:</span>
                          <span class="ml-2 font-semibold text-cyan-600 dark:text-cyan-400">
                            {{ $movement->entry_at->diffForHumans() }}
                          </span>
                        </div>
                        <div>
                          <span class="text-neutral-500 dark:text-neutral-400">Tipo:</span>
                          <span class="ml-2 text-neutral-700 dark:text-neutral-200">
                            {{ $movement->vehicle_type ?? 'Auto' }}
                          </span>
                        </div>
                        @php
                          $tempExit = now();
                          $durationMinutes = $movement->entry_at->diffInMinutes($tempExit);
                          $hours = floor($durationMinutes / 60);
                          $mins = $durationMinutes % 60;
                          $durationFormatted = $mins === 0 ? "{$hours}h" : "{$hours}h {$mins}min";

                          // Calcular monto estimado
                          $tempResult = $movement->calculateTotal($movement->discount);
                          $estimatedAmount = $tempResult['total'] ?? 0;
                        @endphp
                        <div>
                          <span class="text-neutral-500 dark:text-neutral-400">Duración:</span>
                          <span class="ml-2 font-bold text-neutral-900 dark:text-neutral-100">
                            {{ $durationFormatted }}
                          </span>
                        </div>
                      </div>
                      <div class="mt-2 pt-2 border-t border-neutral-200 dark:border-neutral-700">
                        <span class="text-sm text-neutral-600 dark:text-neutral-400">Monto estimado:</span>
                        <span class="ml-2 text-lg font-bold text-emerald-600 dark:text-emerald-400">
                          ${{ number_format($estimatedAmount, 2, ',', '.') }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                      </svg>
                    </div>
                  </div>
                </button>
              @endforeach
            </div>
          </div>

          {{-- Footer --}}
          <div class="flex items-center justify-end gap-3 border-t border-neutral-200 px-5 py-4 dark:border-neutral-800">
            <button type="button" wire:click="cancelMultipleMovements"
                    class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- Script para mantener el focus en el scanner --}}
  <script>
    // Mantener el focus en el campo scanner
    setInterval(() => {
      const scannerInput = document.getElementById('scanner-input');
      if (scannerInput && document.activeElement !== scannerInput) {
        // Solo enfocar si no hay un modal abierto
        const modalOpen = document.querySelector('[style*="background-color: rgba(0,0,0,0.5)"]');
        if (!modalOpen) {
          scannerInput.focus();
        }
      }
    }, 500);
  </script>
</div>
