@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Editar servicio</h1>
@endsection

@section('content')
@php
  $initialTags = old('tags', $service->tags ? implode(',', $service->tags) : '');
  $initialVariants = old('variants', $service->variants->map(function ($v) {
    return [
      'name' => $v->name,
      'duration_minutes' => $v->duration_minutes,
      'price' => (string) $v->price,
      'description' => $v->description,
      'is_active' => $v->is_active,
    ];
  })->toArray());
@endphp
<div class="max-w-3xl mx-auto px-3 sm:px-6 py-6"
     x-data="serviceForm(@js(['tags' => $initialTags, 'variants' => $initialVariants]))">
  <form method="POST" action="{{ route('services.update', $service) }}" class="space-y-5">
    @csrf
    @method('PUT')

    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
      <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-neutral-800 dark:to-neutral-800 px-6 sm:px-8 py-5 border-b border-neutral-200 dark:border-neutral-700">
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Información del servicio</h2>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">Edita los datos generales, variantes y tags.</p>
      </div>

      <div class="p-6 sm:p-8 space-y-6">
        <div class="group">
          <label class="block text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-2.5">Nombre</label>
          <input name="name" value="{{ old('name', $service->name) }}" required maxlength="150"
                 class="w-full rounded-xl border-2 border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 placeholder:text-neutral-400
                        hover:border-neutral-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none
                        dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder:text-neutral-500
                        dark:hover:border-neutral-600 transition-all duration-200" />
          @error('name') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="group">
          <label class="block text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-2.5">Descripción</label>
          <textarea name="description" rows="4"
                    class="w-full rounded-xl border-2 border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 placeholder:text-neutral-400
                           hover:border-neutral-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none resize-none
                           dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder:text-neutral-500
                           dark:hover:border-neutral-600 transition-all duration-200">{{ old('description', $service->description) }}</textarea>
          @error('description') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Precio base (ARS)</label>
            <input name="price" type="number" min="0" step="0.01" value="{{ old('price', (string)$service->price) }}" required
                   class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-right
                          text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                          dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500" />
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Usado si no se selecciona variante.</p>
            @error('price') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-neutral-50 p-4
                      dark:border-neutral-800 dark:bg-neutral-950/40">
            <div>
              <label for="is_active" class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Activo</label>
              <p class="text-xs text-neutral-500 dark:text-neutral-400">Habilita el servicio en listados.</p>
            </div>
            <label class="inline-flex items-center">
              <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}
                     class="peer sr-only">
              <span class="relative h-6 w-11 rounded-full bg-neutral-300 transition-colors duration-300
                           after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-transform
                           peer-checked:bg-indigo-600 peer-checked:after:translate-x-5
                           dark:bg-neutral-700 dark:peer-checked:bg-indigo-500"></span>
            </label>
          </div>
        </div>

        {{-- Categoría de servicio (opcional) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-2.5">
              Categoría (opcional)
            </label>
            <select name="service_category_id"
                    class="w-full rounded-xl border-2 border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900
                           hover:border-neutral-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none
                           dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:hover:border-neutral-600">
              <option value="">Sin categoría</option>
              @foreach(($categories ?? collect()) as $category)
                <option value="{{ $category->id }}" @selected(old('service_category_id', $service->service_category_id) == $category->id)>{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-2.5">
              Crear categoría nueva (opcional)
            </label>
            <input name="new_category" type="text" maxlength="100"
                   placeholder="Ej: Cocheras, Spa, Mantenimiento"
                   class="w-full rounded-xl border-2 border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 placeholder:text-neutral-400
                          hover:border-neutral-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none
                          dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder:text-neutral-500
                          dark:hover:border-neutral-600 transition-all duration-200">
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Si completas este campo, creará la categoría y la asignará.</p>
          </div>
        </div>

        <div class="group">
          <label class="block text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-2.5">
            Categorías / Tags <span class="text-xs text-neutral-500 dark:text-neutral-400 font-normal ml-1.5">(opcional, separa con coma)</span>
          </label>
          <input name="tags" type="text" x-model="tags"
                 class="w-full rounded-xl border-2 border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 placeholder:text-neutral-400
                        hover:border-neutral-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none
                        dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder:text-neutral-500
                        dark:hover:border-neutral-600 transition-all duration-200"
                 placeholder="spa, barbería, mantenimiento">
        </div>

        {{-- Variantes --}}
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Variantes del servicio</h3>
              <p class="text-xs text-neutral-500 dark:text-neutral-400">Duraciones/paquetes con precios diferenciados.</p>
            </div>
            <button type="button" @click="addVariant()"
                    class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 dark:border-neutral-700 px-3 py-2 text-xs font-semibold text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14m-7-7h14" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Añadir variante
            </button>
          </div>

          <template x-if="variants.length === 0">
            <p class="text-xs text-neutral-500 dark:text-neutral-400 border border-dashed border-neutral-200 dark:border-neutral-700 rounded-lg px-3 py-3">
              Sin variantes. Se usará el precio base.
            </p>
          </template>

          <div class="space-y-3">
            <template x-for="(variant, idx) in variants" :key="idx">
              <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50/80 dark:bg-neutral-900/40 p-4 space-y-2">
                <div class="flex items-start gap-3">
                  <div class="flex-1 space-y-2">
                    <input type="text" :name="`variants[${idx}][name]`" x-model="variant.name" placeholder="Premium 60 min"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-3 py-2 text-sm"
                           required>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                      <input type="number" min="0" step="1" :name="`variants[${idx}][duration_minutes]`" x-model="variant.duration_minutes"
                             placeholder="Duración (min)"
                             class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-3 py-2 text-sm">
                      <input type="number" min="0" step="0.01" :name="`variants[${idx}][price]`" x-model="variant.price"
                             placeholder="Precio"
                             class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-3 py-2 text-sm" required>
                      <label class="inline-flex items-center gap-2 text-xs font-semibold text-neutral-700 dark:text-neutral-200">
                        <input type="checkbox" :name="`variants[${idx}][is_active]`" value="1" x-model="variant.is_active"
                               class="rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500 dark:border-neutral-700">
                        Activa
                      </label>
                    </div>
                    <textarea :name="`variants[${idx}][description]`" x-model="variant.description" rows="2"
                              placeholder="Detalle del paquete/duración"
                              class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-950 px-3 py-2 text-sm resize-none"></textarea>
                  </div>
                  <button type="button" @click="removeVariant(idx)"
                          class="text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                </div>
              </div>
            </template>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-4 border-t border-neutral-100 dark:border-neutral-700">
          <a href="{{ route('services.index') }}" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold
                 text-neutral-700 bg-white border-2 border-neutral-300
                 hover:bg-neutral-50 hover:border-neutral-400 active:bg-neutral-100 active:scale-[0.98]
                 dark:text-neutral-300 dark:bg-neutral-800 dark:border-neutral-600 
                 dark:hover:bg-neutral-750 dark:hover:border-neutral-500
                 focus:outline-none focus:ring-4 focus:ring-neutral-500/10 
                 transition-all duration-200 shadow-sm">
            Cancelar
          </a>
          <button class="group/btn relative flex-1 sm:flex-auto inline-flex items-center justify-center gap-2.5 rounded-xl 
                 px-8 py-4 text-base font-bold text-white overflow-hidden
                 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 dark:focus:ring-indigo-400/40
                 active:scale-[0.98] transition-all duration-200
                 bg-gradient-to-r from-indigo-600 via-indigo-600 to-purple-600
                 hover:from-indigo-700 hover:via-indigo-700 hover:to-purple-700
                 dark:from-indigo-500 dark:via-indigo-600 dark:to-purple-600
                 dark:hover:from-indigo-600 dark:hover:via-indigo-700 dark:hover:to-purple-700
                 shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40
                 dark:shadow-indigo-500/20 dark:hover:shadow-indigo-500/30">
            <div class="absolute inset-0 -translate-x-full group-hover/btn:translate-x-full transition-transform duration-1000 ease-in-out
                      bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
            <div class="relative flex items-center gap-2.5">
              <span>Guardar</span>
            </div>
          </button>
        </div>
      </div>
    </div>
  </form>

  {{-- Gestión de Insumos --}}
  <div class="mt-6">
    @livewire('service-supplies-manager', ['service' => $service])
  </div>

</div>

<script>
  function serviceForm(initial) {
    return {
      tags: initial.tags || '',
      variants: Array.isArray(initial.variants) ? initial.variants : [],
      addVariant() {
        this.variants.push({ name: '', duration_minutes: '', price: '', description: '', is_active: true });
      },
      removeVariant(idx) {
        this.variants.splice(idx, 1);
      },
    };
  }
</script>
@endsection
