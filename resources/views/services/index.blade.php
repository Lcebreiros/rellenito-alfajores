@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Servicios</h1>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6">

  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm
                dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm
                dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center gap-2 text-sm">
      <span class="inline-flex items-center rounded-lg border border-neutral-200 bg-white px-2.5 py-1.5 text-neutral-600
                    dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300">
        Mostrando {{ $services->firstItem() }}–{{ $services->lastItem() }} de {{ $services->total() }}
      </span>
    </div>

    <div class="flex items-center gap-2">
      <form method="GET" class="hidden sm:flex items-center gap-2">
        <div class="relative">
          <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
            <path d="M21 21l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <input name="q" value="{{ request('q') }}" placeholder="Buscar servicio…"
                 class="w-60 rounded-lg border border-neutral-300 bg-white pl-9 pr-3 py-2 text-sm text-neutral-700
                        placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:placeholder:text-neutral-400">
        </div>
        <button class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
          Buscar
        </button>
      </form>

      <a href="{{ route('services.create') }}"
         class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Nuevo servicio
      </a>
    </div>
  </div>

  @if($services->count())
    <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      @foreach($services as $service)
        @php
          $priceLabel = '$ ' . number_format((float) $service->price, 2, ',', '.');
          $isActive = (bool)($service->is_active ?? true);
        @endphp

        <div class="group overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm
                    hover:shadow-md hover:border-indigo-200 transition
                    dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-indigo-500/50">
          <div class="p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h2 class="text-sm sm:text-base font-medium text-neutral-900 dark:text-neutral-100 line-clamp-2">
                  {{ $service->name }}
                </h2>
                @if($service->description)
                <div class="mt-0.5 text-[12px] text-neutral-500 dark:text-neutral-400 line-clamp-2">
                  {{ $service->description }}
                </div>
                @endif
              </div>
              <div class="shrink-0 text-[11px] sm:text-xs rounded-full px-2 py-0.5 font-medium
                           {{ $isActive 
                              ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' 
                              : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
                {{ $isActive ? 'Activo' : 'Inactivo' }}
              </div>
            </div>

            <div class="mt-3 flex items-center justify-between gap-2">
              <span class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
                {{ $priceLabel }}
              </span>

              <div class="flex items-center gap-2">
                <a href="{{ route('services.edit', $service) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-1.5 text-sm font-medium text-neutral-700 hover:bg-neutral-50
                          dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 whitespace-nowrap">
                  Editar
                </a>

                <form action="{{ route('services.destroy', $service) }}" method="POST" onsubmit="return confirm('¿Eliminar servicio?')">
                  @csrf
                  @method('DELETE')
                  <button class="inline-flex items-center gap-2 rounded-lg border border-rose-300 px-3 py-1.5 text-sm font-medium text-rose-700 hover:bg-rose-50
                                 dark:border-rose-700 dark:text-rose-300 dark:hover:bg-rose-900/30 whitespace-nowrap">
                    Eliminar
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-6">
      {{ $services->withQueryString()->links() }}
    </div>
  @else
    <div class="text-center py-16">
      <p class="text-neutral-600 dark:text-neutral-400">Aún no hay servicios.</p>
    </div>
  @endif
</div>
@endsection

