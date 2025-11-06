@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Métodos de Pago (Globales)</h1>
    <a href="{{ route('payment-methods.create') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
      <x-heroicon-o-plus class="w-4 h-4" /> Nuevo método
    </a>
  </div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-3 sm:px-6">
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

  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-800 overflow-hidden">
    <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
      <div class="text-sm text-neutral-600 dark:text-neutral-300">
        Administrá los métodos globales disponibles para todos los usuarios.
      </div>
    </div>

    <div class="divide-y divide-neutral-200 dark:divide-neutral-800">
      @forelse($paymentMethods as $pm)
        <div class="p-4 flex items-start gap-4">
          <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-neutral-100 dark:bg-neutral-800 grid place-items-center">
            <x-dynamic-component :component="'heroicon-o-' . $pm->getIcon()" class="w-6 h-6 text-neutral-600 dark:text-neutral-400" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
              <div class="font-semibold text-neutral-900 dark:text-neutral-100 truncate">{{ $pm->name }}</div>
              @if($pm->requires_gateway)
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                  <x-heroicon-s-link class="w-2.5 h-2.5" /> Automático
                </span>
              @endif
              @if($pm->is_active)
                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Activo</span>
              @else
                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] rounded-full bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">Inactivo</span>
              @endif
            </div>
            @if($pm->description)
              <div class="text-sm text-neutral-600 dark:text-neutral-400 mt-0.5">{{ $pm->description }}</div>
            @endif
            <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">slug: <code>{{ $pm->slug }}</code></div>
          </div>
          <div class="flex items-center gap-2">
            <form action="{{ route('payment-methods.toggle', $pm) }}" method="POST">
              @csrf
              <button type="submit"
                      class="px-3 py-1.5 rounded-lg text-sm {{ $pm->is_active ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-100 hover:bg-neutral-300 dark:hover:bg-neutral-600' }}">
                {{ $pm->is_active ? 'Desactivar' : 'Activar' }}
              </button>
            </form>

            <a href="{{ route('payment-methods.edit', $pm) }}"
               class="px-3 py-1.5 rounded-lg text-sm border border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800">
              Editar
            </a>
          </div>
        </div>
      @empty
        <div class="p-8 text-center">
          <x-heroicon-o-credit-card class="w-14 h-14 mx-auto text-neutral-300 dark:text-neutral-700 mb-3" />
          <div class="text-neutral-700 dark:text-neutral-300">No hay métodos globales aún.</div>
          <div class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Podés crearlos con el botón “Nuevo método”.</div>
        </div>
      @endforelse
    </div>
  </div>
</div>
@endsection

