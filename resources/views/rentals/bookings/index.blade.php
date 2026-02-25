@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">Reservas</h1>
      <p class="text-sm text-neutral-600 dark:text-neutral-400">Historial y gestión de todas las reservas.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('rentals.calendar') }}"
         class="text-sm px-3 py-1.5 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        Calendario
      </a>
      <a href="{{ route('rentals.bookings.create') }}"
         class="text-sm px-3 py-1.5 rounded-lg bg-violet-600 hover:bg-violet-700 text-white font-medium transition-colors flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nueva reserva
      </a>
    </div>
  </div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-3 sm:px-6 space-y-4">

  @if(session('ok'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif

  {{-- Filtros --}}
  <form method="GET" action="{{ route('rentals.bookings.index') }}"
        class="container-glass shadow-sm p-3 sm:p-4 flex flex-wrap gap-2 items-end">
    <div>
      <label class="block text-xs text-neutral-500 mb-1">Estado</label>
      <select name="status"
              class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
        <option value="">Todos</option>
        <option value="pending"   {{ request('status') === 'pending'    ? 'selected' : '' }}>Pendiente</option>
        <option value="confirmed" {{ request('status') === 'confirmed'  ? 'selected' : '' }}>Confirmada</option>
        <option value="finished"  {{ request('status') === 'finished'   ? 'selected' : '' }}>Finalizada</option>
        <option value="cancelled" {{ request('status') === 'cancelled'  ? 'selected' : '' }}>Cancelada</option>
      </select>
    </div>
    <div>
      <label class="block text-xs text-neutral-500 mb-1">Espacio</label>
      <select name="space_id"
              class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
        <option value="">Todos</option>
        @foreach($spaces as $space)
          <option value="{{ $space->id }}" {{ request('space_id') == $space->id ? 'selected' : '' }}>
            {{ $space->name }}
          </option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs text-neutral-500 mb-1">Desde</label>
      <input type="date" name="date_from" value="{{ request('date_from') }}"
             class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs text-neutral-500 mb-1">Hasta</label>
      <input type="date" name="date_to" value="{{ request('date_to') }}"
             class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
    </div>
    <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-neutral-700 dark:bg-neutral-600 text-white hover:bg-neutral-800 dark:hover:bg-neutral-500 transition-colors">
      Filtrar
    </button>
    @if(request()->hasAny(['status','space_id','date_from','date_to']))
      <a href="{{ route('rentals.bookings.index') }}" class="px-4 py-2 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800">
        Limpiar
      </a>
    @endif
  </form>

  {{-- Tabla de reservas --}}
  <div class="container-glass shadow-sm overflow-hidden">
    @if($bookings->count())
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-neutral-50 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-700">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400">Espacio</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400">Inicio</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 hidden sm:table-cell">Duración</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400">Cliente</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400">Estado</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400">Monto</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
            @foreach($bookings as $booking)
              <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors">
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                          style="background-color: {{ $booking->space->color ?? '#6366f1' }};"></span>
                    <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $booking->space->name ?? '—' }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-neutral-600 dark:text-neutral-400 whitespace-nowrap">
                  {{ $booking->starts_at->translatedFormat('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3 text-neutral-600 dark:text-neutral-400 hidden sm:table-cell">
                  {{ $booking->duration_minutes >= 60
                     ? floor($booking->duration_minutes / 60).'h '.($booking->duration_minutes % 60 ? ($booking->duration_minutes % 60).'min' : '')
                     : $booking->duration_minutes.'min' }}
                </td>
                <td class="px-4 py-3">
                  <div class="text-neutral-900 dark:text-neutral-100">{{ $booking->getClientDisplayName() }}</div>
                  @if($booking->getClientDisplayPhone())
                    <div class="text-xs text-neutral-400">{{ $booking->getClientDisplayPhone() }}</div>
                  @endif
                </td>
                <td class="px-4 py-3">
                  @php $color = $booking->statusColor() @endphp
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                               bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-300">
                    {{ $booking->statusLabel() }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right font-semibold text-neutral-900 dark:text-neutral-100">
                  ${{ number_format($booking->total_amount, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-right">
                  <a href="{{ route('rentals.bookings.show', $booking) }}"
                     class="text-xs text-violet-600 dark:text-violet-400 hover:underline">
                    Ver
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($bookings->hasPages())
        <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-700">
          {{ $bookings->links() }}
        </div>
      @endif
    @else
      <div class="p-8 text-center text-neutral-500 dark:text-neutral-400">
        <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-sm">No hay reservas con los filtros seleccionados.</p>
      </div>
    @endif
  </div>
</div>
@endsection
