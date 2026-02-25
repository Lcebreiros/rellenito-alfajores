@extends('layouts.app')

@section('header')
  <div class="flex items-center gap-3">
    <a href="{{ route('rentals.bookings.index') }}"
       class="p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
      <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">Reserva #{{ $booking->id }}</h1>
      <p class="text-sm text-neutral-600 dark:text-neutral-400">
        {{ $booking->space->name ?? '—' }} · {{ $booking->starts_at->translatedFormat('d \d\e F \d\e Y, H:i') }}
      </p>
    </div>
  </div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto px-3 sm:px-6 space-y-4">

  @if(session('ok'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif
  @if($errors->any())
    <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- Tarjeta principal --}}
  <div class="container-glass shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $booking->space->color ?? '#6366f1' }};"></span>
        <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ $booking->space->name ?? '—' }}</span>
        @if($booking->space?->category)
          <span class="text-xs px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400">
            {{ $booking->space->category->name }}
          </span>
        @endif
      </div>
      @php $color = $booking->statusColor() @endphp
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                   bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-300">
        {{ $booking->statusLabel() }}
      </span>
    </div>

    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
      {{-- Fecha/hora --}}
      <div>
        <div class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-1">Horario</div>
        <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
          {{ $booking->starts_at->translatedFormat('l d \d\e F \d\e Y') }}
        </div>
        <div class="text-sm text-neutral-600 dark:text-neutral-400">
          {{ $booking->starts_at->format('H:i') }} – {{ $booking->ends_at->format('H:i') }}
        </div>
      </div>

      {{-- Duración y monto --}}
      <div>
        <div class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-1">Duración / Monto</div>
        <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
          {{ $booking->durationOption?->label ?? ($booking->duration_minutes >= 60
             ? floor($booking->duration_minutes/60).'h '.($booking->duration_minutes%60?($booking->duration_minutes%60).'min':'')
             : $booking->duration_minutes.'min') }}
        </div>
        <div class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
          ${{ number_format($booking->total_amount, 0, ',', '.') }}
        </div>
      </div>

      {{-- Cliente --}}
      <div>
        <div class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-1">Cliente</div>
        <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $booking->getClientDisplayName() }}</div>
        @if($booking->getClientDisplayPhone())
          <div class="text-sm text-neutral-500 dark:text-neutral-400">{{ $booking->getClientDisplayPhone() }}</div>
        @endif
        @if($booking->client)
          <a href="{{ route('clients.show', $booking->client) }}"
             class="text-xs text-violet-600 dark:text-violet-400 hover:underline mt-0.5 inline-block">
            Ver perfil del cliente
          </a>
        @endif
      </div>

      {{-- Notas --}}
      @if($booking->notes)
        <div>
          <div class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-1">Notas</div>
          <div class="text-sm text-neutral-700 dark:text-neutral-300">{{ $booking->notes }}</div>
        </div>
      @endif

      {{-- Google Calendar --}}
      @if($booking->google_calendar_event_id)
        <div class="sm:col-span-2">
          <div class="flex items-center gap-2 text-xs text-green-600 dark:text-green-400">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Sincronizado con Google Calendar
          </div>
        </div>
      @endif
    </div>
  </div>

  {{-- Métodos de pago --}}
  @if($booking->paymentMethods->count())
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="px-5 py-3 border-b border-neutral-200 dark:border-neutral-700">
        <h2 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Pagos</h2>
      </div>
      <div class="p-5 space-y-2">
        @foreach($booking->paymentMethods as $method)
          <div class="flex items-center justify-between text-sm">
            <span class="text-neutral-700 dark:text-neutral-300">{{ $method->name }}</span>
            <span class="font-medium text-neutral-900 dark:text-neutral-100">
              ${{ number_format($method->pivot->amount, 0, ',', '.') }}
            </span>
          </div>
        @endforeach
      </div>
    </div>
  @endif

  {{-- Acciones --}}
  <div class="flex flex-wrap gap-2">
    @if($booking->status === 'pending')
      <form method="POST" action="{{ route('rentals.bookings.confirm', $booking) }}" class="inline">
        @csrf
        <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors">
          Confirmar reserva
        </button>
      </form>
    @endif

    @if(in_array($booking->status, ['pending', 'confirmed']))
      <a href="{{ route('rentals.bookings.edit', $booking) }}"
         class="px-4 py-2 text-sm font-medium rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
        Editar
      </a>
      <form method="POST" action="{{ route('rentals.bookings.cancel', $booking) }}" class="inline"
            onsubmit="return confirm('¿Cancelar esta reserva?')">
        @csrf
        <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg border border-rose-300 text-rose-700 dark:border-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
          Cancelar reserva
        </button>
      </form>
    @endif

    <form method="POST" action="{{ route('rentals.bookings.destroy', $booking) }}" class="inline"
          onsubmit="return confirm('¿Eliminar esta reserva permanentemente?')">
      @csrf
      @method('DELETE')
      <button type="submit" class="px-4 py-2 text-sm rounded-lg text-neutral-500 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
        Eliminar
      </button>
    </form>
  </div>
</div>
@endsection
