@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Métodos de Pago</h1>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-6">
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
      {{ session('error') }}
    </div>
  @endif

  {{-- Descripción --}}
  <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
    <div class="flex items-start gap-3">
      <x-heroicon-o-information-circle class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
      <div class="text-sm text-blue-800 dark:text-blue-200">
        <p class="font-medium mb-1">Activa los métodos de pago que quieras ofrecer a tus clientes</p>
        <p class="text-blue-700 dark:text-blue-300">Solo necesitas activar o desactivar con un click. La configuración técnica ya está lista.</p>
      </div>
    </div>
  </div>

  {{-- Grid de métodos de pago --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @foreach($globalMethods as $method)
      @php
        $isActivated = in_array($method->id, $activatedMethodIds);
      @endphp

      <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-800 p-5 transition-all hover:shadow-md {{ $isActivated ? 'ring-2 ring-indigo-500 dark:ring-indigo-400' : '' }}">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                <x-dynamic-component :component="'heroicon-o-' . $method->getIcon()" class="w-6 h-6 text-neutral-600 dark:text-neutral-400" />
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-neutral-900 dark:text-neutral-100 truncate">{{ $method->name }}</h3>
                @if($method->requires_gateway)
                  <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                    <x-heroicon-s-link class="w-2.5 h-2.5" /> Automático
                  </span>
                @endif
              </div>
            </div>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 line-clamp-2">{{ $method->description }}</p>
          </div>

          <div class="flex-shrink-0">
            <form action="{{ route('payment-methods.toggle-global', $method) }}" method="POST" class="inline">
              @csrf
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox"
                       class="sr-only peer"
                       onchange="this.form.submit()"
                       {{ $isActivated ? 'checked' : '' }}>
                <div class="relative w-14 h-7 rounded-full transition-colors ease-in-out duration-200 border-2
                            {{ $isActivated ? 'bg-indigo-600 border-indigo-600' : 'bg-gray-200 dark:bg-neutral-700 border-gray-300 dark:border-neutral-600' }}">
                  <div class="absolute top-0.5 bg-white rounded-full h-5 w-5 transition-transform ease-in-out duration-200 shadow-lg"
                       style="transform: {{ $isActivated ? 'translateX(1.875rem)' : 'translateX(0.125rem)' }}"></div>
                </div>
              </label>
            </form>
          </div>
        </div>

        @if($isActivated)
          <div class="mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-800">
            <div class="flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400">
              <x-heroicon-s-check-circle class="w-4 h-4" />
              <span class="font-medium">Activo y disponible para tus clientes</span>
            </div>
          </div>
        @endif
      </div>
    @endforeach
  </div>

  @if($globalMethods->count() === 0)
    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-8">
      <div class="text-center py-8">
        <x-heroicon-o-credit-card class="w-16 h-16 mx-auto text-neutral-300 dark:text-neutral-700 mb-4" />
        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">No hay métodos de pago disponibles</h3>
        <p class="text-neutral-600 dark:text-neutral-400">Contacta al administrador para habilitar métodos de pago</p>
      </div>
    </div>
  @endif
</div>
@endsection
