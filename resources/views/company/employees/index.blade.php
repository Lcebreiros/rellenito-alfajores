@extends('layouts.app')

@section('title', 'Empleados')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
  <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 flex items-center">
    <i class="fas fa-users text-indigo-600 dark:text-indigo-400 mr-3"></i> 
    Empleados
    @if(isset($filters['branch_id']) && $filters['branch_id'])
      @php
        $currentBranch = \App\Models\Branch::find($filters['branch_id']);
      @endphp
      @if($currentBranch)
        <span class="ml-2 text-sm font-normal text-gray-500 dark:text-neutral-400">
          - {{ $currentBranch->name }}
        </span>
      @endif
    @endif
  </h1>

  <div class="flex gap-2 mt-3 sm:mt-0">
    <a href="{{ route('company.employees.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm
              bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition-colors whitespace-nowrap">
      <i class="fas fa-user-plus"></i>
      <span>Nuevo empleado</span>
    </a>
  </div>
</div>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">

  {{-- Mensajes --}}
  @if(session('success'))
    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 text-green-800 px-4 py-3 flex items-center
                dark:border-green-700 dark:bg-green-900/20 dark:text-green-200">
      <i class="fas fa-check-circle text-green-500 dark:text-green-300 mr-3"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  {{-- Estadísticas resumen --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 dark:text-neutral-400">Total Empleados</p>
          <p class="text-xl font-bold text-gray-900 dark:text-neutral-100">{{ $employees->total() }}</p>
        </div>
        <div class="rounded-lg bg-indigo-50 dark:bg-indigo-500/10 p-2">
          <i class="fas fa-users text-indigo-600 dark:text-indigo-300"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 dark:text-neutral-400">Sucursales</p>
          <p class="text-xl font-bold text-gray-900 dark:text-neutral-100">
            @php($companyId = auth()->user()->rootCompany()?->id ?? auth()->id())
            {{ \App\Models\Branch::where('company_id', $companyId)->count() }}
          </p>
        </div>
        <div class="rounded-lg bg-blue-50 dark:bg-blue-500/10 p-2">
          <i class="fas fa-building text-blue-600 dark:text-blue-300"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 dark:text-neutral-400">Con PC</p>
          <p class="text-xl font-bold text-gray-900 dark:text-neutral-100">
            {{ $employees->getCollection()->where('has_computer', true)->count() }}
          </p>
        </div>
        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-500/10 p-2">
          <i class="fas fa-laptop text-emerald-600 dark:text-emerald-300"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 dark:text-neutral-400">En esta página</p>
          <p class="text-xl font-bold text-gray-900 dark:text-neutral-100">{{ $employees->count() }}</p>
        </div>
        <div class="rounded-lg bg-purple-50 dark:bg-purple-500/10 p-2">
          <i class="fas fa-list text-purple-600 dark:text-purple-300"></i>
        </div>
      </div>
    </div>
  </div>

  {{-- Filtros --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm p-4 mb-6 border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10">
    <form method="GET" action="{{ route('company.employees.index') }}">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-3">
        {{-- Búsqueda --}}
        <div class="lg:col-span-2">
          <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            <i class="fas fa-search text-gray-500 dark:text-neutral-400 mr-1"></i> Buscar
          </label>
          <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-neutral-500 text-sm pointer-events-none"></i>
            <input type="text" 
                   name="q" 
                   value="{{ $filters['q'] ?? '' }}" 
                   placeholder="Nombre, DNI o email..."
                   class="w-full pl-9 pr-4 py-2.5 rounded-lg text-sm
                          border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                          dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-100 dark:placeholder-neutral-400">
          </div>
        </div>

        {{-- Sucursal --}}
        <div>
          <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            <i class="fas fa-building text-gray-500 dark:text-neutral-400 mr-1"></i> Sucursal
          </label>
          <div class="relative">
            <select name="branch_id" 
                    class="w-full px-3 py-2.5 pr-8 rounded-lg text-sm appearance-none
                           border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                           dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-100">
              <option value="">Todas</option>
              @foreach(\App\Models\Branch::where('company_id', $companyId)->get() as $branch)
                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>
                  {{ $branch->name }}
                </option>
              @endforeach
            </select>
            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
          </div>
        </div>

        {{-- Rol --}}
        <div>
          <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            <i class="fas fa-user-tag text-gray-500 dark:text-neutral-400 mr-1"></i> Rol
          </label>
          <div class="relative">
            <select name="role" 
                    class="w-full px-3 py-2.5 pr-8 rounded-lg text-sm appearance-none
                           border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                           dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-100">
              <option value="">Todos</option>
              @foreach(config('hr.roles', ['Empleado','Supervisor','Gerente']) as $role)
                <option value="{{ $role }}" @selected(($filters['role'] ?? '') == $role)>{{ $role }}</option>
              @endforeach
            </select>
            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
          </div>
        </div>

        {{-- Equipo --}}
        <div>
          <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            <i class="fas fa-laptop text-gray-500 dark:text-neutral-400 mr-1"></i> Equipo
          </label>
          <div class="relative">
            <select name="has_computer" 
                    class="w-full px-3 py-2.5 pr-8 rounded-lg text-sm appearance-none
                           border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                           dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-100">
              <option value="">Todos</option>
              <option value="1" @selected(($filters['has_computer'] ?? '') === "1")>Con PC</option>
              <option value="0" @selected(($filters['has_computer'] ?? '') === "0")>Sin PC</option>
            </select>
            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
          </div>
        </div>
      </div>

      {{-- Botones de acción --}}
      <div class="flex items-center gap-2">
        <button type="submit" 
                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors
                       bg-indigo-600 text-white hover:bg-indigo-700 whitespace-nowrap">
          <i class="fas fa-filter text-xs"></i>
          Aplicar filtros
        </button>
        @if(request()->has('q') || request()->has('branch_id') || request()->has('role') || request()->has('has_computer'))
          <a href="{{ route('company.employees.index') }}" 
             class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                    bg-gray-100 text-gray-700 hover:bg-gray-200
                    dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700 whitespace-nowrap">
            <i class="fas fa-eraser text-xs"></i>
            Limpiar
          </a>
        @endif
      </div>
    </form>
  </div>

  {{-- Listado de empleados --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 p-4">
    @if($employees->count() === 0)
      <div class="py-16 text-center">
        <i class="fas fa-user-slash text-gray-300 dark:text-neutral-600 text-5xl mb-3"></i>
        <div class="text-lg font-semibold text-gray-900 dark:text-neutral-100 mb-2">
          No se encontraron empleados
        </div>
        <p class="text-sm text-gray-600 dark:text-neutral-300 mb-6">
          @if(request()->has('q') || request()->has('branch_id') || request()->has('role') || request()->has('has_computer'))
            Intenta ajustar los filtros para obtener resultados diferentes
          @else
            Aún no hay empleados registrados en el sistema
          @endif
        </p>
        @if(request()->has('q') || request()->has('branch_id') || request()->has('role') || request()->has('has_computer'))
          <a href="{{ route('company.employees.index') }}" 
             class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-neutral-300 text-sm font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 transition-colors whitespace-nowrap">
            <i class="fas fa-eraser"></i>
            Limpiar filtros
          </a>
        @endif
      </div>
    @else
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        @foreach($employees as $employee)
          <div class="rounded-xl border border-gray-200 dark:border-neutral-700 p-3 hover:shadow-sm hover:border-indigo-300 dark:hover:border-indigo-600 transition-all bg-white dark:bg-neutral-900">
            <div class="flex items-start gap-3 mb-3">
              <div class="relative flex-shrink-0">
                <img src="{{ $employee->photo_url }}"
                     alt="{{ $employee->first_name }} {{ $employee->last_name }}"
                     class="w-14 h-14 rounded-full object-cover ring-2 ring-gray-100 dark:ring-neutral-800">
                @if($employee->has_computer)
                  <div class="absolute -bottom-0.5 -right-0.5 bg-indigo-600 rounded-full p-1">
                    <i class="fas fa-laptop text-white text-[10px]"></i>
                  </div>
                @endif
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-base text-gray-900 dark:text-neutral-100 truncate">
                  {{ $employee->first_name }} {{ $employee->last_name }}
                </h3>
                @if($employee->role)
                  <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                    {{ $employee->role }}
                  </span>
                @endif
              </div>
            </div>

            @if($employee->branch)
              <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-neutral-400 mb-3 bg-gray-50 dark:bg-neutral-800/60 rounded-lg p-2">
                <i class="fas fa-building text-gray-400 dark:text-neutral-500 flex-shrink-0 text-xs"></i>
                <span class="truncate">{{ $employee->branch->name }}</span>
              </div>
            @endif

            <a href="{{ route('company.employees.show', $employee) }}"
               class="block w-full text-center px-3 py-2 bg-gray-50 dark:bg-neutral-800/60 text-gray-900 dark:text-neutral-100 text-sm font-medium rounded-lg hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 transition-all">
              <i class="fas fa-eye mr-1"></i> Ver perfil
            </a>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Paginación --}}
  @if($employees->hasPages())
    <div class="mt-6">
      {{ $employees->withQueryString()->links() }}
    </div>
  @endif

</div>

{{-- Font Awesome Icons --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
