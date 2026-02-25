@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">Crear ingreso</h1>
      <p class="text-sm text-neutral-600 dark:text-neutral-400">Panel de operador para registrar ingresos y egresos.</p>
    </div>
  </div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 space-y-4">
  {{-- Panel de Operador con Scanner 3nstar --}}
  @livewire('parking.operator-panel')
</div>
@endsection
