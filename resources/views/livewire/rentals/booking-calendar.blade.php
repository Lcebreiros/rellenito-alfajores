<div wire:poll.visible.120s>

  {{-- Notificaciones flash --}}
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-2.5 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif

  {{-- Header del calendario --}}
  <div class="container-glass shadow-sm overflow-hidden mb-4">
    <div class="px-4 sm:px-6 py-3 border-b border-neutral-200 dark:border-neutral-700 flex flex-col sm:flex-row sm:items-center gap-3">
      <div class="flex items-center gap-3 flex-1">
        {{-- Navegación de meses --}}
        <button wire:click="previousMonth" class="p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
          <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100 capitalize min-w-[180px] text-center">
          {{ $currentMonthLabel }}
        </h2>
        <button wire:click="nextMonth" class="p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
          <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
        <button wire:click="goToToday" class="text-xs px-2.5 py-1 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
          Hoy
        </button>
      </div>

      <div class="flex items-center gap-2">
        {{-- Filtro por espacio --}}
        <select wire:model.live="filterSpaceId"
                class="text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-1.5 text-neutral-700 dark:text-neutral-300">
          <option value="">Todos los espacios</option>
          @foreach($spaces as $space)
            <option value="{{ $space->id }}">{{ $space->name }}</option>
          @endforeach
        </select>

        {{-- Ir a lista --}}
        <a href="{{ route('rentals.bookings.index') }}"
           class="text-sm px-3 py-1.5 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
          </svg>
          Lista
        </a>
      </div>
    </div>

    {{-- Leyenda de espacios --}}
    @if($spaces->count())
      <div class="px-4 sm:px-6 py-2 border-b border-neutral-100 dark:border-neutral-800 flex flex-wrap gap-2">
        @foreach($spaces as $space)
          <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded-full text-white font-medium"
                style="background-color: {{ $space->color }};">
            {{ $space->name }}
          </span>
        @endforeach
      </div>
    @endif

    {{-- Grilla del calendario --}}
    <div class="p-3 sm:p-4">
      {{-- Cabecera de días de la semana --}}
      <div class="grid grid-cols-7 mb-1">
        @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dayName)
          <div class="text-center text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 py-1">
            {{ $dayName }}
          </div>
        @endforeach
      </div>

      {{-- Días --}}
      <div class="grid grid-cols-7 gap-0.5">
        @foreach($calendarDays as $day)
          @if($day === null)
            <div class="min-h-[80px] sm:min-h-[100px] rounded-lg"></div>
          @else
            <div
              wire:click="openCreateModal('{{ $day['date'] }}')"
              class="min-h-[80px] sm:min-h-[100px] rounded-lg p-1.5 cursor-pointer transition-colors
                     {{ $day['isToday'] ? 'bg-violet-50 dark:bg-violet-900/20 ring-1 ring-violet-400 dark:ring-violet-600' : 'hover:bg-neutral-50 dark:hover:bg-neutral-800/60' }}
                     {{ $day['isPast'] ? 'opacity-60' : '' }}">

              {{-- Número del día --}}
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold {{ $day['isToday'] ? 'text-violet-700 dark:text-violet-300' : 'text-neutral-600 dark:text-neutral-400' }}">
                  {{ $day['day'] }}
                </span>
                {{-- Indicadores de estado --}}
                <div class="flex gap-0.5">
                  @if($day['hasConfirmed'])
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                  @endif
                  @if($day['hasPending'])
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                  @endif
                </div>
              </div>

              {{-- Reservas del día --}}
              <div class="space-y-0.5 overflow-hidden">
                @foreach($day['bookings']->take(3) as $booking)
                  <div
                    wire:click.stop="openDetailModal({{ $booking->id }})"
                    class="group text-[10px] sm:text-xs px-1.5 py-0.5 rounded text-white truncate cursor-pointer hover:opacity-90 transition-opacity"
                    style="background-color: {{ $booking->space->color ?? '#6366f1' }};"
                    title="{{ $booking->starts_at->format('H:i') }} · {{ $booking->getClientDisplayName() }}">
                    <span class="font-medium">{{ $booking->starts_at->format('H:i') }}</span>
                    <span class="hidden sm:inline"> · {{ Str::limit($booking->getClientDisplayName(), 12) }}</span>
                  </div>
                @endforeach
                @if($day['bookings']->count() > 3)
                  <div class="text-[10px] text-neutral-500 dark:text-neutral-400 px-1">
                    +{{ $day['bookings']->count() - 3 }} más
                  </div>
                @endif
              </div>
            </div>
          @endif
        @endforeach
      </div>
    </div>
  </div>

  {{-- Modal: Crear Reserva --}}
  @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         wire:click.self="$set('showCreateModal', false)">
      <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">

        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
          <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">Nueva reserva — {{ \Carbon\Carbon::parse($createDate)->translatedFormat('d \d\e F') }}</h3>
          <button wire:click="$set('showCreateModal', false)" class="p-1 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800">
            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="p-5 space-y-4">
          @if($createError)
            <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
              {{ $createError }}
            </div>
          @endif

          {{-- Espacio --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Espacio *</label>
            <select wire:model.live="createSpaceId"
                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
              <option value="">Seleccioná un espacio</option>
              @foreach($spaces as $space)
                <option value="{{ $space->id }}">{{ $space->name }}</option>
              @endforeach
            </select>
            @error('createSpaceId') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Fecha y hora de inicio --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Fecha y hora de inicio *</label>
            <input type="datetime-local"
                   wire:model="createStartsAt"
                   class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            @error('createStartsAt') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Duración --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Duración *</label>
            @if($createDurationOptions->count())
              <div class="flex flex-wrap gap-2 mb-2">
                @foreach($createDurationOptions as $option)
                  <button type="button"
                          wire:click="$set('createDurationOptionId', {{ $option->id }})"
                          class="px-3 py-1.5 rounded-lg text-sm border transition-colors
                                 {{ $createDurationOptionId == $option->id
                                    ? 'bg-violet-600 border-violet-600 text-white'
                                    : 'border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:border-violet-400' }}">
                    {{ $option->label }}
                    <span class="text-xs opacity-75">· ${{ number_format($option->price, 0, ',', '.') }}</span>
                  </button>
                @endforeach
              </div>
            @endif
            <div class="flex items-center gap-2">
              <input type="number"
                     wire:model="createDurationMinutes"
                     min="15" max="1440" step="15"
                     class="w-24 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                     placeholder="60">
              <span class="text-sm text-neutral-500">minutos</span>
            </div>
            @error('createDurationMinutes') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Monto --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Monto total</label>
            <div class="relative">
              <span class="absolute left-3 top-2 text-sm text-neutral-500">$</span>
              <input type="number"
                     wire:model="createTotalAmount"
                     min="0" step="0.01"
                     class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 pl-7 pr-3 py-2 text-sm">
            </div>
          </div>

          {{-- Cliente --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Cliente</label>
            <select wire:model.live="createClientId"
                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm mb-2">
              <option value="">Ingresá nombre manual o seleccioná un cliente</option>
              @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->name }}{{ $client->phone ? ' · '.$client->phone : '' }}</option>
              @endforeach
            </select>
            @if(!$createClientId)
              <div class="grid grid-cols-2 gap-2">
                <input type="text"
                       wire:model="createClientName"
                       placeholder="Nombre"
                       class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                <input type="text"
                       wire:model="createClientPhone"
                       placeholder="Teléfono"
                       class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
              </div>
            @endif
          </div>

          {{-- Notas --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Notas</label>
            <textarea wire:model="createNotes"
                      rows="2"
                      class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm resize-none"
                      placeholder="Observaciones..."></textarea>
          </div>
        </div>

        <div class="px-5 py-4 border-t border-neutral-200 dark:border-neutral-700 flex gap-2 justify-end">
          <button wire:click="$set('showCreateModal', false)"
                  class="px-4 py-2 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
            Cancelar
          </button>
          <button wire:click="saveBooking"
                  wire:loading.attr="disabled"
                  wire:target="saveBooking"
                  class="px-4 py-2 text-sm font-medium rounded-lg bg-violet-600 hover:bg-violet-700 text-white transition-colors disabled:opacity-60">
            <span wire:loading.remove wire:target="saveBooking">Guardar reserva</span>
            <span wire:loading wire:target="saveBooking">Guardando...</span>
          </button>
        </div>
      </div>
    </div>
  @endif

  {{-- Modal: Detalle de Reserva --}}
  @if($showDetailModal && $detailBooking)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         wire:click.self="$set('showDetailModal', false)">
      <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl w-full max-w-sm">

        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $detailBooking->space->color ?? '#6366f1' }};"></span>
            <h3 class="font-semibold text-neutral-900 dark:text-neutral-100 text-sm">{{ $detailBooking->space->name }}</h3>
          </div>
          <button wire:click="$set('showDetailModal', false)" class="p-1 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800">
            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="p-5 space-y-3">
          {{-- Estado --}}
          <div class="flex items-center gap-2">
            @php $color = $detailBooking->statusColor() @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                         bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-300">
              {{ $detailBooking->statusLabel() }}
            </span>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">
              {{ $detailBooking->starts_at->translatedFormat('d \d\e F, H:i') }}
              – {{ $detailBooking->ends_at->format('H:i') }}
            </span>
          </div>

          {{-- Cliente --}}
          <div class="text-sm">
            <span class="text-neutral-500 dark:text-neutral-400">Cliente: </span>
            <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $detailBooking->getClientDisplayName() }}</span>
            @if($detailBooking->getClientDisplayPhone())
              <span class="text-neutral-500 dark:text-neutral-400"> · {{ $detailBooking->getClientDisplayPhone() }}</span>
            @endif
          </div>

          {{-- Duración y monto --}}
          <div class="flex items-center justify-between text-sm">
            <span class="text-neutral-500 dark:text-neutral-400">
              {{ $detailBooking->duration_minutes >= 60 ? floor($detailBooking->duration_minutes / 60).'h '.($detailBooking->duration_minutes % 60 ? ($detailBooking->duration_minutes % 60).'min' : '') : $detailBooking->duration_minutes.'min' }}
            </span>
            <span class="font-semibold text-neutral-900 dark:text-neutral-100">
              ${{ number_format($detailBooking->total_amount, 0, ',', '.') }}
            </span>
          </div>

          @if($detailBooking->notes)
            <p class="text-xs text-neutral-500 dark:text-neutral-400 bg-neutral-50 dark:bg-neutral-800 rounded-lg px-3 py-2">
              {{ $detailBooking->notes }}
            </p>
          @endif
        </div>

        <div class="px-5 py-4 border-t border-neutral-200 dark:border-neutral-700 flex flex-wrap gap-2">
          @if($detailBooking->status === 'pending')
            <button wire:click="confirmBooking({{ $detailBooking->id }})"
                    wire:loading.attr="disabled"
                    class="flex-1 px-3 py-2 text-sm font-medium rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors">
              Confirmar
            </button>
          @endif
          @if(in_array($detailBooking->status, ['pending', 'confirmed']))
            <button wire:click="cancelBooking({{ $detailBooking->id }})"
                    wire:confirm="¿Cancelar esta reserva?"
                    class="flex-1 px-3 py-2 text-sm font-medium rounded-lg border border-rose-300 text-rose-700 dark:border-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
              Cancelar
            </button>
          @endif
          <a href="{{ route('rentals.bookings.show', $detailBooking) }}"
             class="px-3 py-2 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
            Ver detalle
          </a>
        </div>
      </div>
    </div>
  @endif

</div>
