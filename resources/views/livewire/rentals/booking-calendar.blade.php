@push('styles')
<style>
  /* Mobile: flex-col, calendario arriba, turnos abajo */
  .bk-cal-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  .bk-slots-panel    { order: 999; }
  .bk-calendar-panel { order: -1; }

  /* Desktop (≥1024px): flex-row, turnos izquierda (flex-1), calendario derecha (20rem fijo) */
  @media (min-width: 1024px) {
    .bk-cal-wrapper {
      flex-direction: row;
      height: calc(100vh - 9.5rem);
    }
    .bk-slots-panel {
      order: 1;
      flex: 1 1 0%;
      min-width: 0;
    }
    .bk-calendar-panel {
      order: 2;
      width: 23rem;
      flex-shrink: 0;
    }
  }
</style>
@endpush

<div wire:poll.visible.120s>

  {{-- Flash --}}
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-2.5 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif

  {{-- ===== LAYOUT: turnos izquierda + calendario fijo derecha (desktop) / calendario arriba + turnos abajo (mobile) ===== --}}
  <div class="bk-cal-wrapper"
       x-data="{ filter: 'all' }">

    {{-- ════════════════════════════════════
         PANEL IZQUIERDO — Lista de turnos
         ════════════════════════════════════ --}}
    <div class="bk-slots-panel flex flex-col rounded-2xl bg-white dark:bg-neutral-900 shadow-sm border border-neutral-200 dark:border-neutral-800 overflow-hidden">

      {{-- Header fijo --}}
      {{-- Desktop: una sola fila (título | filtros | botón) — igual que en f0f1538 --}}
      {{-- Mobile: dos filas (título+botón arriba / filtros abajo) --}}
      <div class="flex-shrink-0 px-5 py-4 border-b border-neutral-100 dark:border-neutral-800">
        <div class="flex flex-col lg:flex-row lg:flex-wrap lg:items-center gap-2 lg:gap-3">

          @php
            $totalFree     = 0;
            $totalOccupied = 0;
            foreach ($daySlots as $sd) {
                foreach ($sd['slots'] as $s) {
                    if ($s['booking'])      $totalOccupied++;
                    elseif (!$s['is_past']) $totalFree++;
                }
            }
          @endphp

          {{-- Título + botón compacto (visible en mobile junto al título) --}}
          <div class="flex items-center gap-2 flex-1 min-w-0">
            <div class="flex-1 min-w-0">
              <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100 capitalize whitespace-nowrap overflow-hidden text-ellipsis">
                {{ $selectedDateLabel }}
              </h2>
              <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 whitespace-nowrap">
                <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ $totalFree }} libre{{ $totalFree !== 1 ? 's' : '' }}</span>
                @if($totalOccupied)
                  <span class="mx-1 text-neutral-300 dark:text-neutral-600">·</span>
                  <span class="text-violet-600 dark:text-violet-400 font-medium">{{ $totalOccupied }} ocupado{{ $totalOccupied !== 1 ? 's' : '' }}</span>
                @endif
              </p>
            </div>
            {{-- Botón compacto solo en mobile --}}
            <button wire:click="openCreateModal('{{ $selectedDate }}')"
                    class="lg:hidden flex-shrink-0 inline-flex items-center gap-1 text-xs px-3 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white font-medium transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Nueva
            </button>
          </div>

          {{-- Filtro de espacio --}}
          @if($spaces->count() > 1)
          <div class="lg:flex-shrink-0">
            <select wire:model.live="filterSpaceId"
                    class="w-full lg:w-auto text-xs rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-violet-400">
              <option value="">Todos los espacios</option>
              @foreach($spaces as $sp)
                <option value="{{ $sp->id }}">{{ $sp->name }}</option>
              @endforeach
            </select>
          </div>
          @endif

          {{-- Filtros: Todos/Libres/Ocupados --}}
          <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 rounded-lg p-1 gap-0.5 lg:flex-shrink-0">
            <button @click="filter = 'all'"
                    :class="filter === 'all' ? 'bg-white dark:bg-neutral-700 shadow-sm text-neutral-900 dark:text-neutral-100' : 'text-neutral-500 dark:text-neutral-400'"
                    class="flex-1 lg:flex-none text-xs px-3 py-1.5 rounded-md font-medium transition-all">Todos</button>
            <button @click="filter = 'free'"
                    :class="filter === 'free' ? 'bg-white dark:bg-neutral-700 shadow-sm text-emerald-700 dark:text-emerald-400' : 'text-neutral-500 dark:text-neutral-400'"
                    class="flex-1 lg:flex-none text-xs px-3 py-1.5 rounded-md font-medium transition-all">Libres</button>
            <button @click="filter = 'occupied'"
                    :class="filter === 'occupied' ? 'bg-white dark:bg-neutral-700 shadow-sm text-violet-700 dark:text-violet-400' : 'text-neutral-500 dark:text-neutral-400'"
                    class="flex-1 lg:flex-none text-xs px-3 py-1.5 rounded-md font-medium transition-all">Ocupados</button>
          </div>

          {{-- Botón completo solo en desktop --}}
          <button wire:click="openCreateModal('{{ $selectedDate }}')"
                  class="hidden lg:inline-flex flex-shrink-0 items-center gap-1.5 text-xs px-3 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white font-medium transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva reserva
          </button>

        </div>
      </div>

      {{-- Slots — scrolleable --}}
      @if($spaces->isEmpty())
        <div class="flex-1 flex flex-col items-center justify-center px-6 py-16 text-center">
          <svg class="w-10 h-10 text-neutral-300 dark:text-neutral-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
          </svg>
          <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">No hay espacios configurados</p>
          <a href="{{ route('rentals.spaces.index') }}" class="text-sm text-violet-600 dark:text-violet-400 hover:underline mt-2">Crear un espacio →</a>
        </div>
      @else
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3">
          @foreach($daySlots as $spaceData)
            @php
              $space = $spaceData['space'];
              $slots = $spaceData['slots'];
            @endphp

            {{-- Separador de espacio --}}
            <div class="flex items-center gap-2 pt-1 pb-0.5 sticky top-0 bg-white dark:bg-neutral-900 z-10 py-2">
              <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $space->color }};"></span>
              <span class="text-xs font-bold uppercase tracking-wider" style="color: {{ $space->color }};">{{ $space->name }}</span>
              @php $spaceFree = collect($slots)->filter(fn($s) => !$s['booking'] && !$s['is_past'])->count(); @endphp
              @if($spaceFree)
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full ml-1 text-white"
                      style="background-color: {{ $space->color }}88;">{{ $spaceFree }} libres</span>
              @endif
            </div>

            @if(empty($slots))
              <p class="text-xs text-neutral-400 pl-4">Configurá opciones de duración para este espacio.</p>
            @else
              <div class="space-y-2">
                @foreach($slots as $slot)
                  @php
                    $isFree     = !$slot['booking'] && !$slot['is_past'];
                    $isOccupied = !empty($slot['booking']);
                    $isPastFree = $slot['is_past'] && !$slot['booking'];
                    $b          = $slot['booking'] ?? null;

                    if ($isFree) {
                        $cardBg     = 'bg-white dark:bg-neutral-800 hover:bg-emerald-50 dark:hover:bg-emerald-900/10 cursor-pointer';
                        $leftBorder = 'border-l-emerald-400';
                        $cardBorder = 'border-neutral-100 dark:border-neutral-700/60';
                    } elseif ($isOccupied && $b->status === 'pending') {
                        $cardBg     = 'bg-white dark:bg-neutral-800';
                        $leftBorder = 'border-l-amber-400';
                        $cardBorder = 'border-neutral-100 dark:border-neutral-700/60';
                    } elseif ($isOccupied && $b->status === 'confirmed') {
                        $cardBg     = 'bg-white dark:bg-neutral-800';
                        $leftBorder = 'border-l-violet-500';
                        $cardBorder = 'border-neutral-100 dark:border-neutral-700/60';
                    } else {
                        $cardBg     = 'bg-neutral-50 dark:bg-neutral-800/40 opacity-45';
                        $leftBorder = 'border-l-neutral-300 dark:border-l-neutral-600';
                        $cardBorder = 'border-neutral-100 dark:border-neutral-700/30';
                    }
                  @endphp

                  <div x-show="filter === 'all'
                            || (filter === 'free'     && {{ $isFree ? 'true' : 'false' }})
                            || (filter === 'occupied' && {{ $isOccupied ? 'true' : 'false' }})"
                       class="rounded-xl border border-l-4 shadow-sm transition-colors flex items-center gap-3 px-4 py-3
                              {{ $cardBg }} {{ $leftBorder }} {{ $cardBorder }}"
                       @if($isFree)
                         wire:click="openCreateModalFromSlot('{{ $selectedDate }}', '{{ $slot['time'] }}', {{ $space->id }})"
                       @endif>

                    {{-- Hora --}}
                    <span class="text-sm font-bold tabular-nums flex-shrink-0 w-12
                                 {{ $isFree ? 'text-emerald-600 dark:text-emerald-400' : ($isPastFree ? 'text-neutral-400' : 'text-neutral-700 dark:text-neutral-300') }}">
                      {{ $slot['time'] }}
                    </span>

                    {{-- Contenido --}}
                    @if($isOccupied)
                      <div class="flex-1 min-w-0 flex items-center gap-2">
                        <div class="min-w-0 flex-1">
                          <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 truncate leading-tight">
                            {{ $b->getClientDisplayName() }}
                          </p>
                          @if($b->getClientDisplayPhone())
                            <p class="text-xs text-neutral-400 leading-tight">{{ $b->getClientDisplayPhone() }}</p>
                          @endif
                        </div>
                        <span class="flex-shrink-0 text-xs font-medium px-2 py-0.5 rounded-full
                                     {{ $b->status === 'pending'
                                         ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                                         : 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400' }}">
                          {{ $b->statusLabel() }}
                        </span>
                      </div>
                      <button wire:click="openDetailModal({{ $b->id }})"
                              class="flex-shrink-0 text-xs px-2.5 py-1 rounded-lg border border-neutral-200 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                        Ver
                      </button>

                    @elseif($isPastFree)
                      <span class="text-xs text-neutral-400 italic">Sin reserva</span>

                    @else
                      <span class="flex-1 text-sm text-neutral-400 dark:text-neutral-500">Disponible</span>
                      <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Reservar
                      </span>
                    @endif

                  </div>
                @endforeach
              </div>
            @endif
          @endforeach
        </div>
      @endif
    </div>

    {{-- ════════════════════════════════════
         PANEL DERECHO — dos secciones:
         1. Violeta (solo el calendario)
         2. Blanco (leyendas abajo)
         ════════════════════════════════════ --}}
    <div class="bk-calendar-panel flex flex-col gap-3">

      {{-- ── PARTE VIOLETA: filtro + mes + grilla ── --}}
      <div class="rounded-2xl overflow-hidden flex-shrink-0" style="background: #7c3aed;">

        {{-- Navegación de mes --}}
        <div class="px-5 pt-5 pb-4">
          <div class="flex items-center justify-between mb-4">
            <button wire:click="previousMonth"
                    class="p-1.5 rounded-full transition-colors"
                    style="color:rgba(255,255,255,0.6);"
                    onmouseenter="this.style.background='rgba(255,255,255,0.15)'"
                    onmouseleave="this.style.background='transparent'">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <button wire:click="goToToday"
                    class="text-base font-bold text-white capitalize transition-colors hover:opacity-75">
              {{ $currentMonthLabel }}
            </button>
            <button wire:click="nextMonth"
                    class="p-1.5 rounded-full transition-colors"
                    style="color:rgba(255,255,255,0.6);"
                    onmouseenter="this.style.background='rgba(255,255,255,0.15)'"
                    onmouseleave="this.style.background='transparent'">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>

          {{-- Días de semana --}}
          <div class="grid grid-cols-7 mb-2">
            @foreach(['L','M','M','J','V','S','D'] as $dn)
              <div class="text-center text-xs font-semibold py-1" style="color:rgba(255,255,255,0.65);">{{ $dn }}</div>
            @endforeach
          </div>

          {{-- Grilla de días --}}
          <div class="grid grid-cols-7 gap-y-1">
            @foreach($calendarDays as $day)
              @if($day === null)
                <div class="h-10"></div>
              @else
                <button wire:click="selectDate('{{ $day['date'] }}')"
                        class="relative h-10 w-full flex items-center justify-center rounded-full text-sm font-medium transition-all"
                        style="{{ $day['isSelected']
                            ? 'background:#fff; color:#7c3aed; font-weight:800;'
                            : ($day['isToday']
                                ? 'color:#fff; box-shadow:0 0 0 1px rgba(255,255,255,0.6); font-weight:700;'
                                : 'color:rgba(255,255,255,'.($day['isPast'] ? '0.4' : '1.0').');') }}">
                  {{ $day['day'] }}
                  @if(($day['hasConfirmed'] || $day['hasPending']) && !$day['isSelected'])
                    <span class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full"
                          style="background-color: {{ $day['hasConfirmed'] ? '#34d399' : '#fbbf24' }};"></span>
                  @endif
                </button>
              @endif
            @endforeach
          </div>
        </div>
      </div>{{-- /parte violeta --}}

      {{-- ── PARTE BLANCA: leyendas ── --}}
      <div class="rounded-2xl bg-white dark:bg-neutral-900 px-4 py-4 space-y-4">

        {{-- Leyenda de estados --}}
        <div class="space-y-2">
          <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-400 dark:text-neutral-500">Estado</p>
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
            <span class="text-xs text-neutral-600 dark:text-neutral-400">Confirmada</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 flex-shrink-0"></span>
            <span class="text-xs text-neutral-600 dark:text-neutral-400">Pendiente</span>
          </div>
        </div>

        {{-- Leyenda de espacios --}}
        @if($spaces->count())
          <div class="space-y-2">
            <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-400 dark:text-neutral-500">Espacios</p>
            @foreach($spaces as $space)
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $space->color }};"></span>
                <span class="text-xs text-neutral-600 dark:text-neutral-400 truncate">{{ $space->name }}</span>
              </div>
            @endforeach
          </div>
        @endif
      </div>{{-- /parte blanca --}}

    </div>{{-- /panel derecho --}}

  </div>{{-- /flex --}}


  {{-- ===== MODAL: Crear Reserva ===== --}}
  @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         wire:click.self="$set('showCreateModal', false)">
      <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">

        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
          <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">
            Nueva reserva — {{ \Carbon\Carbon::parse($createDate)->locale('es')->translatedFormat('d \d\e F') }}
          </h3>
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

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Fecha y hora de inicio *</label>
            <input type="datetime-local" wire:model="createStartsAt"
                   class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            @error('createStartsAt') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Duración *</label>
            @if($createDurationOptions->count())
              <div class="flex flex-wrap gap-2 mb-2">
                @foreach($createDurationOptions as $option)
                  <button type="button" wire:click="$set('createDurationOptionId', {{ $option->id }})"
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
              <input type="number" wire:model="createDurationMinutes" min="15" max="1440" step="15"
                     class="w-24 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                     placeholder="60">
              <span class="text-sm text-neutral-500">minutos</span>
            </div>
            @error('createDurationMinutes') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Monto total</label>
            <div class="relative">
              <span class="absolute left-3 top-2 text-sm text-neutral-500">$</span>
              <input type="number" wire:model="createTotalAmount" min="0" step="0.01"
                     class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 pl-7 pr-3 py-2 text-sm">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Cliente</label>
            <select wire:model.live="createClientId"
                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm mb-2">
              <option value="">Nombre manual o seleccioná cliente</option>
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

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Notas</label>
            <textarea wire:model="createNotes" rows="2"
                      class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm resize-none"
                      placeholder="Observaciones..."></textarea>
          </div>
        </div>

        <div class="px-5 py-4 border-t border-neutral-200 dark:border-neutral-700 flex gap-2 justify-end">
          <button wire:click="$set('showCreateModal', false)"
                  class="px-4 py-2 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
            Cancelar
          </button>
          <button wire:click="saveBooking" wire:loading.attr="disabled" wire:target="saveBooking"
                  class="px-4 py-2 text-sm font-medium rounded-lg bg-violet-600 hover:bg-violet-700 text-white transition-colors disabled:opacity-60">
            <span wire:loading.remove wire:target="saveBooking">Guardar reserva</span>
            <span wire:loading wire:target="saveBooking">Guardando...</span>
          </button>
        </div>
      </div>
    </div>
  @endif

  {{-- ===== MODAL: Detalle de Reserva ===== --}}
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
          <div class="flex items-center gap-2">
            @php $color = $detailBooking->statusColor() @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                         bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-300">
              {{ $detailBooking->statusLabel() }}
            </span>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">
              {{ $detailBooking->starts_at->locale('es')->translatedFormat('d \d\e F, H:i') }} – {{ $detailBooking->ends_at->format('H:i') }}
            </span>
          </div>

          <div class="text-sm">
            <span class="text-neutral-500 dark:text-neutral-400">Cliente: </span>
            <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $detailBooking->getClientDisplayName() }}</span>
            @if($detailBooking->getClientDisplayPhone())
              <span class="text-neutral-500 dark:text-neutral-400"> · {{ $detailBooking->getClientDisplayPhone() }}</span>
            @endif
          </div>

          <div class="flex items-center justify-between text-sm">
            <span class="text-neutral-500 dark:text-neutral-400">
              {{ $detailBooking->duration_minutes >= 60
                  ? floor($detailBooking->duration_minutes / 60).'h '.($detailBooking->duration_minutes % 60 ? ($detailBooking->duration_minutes % 60).'min' : '')
                  : $detailBooking->duration_minutes.'min' }}
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
            <button wire:click="confirmBooking({{ $detailBooking->id }})" wire:loading.attr="disabled"
                    class="flex-1 px-3 py-2 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors">
              Confirmar
            </button>
          @endif
          @if(in_array($detailBooking->status, ['pending', 'confirmed']))
            <button wire:click="cancelBooking({{ $detailBooking->id }})" wire:confirm="¿Cancelar esta reserva?"
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
