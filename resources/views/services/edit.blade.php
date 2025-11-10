@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Editar servicio</h1>
@endsection

@section('content')
<div class="max-w-2xl mx-auto px-3 sm:px-6">
  <form method="POST" action="{{ route('services.update', $service) }}" class="space-y-4">
    @csrf
    @method('PUT')

    <div>
      <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Nombre</label>
      <input name="name" value="{{ old('name', $service->name) }}" required maxlength="150"
             class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-800
                    focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100" />
      @error('name') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Descripción (opcional)</label>
      <textarea name="description" rows="3"
                class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-800
                       focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">{{ old('description', $service->description) }}</textarea>
      @error('description') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Precio</label>
      <input name="price" type="number" min="0" step="0.01" value="{{ old('price', (string)$service->price) }}" required
             class="mt-1 w-40 rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-800
                    focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100" />
      @error('price') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-2">
      <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}
             class="rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500 dark:border-neutral-700" />
      <label for="is_active" class="text-sm text-neutral-700 dark:text-neutral-300">Activo</label>
    </div>

    <div class="pt-2">
      <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Guardar</button>
      <a href="{{ route('services.index') }}" class="ml-2 text-sm text-neutral-600 hover:underline dark:text-neutral-300">Cancelar</a>
    </div>
  </form>

  {{-- Gestión de Insumos --}}
  <div class="mt-6">
    @livewire('service-supplies-manager', ['service' => $service])
  </div>

</div>
@endsection

