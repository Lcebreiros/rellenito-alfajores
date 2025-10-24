@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">Crear usuario</h1>
@endsection

@section('content')
<div class="max-w-2xl mx-auto p-6">
  @if ($errors->any())
    <div class="mb-4 p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-300 rounded">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="bg-white dark:bg-neutral-900 rounded-lg shadow border border-gray-200 dark:border-neutral-800 p-6">
    <form method="POST" action="{{ route('branch.users.store') }}">
      @csrf

      @if(isset($branches))
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Sucursal</label>
          <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            @if(auth()->user()?->isCompany())
              <option value="company" @selected(request('branch_id')==='company')>Empresa (sin sucursal)</option>
            @endif
            @foreach($branches as $branch)
              <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>
                {{ $branch->name }}
              </option>
            @endforeach
          </select>
        </div>
      @endif

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Nombre</label>
        <input type="text" name="name" value="{{ old('name') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required />
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required />
      </div>

      <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Contraseña</label>
          <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Confirmar contraseña</label>
          <input type="password" name="password_confirmation" class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required />
        </div>
      </div>

      <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
          Crear usuario
        </button>
        <a href="{{ url()->previous() }}" class="px-4 py-2 border border-gray-300 dark:border-neutral-800 text-gray-700 dark:text-neutral-200 rounded-md hover:bg-gray-50 dark:hover:bg-neutral-800">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection
