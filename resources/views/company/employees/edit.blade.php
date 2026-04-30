@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between">
    <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">{{ __('company.edit_employee_title') }}</h1>
    <a href="{{ route('company.employees.show', $employee) }}" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">{{ __('company.back_btn_short') }}</a>
  </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-6">
  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('company.employees.update', $employee) }}" enctype="multipart/form-data"
        class="rounded-2xl border border-neutral-200 bg-white shadow-sm overflow-hidden dark:border-neutral-800 dark:bg-neutral-900">
    @csrf
    @method('PUT')

    <div class="px-6 py-5 border-b border-neutral-200/70 dark:border-neutral-800/70">
      <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('company.employee_data_title') }}</h2>
      <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('company.employee_edit_desc') }}</p>
    </div>

    <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_first_name') }} *</label>
            <input name="first_name" value="{{ old('first_name', $employee->first_name) }}" required maxlength="120"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_last_name') }} *</label>
            <input name="last_name" value="{{ old('last_name', $employee->last_name) }}" required maxlength="120"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_dni') }}</label>
            <input name="dni" value="{{ old('dni', $employee->dni) }}" maxlength="50"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_email') }}</label>
            <input name="email" type="email" value="{{ old('email', $employee->email) }}" maxlength="255"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_role') }}</label>
            <select name="role" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
              <option value="">{{ __('company.select_placeholder') }}</option>
              @foreach(($roles ?? ['Empleado','Supervisor','Gerente']) as $r)
                <option value="{{ $r }}" @selected(old('role', $employee->role) === $r)>{{ $r }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_branch') }}</label>
            @php($companyId = auth()->user()->rootCompany()?->id ?? auth()->id())
            <select name="branch_id" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
              <option value="">{{ __('company.no_branch_opt') }}</option>
              @foreach(\App\Models\Branch::where('company_id', $companyId)->get() as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id', $employee->branch_id) == $branch->id)>{{ $branch->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_start_date') }}</label>
            <input type="date" name="start_date" value="{{ old('start_date', optional($employee->start_date)->format('Y-m-d')) }}"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_contract_type') }}</label>
            <input name="contract_type" value="{{ old('contract_type', $employee->contract_type) }}" maxlength="100"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_salary') }}</label>
            <input name="salary" type="number" step="0.01" min="0" value="{{ old('salary', $employee->salary) }}"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_medical_coverage') }}</label>
            <input name="medical_coverage" value="{{ old('medical_coverage', $employee->medical_coverage) }}" maxlength="255"
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_address') }}</label>
          <textarea name="address" rows="3" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('address', $employee->address) }}</textarea>
        </div>

        <div class="flex items-center gap-3">
          <input id="has_computer" type="checkbox" name="has_computer" value="1" @checked(old('has_computer', $employee->has_computer))
                 class="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-indigo-600 dark:border-neutral-700">
          <label for="has_computer" class="text-sm text-neutral-700 dark:text-neutral-300">{{ __('company.field_has_computer') }}</label>
        </div>

        <div class="space-y-4 mt-4">
          <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">{{ __('company.json_section_title') }}</h3>
          <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('company.json_section_desc') }}</p>

          @php
            $encode = fn($v) => $v ? json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
          @endphp

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_family_group') }}</label>
            <textarea name="family_group_json" rows="3" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('family_group_json', $encode($employee->family_group)) }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_objectives') }}</label>
            <textarea name="objectives_json" rows="3" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('objectives_json', $encode($employee->objectives)) }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_tasks') }}</label>
            <textarea name="tasks_json" rows="3" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('tasks_json', $encode($employee->tasks)) }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_schedules') }}</label>
            <textarea name="schedules_json" rows="3" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('schedules_json', $encode($employee->schedules)) }}</textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_benefits') }}</label>
            <textarea name="benefits_json" rows="3" class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">{{ old('benefits_json', $encode($employee->benefits)) }}</textarea>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.replace_contract_file') }}</label>
          <input type="file" name="contract_file" accept=".pdf,.doc,.docx"
                 class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-neutral-900 file:mr-3 file:rounded-md file:border file:border-neutral-300 file:px-3 file:py-1.5 file:text-sm dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100">
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ __('company.contract_file_hint') }}</p>
        </div>
      </div>

      <div class="space-y-4">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('company.field_photo') }}</label>
        <label for="photo" class="block">
          <div class="flex items-center gap-4">
            <img src="{{ $employee->photo_path ? Storage::disk('public')->url($employee->photo_path) : asset('images/default-avatar.png') }}"
                 class="w-20 h-20 rounded-full object-cover ring-2 ring-gray-100 dark:ring-neutral-800" />
            <div>
              <div class="text-sm text-neutral-700 dark:text-neutral-200">{{ __('company.upload_new_photo') }}</div>
              <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('company.photo_hint_edit') }}</div>
            </div>
          </div>
          <input id="photo" name="photo" type="file" accept="image/*" class="sr-only">
        </label>
      </div>
    </div>

    <div class="px-6 py-5 border-t border-neutral-200 dark:border-neutral-800 flex items-center justify-end gap-2">
      <a href="{{ route('company.employees.show', $employee) }}" class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">{{ __('company.cancel_btn') }}</a>
      <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2.5 text-white hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95 transition-all duration-200">{{ __('company.save_changes_btn') }}</button>
    </div>
  </form>
</div>
@endsection
