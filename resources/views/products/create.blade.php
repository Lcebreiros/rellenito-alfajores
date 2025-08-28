@extends('layouts.app')

@section('header')
  <h1 class="text-xl font-semibold text-gray-800 flex items-center">
    <i class="fas fa-cube mr-2 text-indigo-600"></i> Nuevo producto
  </h1>
@endsection

@section('content')
<div class="max-w-screen-md mx-auto px-3 sm:px-6"> {{-- Aumentado a max-w-screen-md --}}

  {{-- Mensajes --}}
  @if(session('ok'))
    <div class="mb-6 rounded-lg bg-green-50 text-green-800 px-4 py-3 text-sm flex items-center">
      <i class="fas fa-check-circle mr-2"></i> {{ session('ok') }}
    </div>
  @endif
  @if($errors->any())
    <div class="mb-6 rounded-lg bg-red-50 text-red-800 px-4 py-3 text-sm">
      <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <div>
          @foreach($errors->all() as $e) 
            <div>{{ $e }}</div> 
          @endforeach
        </div>
      </div>
    </div>
  @endif

  <form method="POST" action="{{ route('products.store') }}" class="space-y-6 bg-white rounded-xl shadow-sm p-6" enctype="multipart/form-data">
    @csrf

    {{-- Nombre --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
        <i class="fas fa-tag text-gray-500 mr-2 text-sm"></i> Nombre *
      </label>
      <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
             class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors"
             placeholder="Ej: Alfajor de maicena">
      <p class="mt-1 text-xs text-gray-500">{{ strlen(old('name', '')) }}/100 caracteres</p>
      @error('name') 
        <p class="mt-1 text-sm text-red-600 flex items-center">
          <i class="fas fa-exclamation-circle mr-1 text-xs"></i> {{ $message }}
        </p> 
      @enderror
    </div>

    {{-- SKU --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
        <i class="fas fa-barcode text-gray-500 mr-2 text-sm"></i> SKU *
      </label>
      <input type="text" name="sku" value="{{ old('sku') }}" required maxlength="50"
             class="w-full rounded-lg border-gray-300 uppercase tracking-wider focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors"
             placeholder="Ej: ALFA-MAI-01">
      <p class="mt-1 text-xs text-gray-500">{{ strlen(old('sku', '')) }}/50 caracteres</p>
      @error('sku') 
        <p class="mt-1 text-sm text-red-600 flex items-center">
          <i class="fas fa-exclamation-circle mr-1 text-xs"></i> {{ $message }}
        </p> 
      @enderror
    </div>

    {{-- Precio y Stock --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
          <i class="fas fa-dollar-sign text-gray-500 mr-2 text-sm"></i> Precio (ARS) *
        </label>
        <input type="number" name="price" value="{{ old('price') }}" min="0" step="0.01" required
               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors"
               placeholder="0,00">
        @error('price') 
          <p class="mt-1 text-sm text-red-600 flex items-center">
            <i class="fas fa-exclamation-circle mr-1 text-xs"></i> {{ $message }}
          </p> 
        @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
          <i class="fas fa-boxes text-gray-500 mr-2 text-sm"></i> Stock *
        </label>
        <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" step="1" required
               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2.5 transition-colors"
               placeholder="0">
        @error('stock') 
          <p class="mt-1 text-sm text-red-600 flex items-center">
            <i class="fas fa-exclamation-circle mr-1 text-xs"></i> {{ $message }}
          </p> 
        @enderror
      </div>
    </div>

    {{-- Foto (opcional) --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
        <i class="fas fa-camera text-gray-500 mr-2 text-sm"></i> Foto (opcional)
      </label>
      <div class="flex items-center justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-lg">
        <div class="space-y-1 text-center">
          <div class="flex text-sm text-gray-600">
            <label class="relative cursor-pointer rounded-md font-medium text-indigo-600 hover:text-indigo-500">
              <span>Subir una imagen</span>
              <input type="file" name="photo" accept="image/*" class="sr-only">
            </label>
          </div>
          <p class="text-xs text-gray-500">PNG, JPG, GIF hasta 2MB</p>
        </div>
      </div>
      @error('photo') 
        <p class="mt-1 text-sm text-red-600 flex items-center">
          <i class="fas fa-exclamation-circle mr-1 text-xs"></i> {{ $message }}
        </p> 
      @enderror
    </div>

    {{-- Activo --}}
    <div class="flex items-center justify-between rounded-lg bg-gray-50 p-4">
      <div>
        <label for="is_active" class="text-sm font-medium text-gray-700">Activo</label>
        <p class="text-xs text-gray-500">Habilita el producto para que aparezca en listados.</p>
      </div>
      <label class="inline-flex items-center cursor-pointer">
        <input id="is_active" type="checkbox" name="is_active" value="1"
               class="sr-only peer" {{ old('is_active', true) ? 'checked' : '' }}>
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:bg-indigo-600 transition-all duration-300 relative">
          <span class="absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full transition-all duration-300 peer-checked:translate-x-5"></span>
        </div>
      </label>
    </div>
    @error('is_active') 
      <p class="mt-1 text-sm text-red-600 flex items-center">
        <i class="fas fa-exclamation-circle mr-1 text-xs"></i> {{ $message }}
      </p> 
    @enderror

    {{-- Acciones --}}
    <div class="pt-4 flex flex-col-reverse sm:flex-row sm:justify-between sm:items-center gap-3 border-t border-gray-100 mt-4">
      <a href="{{ route('products.index') }}"
         class="rounded-lg border border-gray-300 px-5 py-2.5 text-gray-700 hover:bg-gray-50 text-center font-medium transition-colors flex items-center justify-center">
        <i class="fas fa-arrow-left mr-2"></i> Cancelar
      </a>
      <button type="submit"
              class="rounded-lg bg-indigo-600 px-5 py-2.5 text-white hover:bg-indigo-700 font-medium transition-colors flex items-center justify-center">
        <i class="fas fa-save mr-2"></i> Guardar
      </button>
    </div>
  </form>
</div>

<style>
  /* Estilos para mejorar la apariencia */
  .fa-xs {
    font-size: 0.75rem;
  }
</style>

<!-- Incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
  // Script para mejorar la experiencia de usuario
  document.addEventListener('DOMContentLoaded', function() {
    // Contador de caracteres para el campo nombre
    const nameInput = document.querySelector('input[name="name"]');
    const nameCounter = document.querySelector('div:has(> input[name="name"]) + p');
    
    if (nameInput && nameCounter) {
      nameInput.addEventListener('input', function() {
        nameCounter.textContent = `${this.value.length}/100 caracteres`;
      });
    }
    
    // Contador de caracteres para el campo SKU
    const skuInput = document.querySelector('input[name="sku"]');
    const skuCounter = document.querySelector('div:has(> input[name="sku"]) + p');
    
    if (skuInput && skuCounter) {
      skuInput.addEventListener('input', function() {
        skuCounter.textContent = `${this.value.length}/50 caracteres`;
      });
    }
    
    // Mejorar el estilo del checkbox
    const toggleCheckbox = document.querySelector('input[name="is_active"]');
    if (toggleCheckbox) {
      toggleCheckbox.addEventListener('change', function() {
        const toggleDiv = this.nextElementSibling;
        if (this.checked) {
          toggleDiv.classList.remove('bg-gray-200');
          toggleDiv.classList.add('bg-indigo-600');
        } else {
          toggleDiv.classList.remove('bg-indigo-600');
          toggleDiv.classList.add('bg-gray-200');
        }
      });
      
      // Asegurar que el estado inicial es correcto
      if (toggleCheckbox.checked) {
        const toggleDiv = toggleCheckbox.nextElementSibling;
        toggleDiv.classList.remove('bg-gray-200');
        toggleDiv.classList.add('bg-indigo-600');
      }
    }
  });
</script>
@endsection