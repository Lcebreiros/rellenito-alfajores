@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="max-w-7xl mx-auto p-4">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Gestionar usuarios</h1>
            <p class="text-sm text-gray-500">Edita, suspende, reactiva o elimina usuarios de la plataforma.</p>
        </div>
    </div>

    {{-- Mensajes flash --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

{{-- Estadísticas básicas --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</div>
        <div class="text-sm text-gray-600">Total</div>
    </div>
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="text-2xl font-bold text-green-600">{{ $stats['active'] ?? 0 }}</div>
        <div class="text-sm text-gray-600">Activos</div>
    </div>
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="text-2xl font-bold text-yellow-600">{{ $stats['suspended'] ?? 0 }}</div>
        <div class="text-sm text-gray-600">Suspendidos</div>
    </div>
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="text-2xl font-bold text-red-600">{{ $stats['deleted'] ?? 0 }}</div>
        <div class="text-sm text-gray-600">Eliminados</div>
    </div>
</div>


    {{-- LISTADO --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium">Usuarios registrados</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ ucfirst($user->hierarchy_level ?? '—') }}</td>
                            <td class="px-6 py-4">
                                @if($user->is_suspended)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Suspendido</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 text-sm font-medium space-x-2">
                                <a href="{{ route('master.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                
<form action="{{ route('master.users.toggleActive', $user) }}" method="POST" class="inline">
    @csrf
    <button type="submit"
            onclick="return confirm('{{ $user->is_active ? '¿Suspender este usuario?' : '¿Reactivar este usuario?' }}')"
            class="{{ $user->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}">
        {{ $user->is_active ? 'Suspender' : 'Reactivar' }}
    </button>
</form>


                                <form action="{{ route('master.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                <div class="text-center">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay usuarios</h3>
                                    <p class="mt-1 text-sm text-gray-500">Comienza registrando usuarios o creando invitaciones.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
