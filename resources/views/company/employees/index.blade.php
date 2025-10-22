@extends('layouts.app')

@section('title', 'Empleados')

@section('content')
<div class="max-w-6xl mx-auto p-6 text-neutral-900 dark:text-neutral-100">

    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">Empleados</h1>
      <a href="{{ route('company.employees.create') }}"
         class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Nuevo empleado
      </a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('company.employees.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por nombre, DNI o email"
               class="border rounded p-2 w-full bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-800 text-neutral-900 dark:text-neutral-100 placeholder-neutral-500 dark:placeholder-neutral-400">

        <select name="branch_id" class="border rounded p-2 w-full bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-800 text-neutral-900 dark:text-neutral-100">
            <option value="">Todas las sucursales</option>
            @foreach(\App\Models\Branch::where('company_id', auth()->user()->company_id)->get() as $branch)
                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>

        <select name="role" class="border rounded p-2 w-full bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-800 text-neutral-900 dark:text-neutral-100">
            <option value="">Todos los roles</option>
            @foreach(config('hr.roles', ['Empleado','Supervisor','Gerente']) as $role)
                <option value="{{ $role }}" @selected(($filters['role'] ?? '') == $role)>{{ $role }}</option>
            @endforeach
        </select>

        <select name="has_computer" class="border rounded p-2 w-full bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-800 text-neutral-900 dark:text-neutral-100">
            <option value="">Todos</option>
            <option value="1" @selected(($filters['has_computer'] ?? '') === "1")>Con computadora</option>
            <option value="0" @selected(($filters['has_computer'] ?? '') === "0")>Sin computadora</option>
        </select>

        <button type="submit" class="col-span-1 md:col-span-4 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">Filtrar</button>
    </form>

    {{-- Listado de empleados --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($employees as $employee)
            <div class="bg-white dark:bg-neutral-900 rounded-lg shadow border border-transparent dark:border-neutral-800 p-4 flex flex-col items-center text-center">
                <img src="{{ $employee->photo_url ?? asset('images/default-avatar.png') }}"
                     alt="{{ $employee->first_name }} {{ $employee->last_name }}"
                     class="w-24 h-24 rounded-full mb-2 object-cover">

                <h2 class="font-semibold text-lg text-neutral-900 dark:text-neutral-100">{{ $employee->first_name }} {{ $employee->last_name }}</h2>
                <p class="text-sm text-gray-600 dark:text-neutral-300">{{ $employee->role ?? '-' }}</p>
                <p class="text-sm text-gray-500 dark:text-neutral-400">{{ $employee->branch->name ?? '-' }}</p>

                <a href="{{ route('company.employees.show', $employee) }}"
                   class="mt-3 inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                   Ver ficha
                </a>
            </div>
        @empty
            <p class="col-span-full text-gray-500 dark:text-neutral-400">No se encontraron empleados.</p>
        @endforelse
    </div>

    {{-- Paginación --}}
    <div class="mt-6">
        {{ $employees->links() }}
    </div>

</div>
@endsection
