@extends('layouts.app')

@section('title', 'Cuenta suspendida')

@section('content')
<div class="max-w-xl mx-auto p-6">
    <div class="bg-white dark:bg-neutral-950 rounded-2xl shadow p-6 text-center">
        <svg class="mx-auto mb-4 h-12 w-12 text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M12 9v4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 17h.01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <h1 class="text-2xl font-semibold mb-2">Cuenta suspendida</h1>
        <p class="text-sm text-gray-600 mb-4">
            Tu cuenta está temporalmente suspendida. {!! $reason ?? '' !!}
        </p>

        <div class="space-x-2">
            @if(!empty($appealUrl))
                <a href="{{ $appealUrl }}" class="inline-block px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50">
                    Enviar apelación
                </a>
            @endif

            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="inline-block px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200">
                Cerrar sesión
            </a>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>

        <p class="mt-4 text-xs text-gray-400">Si pensás que esto es un error, contactá a soporte.</p>
    </div>
</div>
@endsection
