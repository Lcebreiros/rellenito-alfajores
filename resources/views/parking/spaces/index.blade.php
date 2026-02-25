@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">Cocheras</h1>
      <p class="text-sm text-neutral-600 dark:text-neutral-400">Administra disponibilidad, categorías y tarifas.</p>
    </div>
  </div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-3 sm:px-6 space-y-4">
  @if(session('ok'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif
  @if($errors->any())
    <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 container-glass shadow-sm overflow-hidden">
      <div class="px-4 sm:px-6 py-3 bg-neutral-100/70 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-700">
        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Agregar cochera</h2>
      </div>
      <form method="POST" action="{{ route('parking.spaces.store') }}" class="p-4 sm:p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Nombre</label>
            <input name="name" required maxlength="100" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm" placeholder="Cochera 1">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Código</label>
            <input name="code" maxlength="50" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm" placeholder="A-01">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Categoría</label>
            <select name="category_id" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
              <option value="">Sin categoría</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Estado</label>
            <select name="status" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
              <option value="disponible">Disponible</option>
              <option value="ocupada">Ocupada</option>
              <option value="alquilada">Alquilada</option>
              <option value="mantenimiento">Mantenimiento</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Uso</label>
            <select name="usage" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
              <option value="horaria">Hora / 12h / 24h</option>
              <option value="mensual">Solo mensual</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Tarifa (parking)</label>
            <select name="rate_id" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
              <option value="">Sin tarifa</option>
              @foreach($rates as $rate)
                <option value="{{ $rate->id }}">{{ $rate->name }} @if($rate->vehicle_type) ({{ $rate->vehicle_type }}) @endif</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Servicio asociado</label>
            <select name="service_id" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
              <option value="">Sin servicio</option>
              @foreach($services as $service)
                <option value="{{ $service->id }}">{{ $service->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Notas</label>
          <textarea name="notes" rows="2" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm" placeholder="Ej: techada, cerca del acceso..."></textarea>
        </div>
        <div class="flex justify-end">
          <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 text-sm font-semibold transition">
            Guardar
          </button>
        </div>
      </form>
    </div>

    <div class="container-glass shadow-sm overflow-hidden">
      <div class="px-4 sm:px-6 py-3 bg-neutral-100/70 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-700">
        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Categorías</h2>
      </div>
      <form method="POST" action="{{ route('parking.space-categories.store') }}" class="p-4 sm:p-6 space-y-3">
        @csrf
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Nueva categoría</label>
          <input name="name" required maxlength="100" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm" placeholder="Ej: Cubierta, Descubierta, Moto">
        </div>
        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-neutral-900 hover:bg-neutral-800 text-white px-4 py-2 text-sm font-semibold transition">
          Agregar
        </button>
        @if($categories->count())
          <div class="pt-2">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-400 mb-2">Existentes</h3>
            <div class="flex flex-wrap gap-2">
              @foreach($categories as $category)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                  {{ $category->name }}
                </span>
              @endforeach
            </div>
          </div>
        @endif
      </form>
    </div>
  </div>

  <div class="container-glass shadow-sm overflow-hidden">
    <div class="px-4 sm:px-6 py-3 bg-neutral-100/70 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-700">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Cocheras</h2>
          <p class="text-xs text-neutral-600 dark:text-neutral-400">Disponibilidad y servicio asociado.</p>
        </div>
      </div>
    </div>
    <div class="p-4 sm:p-6">
      @if($spaces->isEmpty())
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Aún no cargaste cocheras.</p>
      @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          @foreach($spaces as $space)
            <div class="border border-neutral-200 dark:border-neutral-800 rounded-xl p-4 bg-white/80 dark:bg-neutral-900/60 shadow-sm">
              <div class="flex items-start justify-between gap-3">
                <div>
                <div class="flex items-center gap-2">
                  <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">{{ $space->name }}</h3>
                  @if($space->code)
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">{{ $space->code }}</span>
                  @endif
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                    @if($space->usage === 'mensual') bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-100
                    @else bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-100 @endif">
                    {{ $space->usage === 'mensual' ? 'Solo mensual' : 'Horaria/diaria' }}
                  </span>
                </div>
                <p class="text-xs text-neutral-600 dark:text-neutral-400">
                  {{ $space->category?->name ?? 'Sin categoría' }}
                </p>
              </div>
                <span class="px-2 py-1 rounded-full text-[11px] font-semibold
                  @if($space->status === 'disponible') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200
                  @elseif($space->status === 'ocupada') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200
                  @elseif($space->status === 'alquilada') bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-200
                  @else bg-neutral-200 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200 @endif">
                  {{ ucfirst($space->status) }}
                </span>
              </div>

              <div class="mt-3 space-y-1 text-sm text-neutral-700 dark:text-neutral-200">
                <div class="flex items-center gap-2">
                  <span class="text-xs uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Tarifa:</span>
                  <span>{{ $space->rate?->name ? $space->rate->name . ($space->rate->vehicle_type ? ' ('.$space->rate->vehicle_type.')' : '') : 'Sin tarifa' }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Servicio:</span>
                  <span>{{ $space->service?->name ?? 'Sin servicio' }}</span>
                </div>
                @if($space->notes)
                  <div class="text-xs text-neutral-600 dark:text-neutral-300">Notas: {{ $space->notes }}</div>
                @endif
              </div>

              <form method="POST" action="{{ route('parking.spaces.update', $space) }}" class="mt-4 space-y-3">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nombre</label>
                    <input name="name" value="{{ $space->name }}" required maxlength="100" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Código</label>
                    <input name="code" value="{{ $space->code }}" maxlength="50" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Categoría</label>
                    <select name="category_id" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                      <option value="">Sin categoría</option>
                      @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected($space->category_id === $category->id)>{{ $category->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Estado</label>
                    <select name="status" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                      <option value="disponible" @selected($space->status === 'disponible')>Disponible</option>
                      <option value="ocupada" @selected($space->status === 'ocupada')>Ocupada</option>
                      <option value="alquilada" @selected($space->status === 'alquilada')>Alquilada</option>
                      <option value="mantenimiento" @selected($space->status === 'mantenimiento')>Mantenimiento</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Uso</label>
                    <select name="usage" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                      <option value="horaria" @selected($space->usage === 'horaria')>Hora / 12h / 24h</option>
                      <option value="mensual" @selected($space->usage === 'mensual')>Solo mensual</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tarifa</label>
                    <select name="rate_id" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                      <option value="">Sin tarifa</option>
                      @foreach($rates as $rate)
                        <option value="{{ $rate->id }}" @selected($space->rate_id === $rate->id)>{{ $rate->name }} @if($rate->vehicle_type) ({{ $rate->vehicle_type }}) @endif</option>
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Servicio</label>
                    <select name="service_id" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                      <option value="">Sin servicio</option>
                      @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected($space->service_id === $service->id)>{{ $service->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Notas</label>
                  <textarea name="notes" rows="2" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">{{ $space->notes }}</textarea>
                </div>
                <div class="flex items-center gap-2">
                  <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-neutral-900 hover:bg-neutral-800 text-white px-4 py-2 text-sm font-semibold transition">
                    Actualizar
                  </button>
                </div>
              </form>
              <form method="POST" action="{{ route('parking.spaces.destroy', $space) }}" class="mt-2" onsubmit="return confirm('¿Eliminar cochera?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-rose-600 hover:text-rose-700 font-semibold">Eliminar</button>
              </form>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
