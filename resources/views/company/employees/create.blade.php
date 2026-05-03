@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">{{ __('company.new_employee_title') }}</h1>
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
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('company.employee_data_title') }}</h2>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('company.employee_data_desc') }}</p>
      </div>
      <a href="{{ route('company.employees.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
        {{ __('company.back_btn_short') }}
      </a>
    </div>

    <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
      {{-- Columna principal --}}
      <div class="lg:col-span-2 space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_first_name') }} *</label>
            <input name="first_name" value="{{ old('first_name') }}" required maxlength="120"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_last_name') }} *</label>
            <input name="last_name" value="{{ old('last_name') }}" required maxlength="120"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_dni') }}</label>
            <input name="dni" value="{{ old('dni') }}" maxlength="50"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_email') }}</label>
            <input name="email" type="email" value="{{ old('email') }}" maxlength="255"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_role') }}</label>
            <select name="role" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
              <option value="">{{ __('company.select_placeholder') }}</option>
              @foreach(($roles ?? ['Empleado','Supervisor','Gerente']) as $r)
                <option value="{{ $r }}" @selected(old('role')===$r)>{{ $r }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_branch') }}</label>
            <select name="branch_id" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
              <option value="">{{ __('company.no_branch_opt') }}</option>
              @php($companyId = auth()->user()->rootCompany()?->id ?? auth()->id())
              @foreach(\App\Models\Branch::where('company_id', $companyId)->get() as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id')==$branch->id)>{{ $branch->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_start_date') }}</label>
            <input type="date" name="start_date" value="{{ old('start_date') }}"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_contract_type') }}</label>
            <input name="contract_type" value="{{ old('contract_type') }}" maxlength="100"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_medical_coverage') }}</label>
          <input name="medical_coverage" value="{{ old('medical_coverage') }}" maxlength="255"
                 class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_address') }}</label>
          <textarea name="address" rows="3" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('address') }}</textarea>
        </div>

        <div class="flex items-center gap-3">
          <input id="has_computer" type="checkbox" name="has_computer" value="1" @checked(old('has_computer'))
                 class="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-indigo-600 dark:border-neutral-700">
          <label for="has_computer" class="text-sm text-neutral-700 dark:text-neutral-300">{{ __('company.field_has_computer') }}</label>
        </div>

        {{-- Acceso al sistema --}}
        <div
          x-data="{ on: {{ old('grant_access') ? 'true' : 'false' }} }"
          class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden"
        >
          {{-- Toggle header --}}
          <div class="flex items-center justify-between gap-4 px-4 py-3 bg-neutral-50 dark:bg-neutral-800/50">
            <div>
              <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">{{ __('company.emp_access_toggle_label') }}</div>
              <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ __('company.emp_access_toggle_desc') }}</div>
            </div>
            <button
              type="button"
              @click="on = !on"
              :class="on ? 'bg-indigo-600' : 'bg-neutral-300 dark:bg-neutral-600'"
              class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
              role="switch" :aria-checked="on.toString()"
            >
              <span :class="on ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"></span>
            </button>
            <input type="hidden" name="grant_access" :value="on ? '1' : '0'">
          </div>

          {{-- Campos condicionales --}}
          <div x-show="on" x-transition class="p-4 space-y-4 border-t border-neutral-200 dark:border-neutral-700">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                  {{ __('company.emp_access_email') }}
                </label>
                <input name="access_email" type="email" value="{{ old('access_email') }}" maxlength="255"
                       autocomplete="off"
                       placeholder="empleado@empresa.com"
                       class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ __('company.emp_access_email_hint') }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                  {{ __('company.emp_access_password') }}
                </label>
                <input name="access_password" type="password" autocomplete="new-password"
                       class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ __('company.emp_access_password_hint') }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                  {{ __('company.emp_access_password_confirm') }}
                </label>
                <input name="access_password_confirmation" type="password" autocomplete="new-password"
                       class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
              </div>
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
                  {{ __('company.emp_access_level') }}
                </label>
                <div class="flex items-center gap-4">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="access_level" value="employee"
                           @checked(old('access_level','employee') === 'employee')
                           class="text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-neutral-700 dark:text-neutral-200">{{ __('company.emp_access_level_employee') }}</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="access_level" value="admin"
                           @checked(old('access_level') === 'admin')
                           class="text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-neutral-700 dark:text-neutral-200">{{ __('company.emp_access_level_admin') }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Columna lateral --}}
      <div class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_photo') }}</label>
          <label for="photo"
                 class="block cursor-pointer rounded-lg border-2 border-dashed border-neutral-300 p-5 text-center hover:border-indigo-400 transition-colors dark:border-neutral-700 dark:hover:border-indigo-500">
            <div class="flex flex-col items-center gap-2">
              <img id="photoPreview" src="{{ asset('images/default-avatar.png') }}" alt="Previsualización"
                   class="w-20 h-20 rounded-full object-cover ring-2 ring-neutral-200 dark:ring-neutral-700">
              <div class="text-sm"><span class="font-medium text-indigo-600 dark:text-indigo-400">{{ __('company.upload_photo_btn') }}</span></div>
              <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('company.photo_hint') }}</p>
            </div>
            <input id="photo" name="photo" type="file" accept="image/*" class="sr-only">
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_salary') }}</label>
            <input name="salary" type="number" step="0.01" min="0" value="{{ old('salary') }}"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          </div>
        </div>

        <div class="space-y-4">
          <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">{{ __('company.json_section_title') }}</h3>
          <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('company.json_section_desc') }}</p>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_family_group') }}</label>
            <textarea name="family_group_json" rows="3" placeholder='[{"nombre":"Juan","parentesco":"Hijo"}]'
                      class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('family_group_json') }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_objectives') }}</label>
            <textarea name="objectives_json" rows="3" placeholder='[{"titulo":"Capacitación","estado":"pendiente"}]'
                      class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('objectives_json') }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_tasks') }}</label>
            <textarea name="tasks_json" rows="3" placeholder='[{"tarea":"Ordenar depósito","prioridad":"media"}]'
                      class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('tasks_json') }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_schedules') }}</label>
            <textarea name="schedules_json" rows="3" placeholder='{"lun":{"entrada":"09:00","salida":"18:00"}}'
                      class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('schedules_json') }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_benefits') }}</label>
            <textarea name="benefits_json" rows="3" placeholder='[{"tipo":"Comedor","detalle":"Almuerzo incluido"}]'
                      class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('benefits_json') }}</textarea>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_contract_file') }}</label>
          <input type="file" name="contract_file" accept=".pdf,.doc,.docx"
                 class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 file:mr-3 file:rounded-md file:border file:border-neutral-300 file:px-3 file:py-1.5 file:text-sm dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ __('company.contract_file_hint') }}</p>
        </div>
      </div>
    </div>

    <div class="px-6 py-5 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-end gap-2">
      <a href="{{ route('company.employees.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">{{ __('company.cancel_btn') }}</a>
      <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2.5 text-white hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95 transition-all duration-200">
        {{ __('company.save_btn') }}
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
