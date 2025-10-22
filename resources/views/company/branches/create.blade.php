@extends('layouts.app')

@section('title', 'Crear Sucursal')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold mb-2">Crear nueva sucursal</h1>
        <p class="text-gray-600">La sucursal se creará con credenciales de acceso para poder iniciar sesión en el sistema.</p>
    </div>

    {{-- Incluir el componente Livewire --}}
    @livewire('company.branch-create')
</div>
@endsection
