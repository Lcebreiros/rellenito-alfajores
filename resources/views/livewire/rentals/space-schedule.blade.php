<div wire:poll.visible.60s>

  {{-- ── HEADER compacto con gradiente violeta ──────────────────────────── --}}
  <div class="relative overflow-hidden rounded-2xl mb-4"
       style="background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 50%, #7c3aed 100%);">
    <div class="absolute inset-0 opacity-10">
      <div class="absolute top-2 right-8 w-24 h-24 rounded-full bg-white/20 blur-2xl"></div>
    </div>
    <div class="relative px-5 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="w-3.5 h-3.5 rounded-full flex-shrink-0 ring-2 ring-white/30"
              style="background-color: {{ $space->color ?? '#6366f1' }};"></span>
        <div>
          <h1 class="text-lg font-bold text-white leading-tight">{{ $space->name }}</h1>
          <div class="flex items-center gap-2 text-violet-200 text-xs mt-0.5">
            @if($space->category)
              <span>{{ $space->category->name }}</span>
              <span>·</span>
            @endif
            <span>Cap. {{ $space->capacity }}</span>
            @if($space->activeDurationOptions->count())
              <span>·</span>
              <span>Base {{ $space->activeDurationOptions->min('minutes') }} min</span>
            @endif
          </div>
        </div>
      </div>
      <a href="{{ route('rentals.spaces.index') }}"
         class="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition-colors flex-shrink-0">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
    </div>
  </div>

  {{-- ── CALENDARIO TRADICIONAL ──────────────────────────────────────────── --}}
  <div class="container-glass shadow-sm overflow-hidden mb-4">

    {{-- Navegación del mes --}}
    <div class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
      <button wire:click="previousMonth"
              class="p-2 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>

      <div class="flex items-center gap-3">
        <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100 capitalize">
          {{ \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}
        </h2>
        <button wire:click="goToToday"
                class="text-xs px-2.5 py-1 rounded-lg bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 hover:bg-violet-200 dark:hover:bg-violet-900/50 font-medium transition-colors">
          Hoy
        </button>
      </div>

      <button wire:click="nextMonth"
              class="p-2 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
    </div>

    {{-- Cabecera días de la semana --}}
    <div class="grid grid-cols-7 border-b border-neutral-100 dark:border-neutral-800">
      @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dayName)
        <div class="text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 py-2.5">
          {{ $dayName }}
        </div>
      @endforeach
    </div>

    {{-- Grilla de días --}}
    <div class="grid grid-cols-7 divide-x divide-y divide-neutral-100 dark:divide-neutral-800">
      @foreach($calendarWeeks as $week)
        @foreach($week as $day)
          @if($day === null)
            <div class="aspect-[1/1] min-h-[44px] bg-neutral-50/50 dark:bg-neutral-800/20"></div>
          @else
            <button wire:click="selectDate('{{ $day['date'] }}')"
                    class="relative flex flex-col items-center justify-center min-h-[44px] py-1.5 transition-colors group
                           {{ $day['isSelected']
                               ? 'bg-violet-600 dark:bg-violet-600'
                               : 'hover:bg-violet-50 dark:hover:bg-violet-900/20' }}">
              {{-- Número del día --}}
              <span class="text-sm font-semibold leading-none
                           {{ $day['isSelected']
                               ? 'text-white'
                               : ($day['isToday']
                                   ? 'text-violet-700 dark:text-violet-300'
                                   : 'text-neutral-800 dark:text-neutral-200') }}">
                {{ $day['dayNum'] }}
              </span>
              {{-- Punto indicator de hoy (cuando no está seleccionado) --}}
              @if($day['isToday'] && !$day['isSelected'])
                <span class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-violet-500"></span>
              @endif
              {{-- Dot de reservas --}}
              @if($day['hasBookings'])
                <span class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full
                             {{ $day['isSelected'] ? 'bg-white/70' : 'bg-amber-400' }}
                             {{ $day['isToday'] && !$day['isSelected'] ? 'translate-y-1' : '' }}"></span>
              @endif
            </button>
          @endif
        @endforeach
      @endforeach
    </div>

    {{-- Leyenda --}}
    <div class="px-5 py-2.5 border-t border-neutral-100 dark:border-neutral-800 flex items-center gap-4 text-xs text-neutral-500 dark:text-neutral-400">
      <span class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
        Día con reservas
      </span>
      <span class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-violet-600 flex-shrink-0"></span>
        Día seleccionado
      </span>
    </div>
  </div>

  {{-- ── FILTRO + FECHA SELECCIONADA ────────────────────────────────────── --}}
  <div class="container-glass shadow-sm mb-4 px-4 py-3 flex items-center justify-between gap-3 flex-wrap">
    <div>
      <p class="text-xs text-neutral-500 dark:text-neutral-400">Horarios para</p>
      <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 capitalize">
        {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d \d\e F') }}
      </p>
    </div>
    <div class="flex items-center gap-2">
      <label class="text-xs text-neutral-500 dark:text-neutral-400">Ver:</label>
      <select wire:model.live="filterStatus"
              class="text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-1.5 text-neutral-700 dark:text-neutral-300">
        <option value="active">Activas</option>
        <option value="all">Todas</option>
        <option value="pending">Solo pendientes</option>
        <option value="confirmed">Solo confirmadas</option>
        <option value="finished">Finalizadas</option>
      </select>
    </div>
  </div>

  {{-- ── TIMELINE ─────────────────────────────────────────────────────────── --}}
  <div class="container-glass shadow-sm overflow-hidden">
    @php $slots = $timeSlots; @endphp

    @if(empty($slots))
      <div class="p-8 text-center text-neutral-500 dark:text-neutral-400 text-sm">
        <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        No hay horarios configurados.<br>
        <a href="{{ route('settings.index') }}" class="text-violet-600 hover:underline text-xs mt-1 inline-block">
          Configurar horario operativo en Ajustes
        </a>
      </div>
    @else
      <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
        @foreach($slots as $slot)
          @php
            $booking    = $slot['booking'];
            $isPast     = $slot['is_past'];
            $hasBooking = $booking !== null;
          @endphp

          @if($hasBooking)
            {{-- Slot con reserva --}}
            <button wire:click="openDetailModal({{ $booking->id }})"
                    class="w-full text-left px-4 py-3 flex items-center gap-3 transition-colors
                           {{ $isPast ? 'opacity-60' : '' }}
                           @if($booking->status === 'pending') bg-amber-50/60 hover:bg-amber-50 dark:bg-amber-900/10 dark:hover:bg-amber-900/20
                           @elseif($booking->status === 'confirmed') bg-emerald-50/60 hover:bg-emerald-50 dark:bg-emerald-900/10 dark:hover:bg-emerald-900/20
                           @else bg-neutral-50 dark:bg-neutral-800/30
                           @endif">
              <div class="text-xs font-mono font-semibold text-neutral-500 dark:text-neutral-400 w-12 flex-shrink-0 text-right">
                {{ $slot['time_label'] }}
              </div>
              <div class="w-0.5 self-stretch rounded-full flex-shrink-0
                          @if($booking->status === 'pending') bg-amber-400
                          @elseif($booking->status === 'confirmed') bg-emerald-400
                          @else bg-neutral-300 dark:bg-neutral-600
                          @endif"></div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                    {{ $booking->getClientDisplayName() ?: 'Sin cliente' }}
                  </span>
                  @if($booking->client_phone)
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">· {{ $booking->client_phone }}</span>
                  @endif
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                  <span class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ $booking->starts_at->format('H:i') }} – {{ $booking->ends_at->format('H:i') }}
                    ({{ $booking->duration_minutes }} min)
                  </span>
                  @if($booking->total_amount > 0)
                    <span class="text-xs text-neutral-600 dark:text-neutral-400">
                      · ${{ number_format($booking->total_amount, 0, ',', '.') }}
                    </span>
                  @endif
                </div>
              </div>
              <div class="flex-shrink-0">
                @if($booking->status === 'pending')
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">Pendiente</span>
                @elseif($booking->status === 'confirmed')
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">Confirmada</span>
                @elseif($booking->status === 'finished')
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-400">Finalizada</span>
                @elseif($booking->status === 'cancelled')
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">Cancelada</span>
                @endif
              </div>
              <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>

          @elseif($isPast)
            {{-- Slot pasado libre --}}
            <div class="px-4 py-3 flex items-center gap-3 opacity-35">
              <div class="text-xs font-mono font-semibold text-neutral-400 w-12 flex-shrink-0 text-right">
                {{ $slot['time_label'] }}
              </div>
              <div class="w-0.5 self-stretch rounded-full flex-shrink-0 bg-neutral-200 dark:bg-neutral-700"></div>
              <div class="flex-1 text-xs text-neutral-400 italic">Libre</div>
            </div>

          @else
            {{-- Slot libre disponible --}}
            <button wire:click="openCreateModal('{{ $slot['datetime'] }}')"
                    class="w-full text-left px-4 py-3 flex items-center gap-3 hover:bg-violet-50/50 dark:hover:bg-violet-900/10 transition-colors group">
              <div class="text-xs font-mono font-semibold text-neutral-500 dark:text-neutral-400 w-12 flex-shrink-0 text-right group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                {{ $slot['time_label'] }}
              </div>
              <div class="w-0.5 self-stretch rounded-full flex-shrink-0 bg-neutral-200 dark:bg-neutral-700 group-hover:bg-violet-400 transition-colors"></div>
              <div class="flex-1 text-xs text-neutral-400 dark:text-neutral-500 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Disponible — click para reservar
              </div>
            </button>
          @endif
        @endforeach
      </div>
    @endif
  </div>

  {{-- ── MODAL: Crear reserva ────────────────────────────────────────────── --}}
  @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-sm"
         wire:click.self="$set('showCreateModal', false)">
      <div class="bg-white dark:bg-neutral-900 rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto">

        <div class="sticky top-0 bg-white dark:bg-neutral-900 px-5 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
          <div>
            <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">Nueva reserva</h3>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
              {{ $space->name }} · {{ \Carbon\Carbon::parse($createStartsAt)->format('H:i') }}
              · {{ \Carbon\Carbon::parse($createStartsAt)->translatedFormat('d/m/Y') }}
            </p>
          </div>
          <button wire:click="$set('showCreateModal', false)"
                  class="p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="p-5 space-y-4">
          @if($createErrorMessage)
            <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
              {{ $createErrorMessage }}
            </div>
          @endif

          {{-- Duración --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">Duración *</label>
            @if($space->activeDurationOptions->count())
              <div class="flex flex-wrap gap-2 mb-3">
                @foreach($space->activeDurationOptions as $option)
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
                     class="w-24 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
              <span class="text-sm text-neutral-500">minutos</span>
            </div>
            @error('createDurationMinutes')
              <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Cliente --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Cliente</label>
            <select wire:model.live="createClientId"
                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm mb-2">
              <option value="">Ingresá nombre o seleccioná cliente registrado</option>
              @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->name }}{{ $client->phone ? ' · '.$client->phone : '' }}</option>
              @endforeach
            </select>
            @if(!$createClientId)
              <div class="grid grid-cols-2 gap-2">
                <input type="text" wire:model="createClientName" placeholder="Nombre"
                       class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                <input type="text" wire:model="createClientPhone" placeholder="Teléfono"
                       class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
              </div>
            @endif
          </div>

          {{-- Notas --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Notas</label>
            <textarea wire:model="createNotes" rows="2" placeholder="Observaciones..."
                      class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm resize-none"></textarea>
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
                  class="px-5 py-2 text-sm font-medium rounded-lg bg-violet-600 hover:bg-violet-700 text-white transition-colors disabled:opacity-60">
            <span wire:loading.remove wire:target="saveBooking">Reservar (pendiente)</span>
            <span wire:loading wire:target="saveBooking">Guardando...</span>
          </button>
        </div>
      </div>
    </div>
  @endif

  {{-- ── MODAL: Detalle / Acciones ───────────────────────────────────────── --}}
  @if($showDetailModal && $detailBooking)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-sm"
         wire:click.self="$set('showDetailModal', false)">
      <div class="bg-white dark:bg-neutral-900 rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-sm">

        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                  style="background-color: {{ $space->color ?? '#6366f1' }};"></span>
            <h3 class="font-semibold text-neutral-900 dark:text-neutral-100 text-sm">{{ $space->name }}</h3>
          </div>
          <button wire:click="$set('showDetailModal', false)"
                  class="p-1 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
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
          </div>

          {{-- Horario --}}
          <div class="flex items-center gap-2 text-sm">
            <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-neutral-700 dark:text-neutral-300">
              {{ $detailBooking->starts_at->format('H:i') }} – {{ $detailBooking->ends_at->format('H:i') }}
              <span class="text-neutral-400 text-xs">({{ $detailBooking->duration_minutes }} min)</span>
            </span>
          </div>

          {{-- Cliente --}}
          @if($detailBooking->getClientDisplayName())
            <div class="flex items-center gap-2 text-sm">
              <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <span class="text-neutral-700 dark:text-neutral-300">{{ $detailBooking->getClientDisplayName() }}</span>
              @if($detailBooking->client_phone)
                <a href="tel:{{ $detailBooking->client_phone }}" class="text-violet-600 text-xs hover:underline">
                  {{ $detailBooking->client_phone }}
                </a>
              @endif
            </div>
          @endif

          {{-- Monto --}}
          @if($detailBooking->total_amount > 0)
            <div class="flex items-center gap-2 text-sm">
              <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="text-neutral-700 dark:text-neutral-300 font-medium">
                ${{ number_format($detailBooking->total_amount, 0, ',', '.') }}
              </span>
            </div>
          @endif

          {{-- Notas --}}
          @if($detailBooking->notes)
            <div class="text-xs text-neutral-500 dark:text-neutral-400 bg-neutral-50 dark:bg-neutral-800 rounded-lg px-3 py-2">
              {{ $detailBooking->notes }}
            </div>
          @endif

          {{-- Enlace detalle --}}
          <a href="{{ route('rentals.bookings.show', $detailBooking) }}"
             class="inline-flex items-center gap-1 text-xs text-violet-600 hover:underline">
            Ver detalle completo
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </a>
        </div>

        {{-- Acciones --}}
        @if(in_array($detailBooking->status, ['pending', 'confirmed']))
          <div class="px-5 pb-5 flex gap-2">
            @if($detailBooking->status === 'pending')
              <button wire:click="confirmBooking({{ $detailBooking->id }})"
                      wire:loading.attr="disabled"
                      wire:target="confirmBooking({{ $detailBooking->id }})"
                      class="flex-1 py-2.5 text-sm font-medium rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors disabled:opacity-60">
                <span wire:loading.remove wire:target="confirmBooking({{ $detailBooking->id }})">Confirmar</span>
                <span wire:loading wire:target="confirmBooking({{ $detailBooking->id }})">Confirmando...</span>
              </button>
            @endif
            <button wire:click="cancelBooking({{ $detailBooking->id }})"
                    wire:loading.attr="disabled"
                    wire:target="cancelBooking({{ $detailBooking->id }})"
                    wire:confirm="¿Cancelar esta reserva?"
                    class="{{ $detailBooking->status === 'confirmed' ? 'flex-1' : '' }} px-4 py-2.5 text-sm font-medium rounded-xl border border-rose-300 text-rose-600 hover:bg-rose-50 dark:border-rose-800 dark:text-rose-400 dark:hover:bg-rose-900/20 transition-colors disabled:opacity-60">
              <span wire:loading.remove wire:target="cancelBooking({{ $detailBooking->id }})">Cancelar reserva</span>
              <span wire:loading wire:target="cancelBooking({{ $detailBooking->id }})">Cancelando...</span>
            </button>
          </div>
        @else
          <div class="px-5 pb-5">
            <button wire:click="$set('showDetailModal', false)"
                    class="w-full py-2.5 text-sm rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
              Cerrar
            </button>
          </div>
        @endif
      </div>
    </div>
  @endif

</div>
