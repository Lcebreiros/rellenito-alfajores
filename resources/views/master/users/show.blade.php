@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold">Usuario #{{ $user->id }}</h1>
@endsection

@section('content')
<div class="container mx-auto">
  <div class="bg-white shadow rounded p-6">
    <div class="flex gap-6">
      <div class="w-48">
        <img src="{{ $user->profile_photo_url ?? asset('images/default-avatar.png') }}" alt="avatar" class="rounded w-48 h-48 object-cover">
      </div>
      <div class="flex-1">
        <h2 class="text-xl font-semibold">{{ $user->name }}</h2>
        <p class="text-sm text-gray-600">{{ $user->email }}</p>
        <p class="mt-3"><strong>Jerarquía:</strong> {{ $user->hierarchy_level }}</p>
        <p><strong>Activo:</strong> {{ $user->is_active ? 'Sí' : 'No' }}</p>
        <p><strong>Creado:</strong> {{ $user->created_at->toDayDateTimeString() }}</p>

        <div class="mt-4 space-x-2">
          <a href="{{ route('master.users.edit', $user) }}" class="btn px-3 py-2 bg-indigo-600 text-white rounded">Editar</a>
          <a href="{{ route('master.users.index') }}" class="btn px-3 py-2 bg-gray-200 rounded">Volver</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
