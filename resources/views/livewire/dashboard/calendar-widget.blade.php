<div wire:poll.visible.60s class="h-full flex flex-col
                                   bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800
                                   rounded-2xl shadow-sm overflow-hidden"
     x-data="{ showCalendar: false }">
  {{-- Header --}}
  <div class="px-4 sm:px-5 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
    <div>
      <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Calendario</h3>
      <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $currentMonth }}</div>
    </div>
    <div class="flex items-center gap-2">
      @if(auth()->user()->google_access_token && auth()->user()->google_refresh_token)
        {{-- Connected to Google Calendar --}}
        <div class="flex items-center gap-1.5 px-2 py-1 bg-green-50 dark:bg-green-900/20 rounded-lg" title="Conectado con Google Calendar">
          <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
          <span class="text-[10px] font-medium text-green-700 dark:text-green-300">Google</span>
        </div>
      @else
        {{-- Not connected - show connect button --}}
        <a href="{{ route('google.connect') }}"
           class="flex items-center gap-1.5 px-2 py-1 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
           title="Conectar con Google Calendar">
          <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/>
          </svg>
          <span class="text-[10px] font-medium text-blue-700 dark:text-blue-300">Conectar</span>
        </a>
      @endif
      <button @click="showCalendar = true"
              class="p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
              title="Ver calendario completo">
        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
      </button>
    </div>
  </div>

  {{-- Mini Stats --}}
  <div class="px-4 sm:px-5 pt-4 pb-2 grid grid-cols-2 gap-3">
    <div class="text-center">
      <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mb-1">Pagos Vencidos</div>
      <div class="text-2xl font-bold tabular-nums {{ $totalPaymentsDue > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-neutral-900 dark:text-white' }}">
        {{ $totalPaymentsDue }}
      </div>
    </div>
    <div class="text-center">
      <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mb-1">Compras Este Mes</div>
      <div class="text-2xl font-bold text-neutral-900 dark:text-white tabular-nums">
        {{ $totalPurchases }}
      </div>
    </div>
  </div>

  {{-- Events List --}}
  <div class="flex-1 px-4 sm:px-5 pb-4 overflow-y-auto dashboard-widget-scroll">
    <div class="text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Eventos</div>

    @if($upcomingEvents->isEmpty())
      <div class="flex flex-col items-center justify-center py-8 text-center">
        <svg class="w-12 h-12 text-neutral-300 dark:text-neutral-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">No hay eventos próximos</p>
      </div>
    @else
      <div class="space-y-2">
        @foreach($upcomingEvents as $event)
          @php
            $eventDate = \Carbon\Carbon::parse($event['date']);
            $isToday = $eventDate->isToday();
            $isPast = $eventDate->isPast() && !$isToday;
          @endphp

          <div class="flex items-start gap-2 p-2 rounded-lg {{ $isPast ? 'bg-rose-50 dark:bg-rose-900/20' : 'bg-neutral-50 dark:bg-neutral-800/50' }}">
            {{-- Icon --}}
            <div class="flex-shrink-0 mt-0.5">
              @if($event['type'] === 'payment')
                <div class="w-6 h-6 rounded-full flex items-center justify-center {{ $isPast || ($event['is_overdue'] ?? false) ? 'bg-rose-500 dark:bg-rose-600' : 'bg-blue-500 dark:bg-blue-600' }}">
                  <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
              @elseif($event['type'] === 'purchase')
                <div class="w-6 h-6 rounded-full flex items-center justify-center bg-emerald-500 dark:bg-emerald-600">
                  <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                  </svg>
                </div>
              @elseif($event['type'] === 'order')
                <div class="w-6 h-6 rounded-full flex items-center justify-center {{ ($event['is_overdue'] ?? false) ? 'bg-rose-500 dark:bg-rose-600' : 'bg-indigo-500 dark:bg-indigo-600' }}">
                  <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
              @elseif($event['type'] === 'booking')
                <div class="w-6 h-6 rounded-full flex items-center justify-center"
                     style="background-color: {{ $event['space_color'] ?? '#6366f1' }};">
                  <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
              @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                  <div class="text-xs font-medium text-neutral-900 dark:text-neutral-100 truncate">
                    {{ $event['title'] }}
                  </div>
                  @if($event['type'] === 'payment')
                    <div class="text-[10px] text-neutral-500 dark:text-neutral-400 truncate">
                      {{ $event['provider'] ?? '' }}
                    </div>
                  @elseif($event['type'] === 'purchase')
                    <div class="text-[10px] text-neutral-500 dark:text-neutral-400">
                      {{ number_format($event['quantity'], 2) }} {{ $event['unit'] }}
                    </div>
                  @elseif($event['type'] === 'order')
                    <div class="text-[10px] text-neutral-500 dark:text-neutral-400 truncate">
                      Agendado
                    </div>
                  @elseif($event['type'] === 'booking')
                    <div class="text-[10px] text-neutral-500 dark:text-neutral-400">
                      {{ $event['time'] ?? '' }} · {{ $event['status'] === 'confirmed' ? 'Confirmada' : 'Pendiente' }}
                    </div>
                  @endif
                </div>
                @if($event['type'] !== 'booking')
                <div class="text-right flex-shrink-0">
                  <div class="text-xs font-semibold {{ ($event['type'] === 'payment' && $isPast) || ($event['type']==='order' && ($event['is_overdue'] ?? false)) ? 'text-rose-600 dark:text-rose-400' : 'text-neutral-900 dark:text-white' }} tabular-nums">
                    ${{ number_format($event['amount'], 0, ',', '.') }}
                  </div>
                </div>
                @endif
              </div>

              {{-- Date --}}
              <div class="mt-1 flex items-center gap-1">
                <svg class="w-3 h-3 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-[10px] {{ $isToday ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-neutral-500 dark:text-neutral-400' }}">
                  @if($isToday)
                    Hoy
                  @elseif($isPast)
                    {{ $eventDate->diffForHumans() }}
                  @else
                    {{ $eventDate->format('d/m/Y') }}
                  @endif
                </span>
                @if($event['type'] === 'payment' && ($isPast || ($event['is_overdue'] ?? false)))
                  <span class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold ml-1">• Vencido</span>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Legend --}}
  <div class="px-4 sm:px-5 py-2 border-t border-neutral-200 dark:border-neutral-800">
    <div class="flex items-center justify-center gap-4 text-xs">
      <div class="flex items-center gap-1.5">
        <div class="w-2 h-2 rounded-full bg-blue-500 dark:bg-blue-600"></div>
        <span class="text-neutral-600 dark:text-neutral-400">Pagos</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-600"></div>
        <span class="text-neutral-600 dark:text-neutral-400">Compras</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-2 h-2 rounded-full bg-indigo-500 dark:bg-indigo-600"></div>
        <span class="text-neutral-600 dark:text-neutral-400">Agendados</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-2 h-2 rounded-full bg-rose-500 dark:bg-rose-600"></div>
        <span class="text-neutral-600 dark:text-neutral-400">Vencidos</span>
      </div>
    </div>
  </div>

  {{-- Calendar Modal (teleported to <body> to avoid sidebar clipping) --}}
  <template x-teleport="body">
    <div x-show="showCalendar"
         x-cloak
         @click.self="showCalendar = false"
         style="z-index: 10000;"
         class="fixed inset-0 flex items-center justify-center bg-black/50 p-1 sm:p-3 md:p-4">
      <div @click.stop
           class="bg-white dark:bg-neutral-900 rounded-lg sm:rounded-xl shadow-2xl border border-neutral-200 dark:border-neutral-800 w-full h-full sm:h-auto sm:max-h-[98vh] flex flex-col"
           style="max-width: min(100vw, 1400px);">
      {{-- Modal Header --}}
      <div class="px-3 sm:px-4 py-2 sm:py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-2 sm:gap-3">
          <h3 class="text-sm sm:text-base font-semibold text-neutral-900 dark:text-neutral-100">Calendario</h3>

          {{-- Month Navigation --}}
          <div class="flex items-center gap-1 sm:gap-2">
            <button wire:click="previousMonth"
                    class="p-1 rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                    title="Mes anterior">
              <svg class="w-3 h-3 sm:w-4 sm:h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>

            <button wire:click="goToToday"
                    class="px-2 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-xs font-medium rounded bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300 transition-colors">
              Hoy
            </button>

            <button wire:click="nextMonth"
                    class="p-1 rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                    title="Mes siguiente">
              <svg class="w-3 h-3 sm:w-4 sm:h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>

          <p class="text-[10px] sm:text-xs font-medium text-neutral-600 dark:text-neutral-400">{{ $currentMonth }}</p>

          @if(auth()->user()->google_access_token && auth()->user()->google_refresh_token)
            {{-- Disconnect button --}}
            <form action="{{ route('google.disconnect') }}" method="POST" class="inline">
              @csrf
              <button type="submit"
                      onclick="return confirm('¿Deseas desconectar tu cuenta de Google Calendar?')"
                      class="flex items-center gap-1 px-2 py-1 text-[10px] sm:text-xs font-medium rounded bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 text-red-700 dark:text-red-300 transition-colors"
                      title="Desconectar Google Calendar">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span class="hidden sm:inline">Desconectar Google</span>
              </button>
            </form>
          @endif
        </div>

        <button @click="showCalendar = false"
                class="p-1 sm:p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
          <svg class="w-4 h-4 sm:w-5 sm:h-5 text-neutral-500 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      {{-- Calendar Grid --}}
      <div class="p-2 sm:p-3 md:p-4 overflow-y-auto flex-1 dashboard-widget-scroll">
        {{-- Days of week header --}}
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.25rem; margin-bottom: 0.25rem;">
          @foreach(['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $day)
            <div class="text-center text-[10px] sm:text-xs font-semibold text-neutral-600 dark:text-neutral-400 py-0.5 sm:py-1">
              {{ $day }}
            </div>
          @endforeach
        </div>

        {{-- Calendar days grid --}}
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.25rem; grid-auto-rows: 1fr;">
          @foreach($calendarDays as $day)
            @if($day === null)
              {{-- Empty cell for days before month start --}}
              <div style="aspect-ratio: 1 / 1;" class="bg-neutral-50/50 dark:bg-neutral-800/30 rounded"></div>
            @else
              @php
                $hasEvents = !empty($day['events']);
                $isToday = $day['isToday'];
              @endphp
              {{-- Day cell --}}
              <div style="aspect-ratio: 1 / 1; display: flex; flex-direction: column; overflow: hidden;"
                   class="border rounded p-1 sm:p-1.5 md:p-2 {{ $isToday ? 'bg-indigo-50 dark:bg-indigo-950/30 border-indigo-400 dark:border-indigo-600 border-2' : 'border-neutral-200 dark:border-neutral-700' }} hover:shadow-md transition-shadow">
                {{-- Day number --}}
                <div class="text-[10px] sm:text-xs md:text-sm font-bold mb-0.5 sm:mb-1 {{ $isToday ? 'text-indigo-700 dark:text-indigo-300' : 'text-neutral-700 dark:text-neutral-300' }}">
                  {{ $day['day'] }}
                </div>

                {{-- Events for this day --}}
                @if($hasEvents)
                  <div style="flex: 1; overflow-y: auto; overflow-x: hidden; display: flex; flex-direction: column; gap: 0.125rem;" class="dashboard-widget-scroll">
                    @foreach($day['events'] as $event)
                      @php
                        $isOverdue = ($event['is_overdue'] ?? false) === true;
                        $bgColor = $isOverdue
                          ? 'bg-rose-500 dark:bg-rose-600'
                          : ($event['type'] === 'payment'
                              ? 'bg-blue-500 dark:bg-blue-600'
                              : ($event['type'] === 'order'
                                  ? 'bg-indigo-500 dark:bg-indigo-600'
                                  : 'bg-emerald-500 dark:bg-emerald-600'));
                      @endphp
                      <div class="text-[7px] sm:text-[8px] md:text-[10px] px-0.5 sm:px-1 md:px-1.5 py-0.5 sm:py-0.5 md:py-1 rounded {{ $bgColor }} text-white cursor-pointer hover:opacity-90 transition-opacity"
                           title="{{ $event['title'] }}: ${{ number_format($event['amount'], 0, ',', '.') }}">
                        <div class="font-medium truncate leading-tight">{{ $event['title'] }}</div>
                        <div class="opacity-90 truncate text-[6px] sm:text-[7px] md:text-[9px] leading-tight hidden sm:block">${{ number_format($event['amount'], 0, ',', '.') }}</div>
                      </div>
                    @endforeach
                  </div>
                @endif
              </div>
            @endif
          @endforeach
        </div>

        {{-- Legend --}}
        <div class="mt-2 sm:mt-3 md:mt-4 pt-2 sm:pt-3 border-t border-neutral-200 dark:border-neutral-800 flex-shrink-0">
          <div class="flex items-center justify-center gap-2 sm:gap-3 md:gap-4 text-[10px] sm:text-xs">
            <div class="flex items-center gap-1 sm:gap-1.5">
              <div class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded bg-blue-500 dark:bg-blue-600"></div>
              <span class="text-neutral-600 dark:text-neutral-400">Pagos</span>
            </div>
            <div class="flex items-center gap-1 sm:gap-1.5">
              <div class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded bg-emerald-500 dark:bg-emerald-600"></div>
              <span class="text-neutral-600 dark:text-neutral-400">Compras</span>
            </div>
            <div class="flex items-center gap-1 sm:gap-1.5">
              <div class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded bg-indigo-500 dark:bg-indigo-600"></div>
              <span class="text-neutral-600 dark:text-neutral-400">Agendados</span>
            </div>
            <div class="flex items-center gap-1 sm:gap-1.5">
              <div class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded bg-rose-500 dark:bg-rose-600"></div>
              <span class="text-neutral-600 dark:text-neutral-400">Vencidos</span>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </template>
</div>
