@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Nuevo empleado</h1>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-6">

  @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('success') }}
    </div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('company.employees.store') }}" enctype="multipart/form-data"
        class="rounded-2xl border border-neutral-200 bg-white shadow-sm overflow-hidden dark:border-neutral-800 dark:bg-neutral-900">
    @csrf

    <div class="px-6 py-5 border-b border-neutral-200/70 dark:border-neutral-800/70 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Datos del empleado</h2>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Completá los datos básicos y adjunta archivos opcionales.</p>
      </div>
      <a href="{{ route('company.employees.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
        Volver
      </a>
    </div>

    <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
      {{-- Columna principal --}}
      <div class="lg:col-span-2 space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Nombre *</label>
            <input name="first_name" value="{{ old('first_name') }}" required maxlength="120"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Apellido *</label>
            <input name="last_name" value="{{ old('last_name') }}" required maxlength="120"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">DNI</label>
            <input name="dni" value="{{ old('dni') }}" maxlength="50"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Email</label>
            <input name="email" type="email" value="{{ old('email') }}" maxlength="255"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Rol</label>
            <select name="role" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
              <option value="">Seleccione</option>
              @foreach(($roles ?? ['Empleado','Supervisor','Gerente']) as $r)
                <option value="{{ $r }}" @selected(old('role')===$r)>{{ $r }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Sucursal</label>
            <select name="branch_id" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
              <option value="">Sin sucursal</option>
              @php($companyId = auth()->user()->rootCompany()?->id ?? auth()->id())
              @foreach(\App\Models\Branch::where('company_id', $companyId)->get() as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id')==$branch->id)>{{ $branch->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Fecha de inicio</label>
            <input type="date" name="start_date" value="{{ old('start_date') }}"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Tipo de contrato</label>
            <input name="contract_type" value="{{ old('contract_type') }}" maxlength="100"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Cobertura médica</label>
          <input name="medical_coverage" value="{{ old('medical_coverage') }}" maxlength="255"
                 class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Dirección</label>
          <textarea name="address" rows="3" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('address') }}</textarea>
        </div>

        <div class="flex items-center gap-3">
          <input id="has_computer" type="checkbox" name="has_computer" value="1" @checked(old('has_computer'))
                 class="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-indigo-600 dark:border-neutral-700">
          <label for="has_computer" class="text-sm text-neutral-700 dark:text-neutral-300">Cuenta con computadora</label>
        </div>
      </div>

      {{-- Columna lateral --}}
      <div class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Foto</label>
          <label for="photo"
                 class="block cursor-pointer rounded-lg border-2 border-dashed border-neutral-300 p-5 text-center hover:border-indigo-400 transition-colors dark:border-neutral-700 dark:hover:border-indigo-500">
            <div class="flex flex-col items-center gap-2">
              <img id="photoPreview" src="{{ asset('images/default-avatar.png') }}" alt="Previsualización"
                   class="w-20 h-20 rounded-full object-cover ring-2 ring-neutral-200 dark:ring-neutral-700">
              <div class="text-sm"><span class="font-medium text-indigo-600 dark:text-indigo-400">Subir foto</span></div>
              <p class="text-xs text-neutral-500 dark:text-neutral-400">PNG, JPG (hasta 2MB)</p>
            </div>
            <input id="photo" name="photo" type="file" accept="image/*" class="sr-only">
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Salario</label>
            <input name="salary" type="number" step="0.01" min="0" value="{{ old('salary') }}"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          </div>
        </div>

        <div class="space-y-4">
          <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Datos opcionales (JSON)</h3>
          <p class="text-xs text-neutral-500 dark:text-neutral-400">Podés dejar estos campos vacíos o pegar JSON válido.</p>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Grupo familiar (JSON)</label>
            <textarea name="family_group_json" rows="3" placeholder='[{"nombre":"Juan","parentesco":"Hijo"}]'
                      class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('family_group_json') }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Objetivos (JSON)</label>
            <textarea name="objectives_json" rows="3" placeholder='[{"titulo":"Capacitación","estado":"pendiente"}]'
                      class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('objectives_json') }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Tareas (JSON)</label>
            <textarea name="tasks_json" rows="3" placeholder='[{"tarea":"Ordenar depósito","prioridad":"media"}]'
                      class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('tasks_json') }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Horarios (JSON)</label>
            <textarea name="schedules_json" rows="3" placeholder='{"lun":{"entrada":"09:00","salida":"18:00"}}'
                      class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('schedules_json') }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Beneficios (JSON)</label>
            <textarea name="benefits_json" rows="3" placeholder='[{"tipo":"Comedor","detalle":"Almuerzo incluido"}]'
                      class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('benefits_json') }}</textarea>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Archivo de contrato</label>
          <input type="file" name="contract_file" accept=".pdf,.doc,.docx"
                 class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 file:mr-3 file:rounded-md file:border file:border-neutral-300 file:px-3 file:py-1.5 file:text-sm dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">PDF, DOC, DOCX (hasta 5MB).</p>
        </div>
      </div>
    </div>

    <div class="px-6 py-5 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-end gap-2">
      <a href="{{ route('company.employees.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">Cancelar</a>
      <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-white hover:bg-indigo-700">
        Guardar
      </button>
    </div>
  </form>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('photo');
    const preview = document.getElementById('photoPreview');
    if (!input || !preview) return;

    input.addEventListener('change', (e) => {
      const file = e.target.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (ev) => {
        if (ev.target?.result) {
          preview.src = ev.target.result;
        }
      };
      reader.readAsDataURL(file);
    });
  });
</script>
@endpush
@endsection
