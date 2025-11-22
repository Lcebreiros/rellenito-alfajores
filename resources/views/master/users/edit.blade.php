@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold">Editar usuario</h1>
@endsection

@section('content')
<div class="container mx-auto max-w-3xl">
  @if(session('success'))
    <div class="mb-4 text-green-700 bg-green-100 p-3 rounded">{{ session('success') }}</div>
  @endif

  <div class="bg-white shadow rounded p-6">
    <form action="{{ route('master.users.update', $user) }}" method="POST" class="space-y-4">
      @csrf
      @method('PUT')

      <div>
        <label class="block text-sm font-medium">Nombre</label>
        <input name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 border rounded" />
      </div>

      <div>
        <label class="block text-sm font-medium">Email</label>
        <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 border rounded" />
      </div>

      <div>
        <label class="block text-sm font-medium">Jerarquía (nivel)</label>
        <input name="hierarchy_level" type="number" value="{{ old('hierarchy_level', $user->hierarchy_level) }}" class="w-full px-3 py-2 border rounded" />
        <p class="text-xs text-gray-500 mt-1">-1 master, 0 company, 1 admin, 2 user</p>
      </div>

      <div class="flex items-center gap-3">
        <input type="checkbox" id="is_active" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} />
        <label for="is_active" class="text-sm">Activo</label>
      </div>

      <div class="flex gap-2">
        <button class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95 transition-all duration-200" type="submit">Guardar</button>
        <a href="{{ route('master.users.show', $user) }}" class="px-4 py-2 bg-gray-200 rounded">Cancelar</a>
      </div>
    </form>

    <hr class="my-6">

    <h3 id="reset" class="text-lg font-semibold mb-3">Resetear contraseña</h3>
    <form action="{{ route('master.users.resetPassword', $user) }}" method="POST" class="space-y-3">
      @csrf
      <div>
        <label class="block text-sm font-medium">Nueva contraseña</label>
        <input name="password" type="password" class="w-full px-3 py-2 border rounded" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Confirmar nueva contraseña</label>
        <input name="password_confirmation" type="password" class="w-full px-3 py-2 border rounded" required />
      </div>

      <div>
        <button class="px-4 py-2 bg-orange-600 text-white rounded" type="submit">Resetear contraseña</button>
      </div>
    </form>
  </div>
</div>
@endsection
