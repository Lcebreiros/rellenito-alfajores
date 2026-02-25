@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">Alquileres</h1>
      <p class="text-sm text-neutral-600 dark:text-neutral-400">Calendario de reservas y espacios.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('rentals.spaces.index') }}"
         class="text-sm px-3 py-1.5 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Espacios
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
@livewire('rentals.booking-calendar')
@endsection
