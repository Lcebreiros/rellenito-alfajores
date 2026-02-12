@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between">
    <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Ficha del empleado</h1>
    <div class="flex items-center gap-2">
      <a href="{{ route('company.employees.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">Volver</a>
      @can('update', $employee)
      <a href="{{ route('company.employees.edit', $employee) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Editar</a>
      @endcan
      <button id="openEmployeeCardBtn" type="button" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
        Descargar ficha
      </button>
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
<div x-data="{ openEval:false, openNote:false }" class="max-w-5xl mx-auto px-3 sm:px-6">
  @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('success') }}
    </div>
  @endif
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
        @if(auth()->user()->hasModule('parking'))
        <div x-data="{ openShifts: false }" class="rounded-xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
          <button @click="openShifts = !openShifts" class="w-full px-4 py-3 bg-neutral-50 dark:bg-neutral-900/40 hover:bg-neutral-100 dark:hover:bg-neutral-800/60 transition-colors">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-400 transition-transform" :class="openShifts ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Historial de Turnos</h3>
                @if(($shifts ?? collect())->count())
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ ($shifts ?? collect())->count() }}</span>
                @endif
              </div>
              <span class="text-xs text-neutral-500 dark:text-neutral-400" x-text="openShifts ? 'Ocultar' : 'Ver historial'"></span>
            </div>
          </button>

          <div x-show="openShifts" x-collapse x-cloak>
            @if(($shifts ?? collect())->isNotEmpty())
              <div class="divide-y divide-neutral-200 dark:divide-neutral-800">
                @foreach($shifts as $shift)
                  <div x-data="{ expanded: false }" class="bg-white dark:bg-neutral-900">
                    <button @click="expanded = !expanded" class="w-full px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-900/40 transition-colors">
                      <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 flex-1 text-left">
                          <svg class="w-4 h-4 text-neutral-400 transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                          </svg>
                          <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                              <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ optional($shift->started_at)->format('d/m/Y H:i') }}</span>
                              @if($shift->status === 'open')
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 text-xs font-medium">En curso</span>
                              @else
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 text-xs font-medium">Cerrado</span>
                              @endif
                            </div>
                            <div class="flex items-center gap-4 text-xs text-neutral-600 dark:text-neutral-400">
                              <span>{{ $shift->total_movements ?? 0 }} movimientos</span>
                              <span class="font-semibold text-neutral-900 dark:text-neutral-100">Total: ${{ number_format((float) $shift->incomes_total, 0, ',', '.') }}</span>
                              @php $diff = (float) $shift->cash_difference; @endphp
                              @if($diff != 0)
                                <span class="font-semibold {{ $diff > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                  Diferencia: {{ $diff > 0 ? '+' : '-' }}${{ number_format(abs($diff), 0, ',', '.') }}
                                </span>
                              @endif
                            </div>
                          </div>
                        </div>
                      </div>
                    </button>

                    <div x-show="expanded" x-collapse x-cloak class="px-4 pb-4 space-y-3 bg-neutral-50 dark:bg-neutral-900/20">
                      {{-- Información del turno --}}
                      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3">
                        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-3">
                          <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Efectivo Inicial</div>
                          <div class="font-semibold text-neutral-900 dark:text-neutral-100">${{ number_format((float) $shift->initial_cash, 0, ',', '.') }}</div>
                        </div>
                        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-3">
                          <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Efectivo Esperado</div>
                          <div class="font-semibold text-neutral-900 dark:text-neutral-100">${{ number_format((float) $shift->expected_cash, 0, ',', '.') }}</div>
                        </div>
                        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-3">
                          <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Efectivo Contado</div>
                          <div class="font-semibold text-neutral-900 dark:text-neutral-100">${{ number_format((float) $shift->cash_counted, 0, ',', '.') }}</div>
                        </div>
                        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-3">
                          <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Fin del turno</div>
                          <div class="font-semibold text-neutral-900 dark:text-neutral-100">{{ $shift->ended_at ? $shift->ended_at->format('d/m H:i') : 'En curso' }}</div>
                        </div>
                      </div>

                      {{-- Botón para ver detalle completo --}}
                      <div class="flex justify-end">
                        <a href="{{ route('parking.shifts.show', $shift) }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-white text-sm font-medium hover:bg-indigo-700 transition-colors">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                          </svg>
                          Ver detalle completo del turno
                        </a>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="px-4 py-8 text-center">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Sin turnos registrados.</p>
              </div>
            @endif
          </div>
        </div>
        @endif

        @php
          $sections = [
            'family_group' => 'Grupo familiar',
            'objectives'   => 'Objetivos',
            'tasks'        => 'Tareas',
            'schedules'    => 'Horarios',
            'benefits'     => 'Beneficios',
          ];
        @endphp

        {{-- Evaluaciones con botón para modal --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 p-4">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Evaluaciones</h3>
            @can('update', $employee)
            <button type="button" @click="openEval=true" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-white text-xs hover:bg-indigo-700">Agregar evaluación</button>
            @endcan
          </div>
          @php $evals = $employee->evaluations; @endphp
          @if(is_array($evals) && count($evals))
            <ul class="list-disc list-inside text-sm text-neutral-700 dark:text-neutral-300 space-y-1 mb-3">
              @foreach($evals as $ev)
                <li>
                  {{ is_array($ev) ? ($ev['text'] ?? json_encode($ev, JSON_UNESCAPED_UNICODE)) : $ev }}
                  @if(is_array($ev) && (!empty($ev['at']) || !empty($ev['by'])))
                    <span class="text-xs text-neutral-500">— {{ $ev['at'] ?? '' }}</span>
                  @endif
                </li>
              @endforeach
            </ul>
          @else
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-3">Sin evaluaciones</p>
          @endif
        </div>

        {{-- Notas con botón para modal --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 p-4">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Notas</h3>
            @can('update', $employee)
            <button type="button" @click="openNote=true" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-white text-xs hover:bg-indigo-700">Agregar nota</button>
            @endcan
          </div>
          @php $notes = $employee->notes; @endphp
          @if(is_array($notes) && count($notes))
            <ul class="list-disc list-inside text-sm text-neutral-700 dark:text-neutral-300 space-y-1 mb-3">
              @foreach($notes as $n)
                <li>
                  {{ is_array($n) ? ($n['text'] ?? json_encode($n, JSON_UNESCAPED_UNICODE)) : $n }}
                  @if(is_array($n) && (!empty($n['at']) || !empty($n['by'])))
                    <span class="text-xs text-neutral-500">— {{ $n['at'] ?? '' }}</span>
                  @endif
                </li>
              @endforeach
            </ul>
          @else
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-3">Sin notas</p>
          @endif
        </div>

        {{-- Resto de secciones informativas --}}
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

  {{-- Modal: Agregar evaluación --}}
  @can('update', $employee)
  <div x-cloak x-show="openEval" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click="openEval=false"></div>
    <div class="relative w-full max-w-lg mx-auto bg-white dark:bg-neutral-900 rounded-xl shadow-lg border border-neutral-200 dark:border-neutral-800 p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Agregar evaluación</h3>
        <button class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400" @click="openEval=false">✕</button>
      </div>
      <form method="POST" action="{{ route('company.employees.evaluations.add', $employee) }}" class="space-y-3">
        @csrf
        <textarea name="evaluation" rows="5" required placeholder="Escribe una evaluación"
                  class="w-full rounded-lg border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"></textarea>
        <div class="flex justify-end gap-2">
          <button type="button" @click="openEval=false" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">Cancelar</button>
          <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal: Agregar nota --}}
  <div x-cloak x-show="openNote" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click="openNote=false"></div>
    <div class="relative w-full max-w-lg mx-auto bg-white dark:bg-neutral-900 rounded-xl shadow-lg border border-neutral-200 dark:border-neutral-800 p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Agregar nota</h3>
        <button class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400" @click="openNote=false">✕</button>
      </div>
      <form method="POST" action="{{ route('company.employees.notes.add', $employee) }}" class="space-y-3">
        @csrf
        <textarea name="note" rows="5" required placeholder="Escribe una nota"
                  class="w-full rounded-lg border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"></textarea>
        <div class="flex justify-end gap-2">
          <button type="button" @click="openNote=false" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">Cancelar</button>
          <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Guardar</button>
        </div>
      </form>
    </div>
  </div>
  @endcan
</div>
@endsection

@push('modals')
{{-- Modal de Ficha del Empleado --}}
<div id="employeeCardModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 max-w-2xl w-full mx-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">Ficha del empleado</h3>
      <button id="closeEmployeeCard" class="text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <div class="space-y-4" id="employeeCardPrintable">
      <div class="flex items-start gap-4">
        <div class="w-16 h-16 rounded-lg overflow-hidden bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 flex items-center justify-center">
          @if($photo)
            <img src="{{ $photo }}" alt="Foto" class="w-full h-full object-cover">
          @else
            <svg class="w-7 h-7 text-neutral-400" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.5"/><path d="M4 20c0-3.314 3.582-6 8-6s8 2.686 8 6" stroke="currentColor" stroke-width="1.5"/></svg>
          @endif
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $fullName }}</div>
          <div class="text-sm text-neutral-600 dark:text-neutral-300">{{ $employee->role ?: '—' }}</div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
          <div class="text-xs text-neutral-500">Empresa</div>
          <div class="font-medium text-neutral-800 dark:text-neutral-200">{{ $companyName }}</div>
        </div>
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
          <div class="text-xs text-neutral-500">Sucursal</div>
          <div class="font-medium text-neutral-800 dark:text-neutral-200">{{ $branchName }}</div>
        </div>
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
          <div class="text-xs text-neutral-500">DNI</div>
          <div class="font-medium text-neutral-800 dark:text-neutral-200">{{ $employee->dni ?: '—' }}</div>
        </div>
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
          <div class="text-xs text-neutral-500">Email</div>
          <div class="font-medium text-neutral-800 dark:text-neutral-200">{{ $employee->email ?: '—' }}</div>
        </div>
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
          <div class="text-xs text-neutral-500">Inicio</div>
          <div class="font-medium text-neutral-800 dark:text-neutral-200">{{ optional($employee->start_date)->format('d/m/Y') ?: '—' }}</div>
        </div>
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
          <div class="text-xs text-neutral-500">Contrato</div>
          <div class="font-medium text-neutral-800 dark:text-neutral-200">{{ $employee->contract_type ?: '—' }}</div>
        </div>
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
          <div class="text-xs text-neutral-500">Cobertura médica</div>
          <div class="font-medium text-neutral-800 dark:text-neutral-200">{{ $employee->medical_coverage ?: '—' }}</div>
        </div>
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
          <div class="text-xs text-neutral-500">Salario</div>
          <div class="font-medium text-neutral-800 dark:text-neutral-200">{{ $employee->salary ? ('$ '.number_format($employee->salary, 2, ',', '.')) : '—' }}</div>
        </div>
      </div>

      @php
        $summarySections = [
          'objectives' => 'Objetivos',
          'tasks'      => 'Tareas',
          'benefits'   => 'Beneficios',
        ];
      @endphp
      @foreach($summarySections as $field => $label)
        @php $val = $employee->$field; @endphp
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
          <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-1">{{ $label }}</div>
          @if(is_array($val) && count($val))
            <ul class="list-disc list-inside text-sm text-neutral-700 dark:text-neutral-300 space-y-0.5">
              @foreach($val as $item)
                <li>{{ is_string($item) ? $item : json_encode($item, JSON_UNESCAPED_UNICODE) }}</li>
              @endforeach
            </ul>
          @else
            <div class="text-sm text-neutral-500">Sin datos</div>
          @endif
        </div>
      @endforeach
    </div>

    <div class="mt-4 flex items-center justify-end gap-2 print:hidden">
      <button id="printEmployeeCard" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Guardar como PDF</button>
    </div>
  </div>
</div>

{{-- Print styles para ficha --}}
<style>
@media print {
  header, nav, .print\:hidden, #employeeCardModal .print\:hidden, #openEmployeeCardBtn, #closeEmployeeCard, #printEmployeeCard, [x-data] > *:not(#employeeCardModal) { display: none !important; }
  body { background: #fff !important; color: #000 !important; }
  .dark * { color: #000 !important; background: #fff !important; }
  #employeeCardModal { position: static !important; inset: auto !important; background: transparent !important; }
  #employeeCardPrintable { box-shadow: none !important; border: none !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const openBtn = document.getElementById('openEmployeeCardBtn');
  const modal = document.getElementById('employeeCardModal');
  const closeBtn = document.getElementById('closeEmployeeCard');
  const printBtn = document.getElementById('printEmployeeCard');

  const show = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); }
  const hide = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); }

  openBtn?.addEventListener('click', show);
  closeBtn?.addEventListener('click', hide);
  modal?.addEventListener('click', (e) => { if(e.target === modal) hide(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) hide(); });
  printBtn?.addEventListener('click', () => { window.print(); hide(); });
});
</script>
@endpush
