@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">Editar usuario #{{ $user->id }}</h1>
@endsection

@section('content')
<div class="max-w-xl mx-auto p-6">
  @if ($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="bg-white dark:bg-neutral-900 rounded-lg shadow border border-gray-200 dark:border-neutral-800 p-6">
    <form method="POST" action="{{ route('branch.users.update', $user) }}">
      @csrf
      @method('PUT')

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Nombre</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded" required />
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded" required />
      </div>

      <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Nueva contraseña</label>
          <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Confirmación</label>
          <input type="password" name="password_confirmation" class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded" />
        </div>
      </div>

      <div class="mb-4 flex items-center gap-2">
        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $user->is_active)) />
        <label for="is_active" class="text-sm text-neutral-800 dark:text-neutral-200">Activo</label>
      </div>

      <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Guardar</button>
        <a href="{{ route('branch.users.index') }}" class="px-4 py-2 border border-gray-300 dark:border-neutral-800 rounded">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection

