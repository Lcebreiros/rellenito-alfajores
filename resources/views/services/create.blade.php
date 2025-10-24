@extends('layouts.app')

@section('header')
  <div class="flex items-center gap-3">
    <a href="{{ route('services.index') }}" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Crear nuevo servicio</h1>
  </div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
  
  {{-- Card principal --}}
  <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
    
    {{-- Header de la card --}}
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-neutral-800 dark:to-neutral-800 px-6 sm:px-8 py-5 border-b border-neutral-200 dark:border-neutral-700">
      <div class="flex items-start gap-4">
        <div class="p-2.5 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl">
          <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Información del servicio</h2>
          <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-0.5">Complete los datos del servicio que desea ofrecer</p>
        </div>
      </div>
    </div>

    {{-- Formulario --}}
    <form method="POST" action="{{ route('services.store') }}" class="p-6 sm:p-8"
          x-data="{ price: '{{ old('price', '0') }}', isActive: {{ old('is_active', true) ? 'true' : 'false' }} }">
      @csrf

      <div class="space-y-7">
        
        {{-- Nombre --}}
        <div class="group">
          <label for="name" class="block text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-2.5">
            Nombre del servicio <span class="text-rose-500">*</span>
          </label>
          <input 
            id="name"
            name="name" 
            type="text"
            value="{{ old('name') }}" 
            required 
            maxlength="150"
            placeholder="Ej: Desarrollo web personalizado"
            class="w-full rounded-xl border-2 border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 placeholder:text-neutral-400
                   hover:border-neutral-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none
                   dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder:text-neutral-500
                   dark:hover:border-neutral-600 transition-all duration-200
                   @error('name') border-rose-300 dark:border-rose-700 focus:border-rose-500 focus:ring-rose-500/10 @enderror"
          />
          @error('name') 
            <div class="flex items-start gap-2 mt-2 text-rose-600 dark:text-rose-400">
              <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">{{ $message }}</span>
            </div>
          @enderror
        </div>

        {{-- Descripción --}}
        <div class="group">
          <label for="description" class="block text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-2.5">
            Descripción 
            <span class="text-xs text-neutral-500 dark:text-neutral-400 font-normal ml-1.5">(opcional)</span>
          </label>
          <textarea 
            id="description"
            name="description" 
            rows="5"
            placeholder="Describa los detalles, características y alcance del servicio..."
            class="w-full rounded-xl border-2 border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 placeholder:text-neutral-400
                   hover:border-neutral-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none resize-none
                   dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder:text-neutral-500
                   dark:hover:border-neutral-600 transition-all duration-200
                   @error('description') border-rose-300 dark:border-rose-700 focus:border-rose-500 focus:ring-rose-500/10 @enderror"
          >{{ old('description') }}</textarea>
          <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Una buena descripción ayuda a los clientes a entender mejor tu servicio
          </p>
          @error('description')
            <div class="flex items-start gap-2 mt-2 text-rose-600 dark:text-rose-400">
              <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm">{{ $message }}</span>
            </div>
          @enderror
        </div>

        {{-- Precio y Estado --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          
          {{-- Precio --}}
<div>
          <label for="price" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
            Precio (ARS) <span class="text-rose-600">*</span>
          </label>
          <input id="price" type="number" name="price" value="{{ old('price') }}" min="0" step="0.01" required
                 class="w-full rounded-lg border-neutral-300 bg-white px-4 py-2.5 text-right
                        text-neutral-900 placeholder:text-neutral-400 focus:border-indigo-500 focus:ring-indigo-500
                        dark:border-neutral-700 dark:bg-neutral-900/50 dark:text-neutral-100 dark:placeholder:text-neutral-500"
                 placeholder="0,00">
          @error('price')
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
          @enderror
        </div>

          {{-- Estado --}}
          <div class="group">
                  {{-- Activo --}}
      <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-neutral-50 p-4
                  dark:border-neutral-800 dark:bg-neutral-950/40">
        <div>
          <label for="is_active" class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Activo</label>
          <p class="text-xs text-neutral-500 dark:text-neutral-400">Habilita el producto para aparecer en listados.</p>
        </div>

        <label class="inline-flex items-center">
          <input id="is_active" type="checkbox" name="is_active" value="1" class="peer sr-only"
                 {{ old('is_active', true) ? 'checked' : '' }}>
          <span class="relative h-6 w-11 rounded-full bg-neutral-300 transition-colors duration-300
                       after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-transform
                       peer-checked:bg-indigo-600 peer-checked:after:translate-x-5
                       dark:bg-neutral-700 dark:peer-checked:bg-indigo-500"></span>
        </label>
      </div>
      @error('is_active')
        <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
      @enderror
            
              </label>
            </div>
          </div>
        </div>

      </div>

      {{-- Botones de acción --}}
      <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3 pt-8 mt-8 border-t-2 border-neutral-100 dark:border-neutral-700">
        <a 
          href="{{ route('services.index') }}" 
          class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold
                 text-neutral-700 bg-white border-2 border-neutral-300
                 hover:bg-neutral-50 hover:border-neutral-400 active:bg-neutral-100 active:scale-[0.98]
                 dark:text-neutral-300 dark:bg-neutral-800 dark:border-neutral-600 
                 dark:hover:bg-neutral-750 dark:hover:border-neutral-500
                 focus:outline-none focus:ring-4 focus:ring-neutral-500/10 
                 transition-all duration-200 shadow-sm"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
        <button 
          type="submit"
          class="group/btn relative flex-1 sm:flex-auto inline-flex items-center justify-center gap-2.5 rounded-xl 
                 px-8 py-4 text-base font-bold text-white overflow-hidden
                 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 dark:focus:ring-indigo-400/40
                 active:scale-[0.98] transition-all duration-200
                 bg-gradient-to-r from-indigo-600 via-indigo-600 to-purple-600
                 hover:from-indigo-700 hover:via-indigo-700 hover:to-purple-700
                 dark:from-indigo-500 dark:via-indigo-600 dark:to-purple-600
                 dark:hover:from-indigo-600 dark:hover:via-indigo-700 dark:hover:to-purple-700
                 shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40
                 dark:shadow-indigo-500/20 dark:hover:shadow-indigo-500/30"
        >
          {{-- Efecto de brillo animado --}}
          <div class="absolute inset-0 -translate-x-full group-hover/btn:translate-x-full transition-transform duration-1000 ease-in-out
                      bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
          
          {{-- Contenido del botón --}}
          <div class="relative flex items-center gap-2.5">
            <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="tracking-wide">Guardar servicio</span>
          </div>
          
          {{-- Indicador de éxito (opcional para futuras mejoras) --}}
          <div class="absolute inset-0 bg-emerald-500 rounded-xl scale-0 opacity-0 flex items-center justify-center
                      transition-all duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
        </button>
      </div>
    </form>

  </div>

  {{-- Tips/Ayuda opcional --}}
  <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-xl">
    <div class="flex gap-3">
      <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div class="text-sm text-blue-900 dark:text-blue-100">
        <p class="font-medium mb-1">Consejos para crear un buen servicio</p>
        <ul class="space-y-1 text-blue-800 dark:text-blue-200">
          <li>• Usa un nombre claro y descriptivo</li>
          <li>• Detalla qué incluye el servicio en la descripción</li>
          <li>• Asegúrate de que el precio refleje el valor del servicio</li>
        </ul>
      </div>
    </div>
  </div>

</div>
@endsection
