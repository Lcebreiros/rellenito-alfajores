@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between">
    <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Ficha del empleado</h1>
    <div class="flex items-center gap-2">
      <a href="{{ route('company.employees.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">Volver</a>
      @can('update', $employee)
      <a href="{{ route('company.employees.edit', $employee) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Editar</a>
      @endcan
    </div>
  </div>
@endsection

@section('content')
@php
  $fullName = trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')) ?: '—';
  $photo = $employee->photo_path ? Storage::disk('public')->url($employee->photo_path) : null;
  $companyName = $employee->company->name ?? ($employee->company->business_name ?? '—');
  $branchName  = $employee->branch->name ?? '—';
@endphp

<div class="max-w-5xl mx-auto px-3 sm:px-6">
  <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow ring-1 ring-neutral-200/70 dark:ring-neutral-800 overflow-hidden">
    <div class="p-6 border-b border-neutral-200 dark:border-neutral-800 flex items-start gap-4">
      <div class="w-20 h-20 rounded-xl overflow-hidden bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 flex items-center justify-center">
        @if($photo)
          <img src="{{ $photo }}" alt="Foto" class="w-full h-full object-cover">
        @else
          <svg class="w-8 h-8 text-neutral-400" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.5"/><path d="M4 20c0-3.314 3.582-6 8-6s8 2.686 8 6" stroke="currentColor" stroke-width="1.5"/></svg>
        @endif
      </div>
      <div class="flex-1 min-w-0">
        <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100 truncate">{{ $fullName }}</h2>
        <div class="mt-1 text-sm text-neutral-500 dark:text-neutral-400 flex flex-wrap gap-x-3 gap-y-1">
          <span>DNI: <span class="text-neutral-800 dark:text-neutral-200">{{ $employee->dni ?: '—' }}</span></span>
          <span>Email: <span class="text-neutral-800 dark:text-neutral-200">{{ $employee->email ?: '—' }}</span></span>
        </div>
        <div class="mt-2 text-sm text-neutral-600 dark:text-neutral-300 flex flex-wrap gap-2">
          <span class="inline-flex items-center rounded-full px-2 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 text-xs">Rol: {{ $employee->role ?: '—' }}</span>
          <span class="inline-flex items-center rounded-full px-2 py-0.5 bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 text-xs">Sucursal: {{ $branchName }}</span>
          <span class="inline-flex items-center rounded-full px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 text-xs">Empresa: {{ $companyName }}</span>
        </div>
      </div>
    </div>

    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="space-y-4">
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 p-4 bg-neutral-50 dark:bg-neutral-900/40">
          <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Contacto</h3>
          <div class="text-sm text-neutral-700 dark:text-neutral-300">
            <div><span class="text-neutral-500 dark:text-neutral-400">Email:</span> {{ $employee->email ?: '—' }}</div>
            <div><span class="text-neutral-500 dark:text-neutral-400">Dirección:</span> {{ $employee->address ?: '—' }}</div>
            <div><span class="text-neutral-500 dark:text-neutral-400">Cobertura médica:</span> {{ $employee->medical_coverage ?: '—' }}</div>
            <div><span class="text-neutral-500 dark:text-neutral-400">Inicio:</span> {{ optional($employee->start_date)->format('d/m/Y') ?: '—' }}</div>
            <div><span class="text-neutral-500 dark:text-neutral-400">Contrato:</span> {{ $employee->contract_type ?: '—' }}</div>
            <div><span class="text-neutral-500 dark:text-neutral-400">Computadora:</span> {{ $employee->has_computer ? 'Sí' : 'No' }}</div>
          </div>
        </div>

        @if($employee->contract_file_path)
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 p-4 bg-neutral-50 dark:bg-neutral-900/40">
          <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Archivo de contrato</h3>
          <a href="{{ Storage::disk('public')->url($employee->contract_file_path) }}" target="_blank"
             class="inline-flex items-center gap-2 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Ver archivo</a>
        </div>
        @endif
      </div>

      <div class="md:col-span-2 space-y-6">
        @php
          $sections = [
            'family_group' => 'Grupo familiar',
            'evaluations'  => 'Evaluaciones',
            'objectives'   => 'Objetivos',
            'tasks'        => 'Tareas',
            'schedules'    => 'Horarios',
            'benefits'     => 'Beneficios',
          ];
        @endphp

        @foreach($sections as $field => $label)
          @php $val = $employee->$field; @endphp
          <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 p-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ $label }}</h3>
            </div>
            @if(is_array($val) && count($val))
              <ul class="list-disc list-inside text-sm text-neutral-700 dark:text-neutral-300 space-y-1">
                @foreach($val as $item)
                  <li>{{ is_string($item) ? $item : json_encode($item, JSON_UNESCAPED_UNICODE) }}</li>
                @endforeach
              </ul>
            @else
              <p class="text-sm text-neutral-500 dark:text-neutral-400">Sin información</p>
            @endif
          </div>
        @endforeach
      </div>
    </div>

    @can('delete', $employee)
    <div class="px-6 py-5 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-end">
      <form action="{{ route('company.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('¿Eliminar empleado?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">Eliminar</button>
      </form>
    </div>
    @endcan
  </div>
</div>
@endsection

