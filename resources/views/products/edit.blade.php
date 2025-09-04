{{-- resources/views/products/edit.blade.php --}}
@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between gap-3">
    <div class="min-w-0">
      <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">
        Editar producto
      </h1>
      <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
        Actualiza datos, gestiona stock y elimina el producto si es necesario.
      </p>
    </div>
    <a href="{{ route('products.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50
              dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Volver
    </a>
  </div>
@endsection

@section('content')
<div
  x-data="deleteDialog()"
  class="max-w-5xl mx-auto px-3 sm:px-6 py-6"
>
  {{-- Flash --}}
  @if (session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm
                dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif
  @if (session('error'))
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm
                dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      {{ session('error') }}
    </div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm
                dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  {{-- Layout responsivo: en desktop dos columnas --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
    {{-- Columna principal --}}
    <div class="lg:col-span-2 space-y-6">
      {{-- Formulario principal --}}
      <div class="rounded-2xl bg-white dark:bg-neutral-900 ring-1 ring-neutral-200/70 dark:ring-neutral-800 shadow-sm">
        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-800">
          <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Datos del producto</h2>
        </div>

        <form action="{{ route('products.update', $product) }}" method="POST" class="px-5 py-5 space-y-5">
          @csrf
          @method('PUT')

          {{-- Nombre --}}
          <div>
            <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Nombre</label>
            <input
              id="name" name="name" type="text" required maxlength="100"
              value="{{ old('name', $product->name) }}"
              class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-neutral-800 placeholder-neutral-400
                     focus:border-indigo-500 focus:ring-indigo-500
                     dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder-neutral-500"
            >
          </div>

          {{-- SKU y Precio --}}
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="sku" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">SKU</label>
              <input
                id="sku" name="sku" type="text" required maxlength="50"
                value="{{ old('sku', $product->sku) }}"
                class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-neutral-800 placeholder-neutral-400
                       focus:border-indigo-500 focus:ring-indigo-500
                       dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder-neutral-500"
              >
            </div>

            <div>
              <label for="price" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Precio</label>
              <input
                id="price" name="price" type="number" step="0.01" min="0" required
                value="{{ old('price', $product->price) }}"
                class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-neutral-800 placeholder-neutral-400
                       focus:border-indigo-500 focus:ring-indigo-500
                       dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder-neutral-500"
              >
            </div>
          </div>

          {{-- Activo --}}
          <div class="flex items-center gap-3">
            <input
              id="is_active" name="is_active" type="checkbox" value="1"
              @checked(old('is_active', (bool)($product->is_active ?? true)))
              class="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-indigo-600
                     dark:border-neutral-700"
            >
            <label for="is_active" class="text-sm text-neutral-700 dark:text-neutral-300">
              Activo / visible en catálogo
            </label>
          </div>

          <div class="pt-2 flex items-center justify-end gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h14M12 5v14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Guardar cambios
            </button>
          </div>
        </form>
      </div>

      {{-- Zona de peligro: eliminar --}}
      <div class="rounded-2xl bg-white dark:bg-neutral-900 ring-1 ring-neutral-200/70 dark:ring-neutral-800 shadow-sm">
        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-800">
          <h2 class="text-base font-semibold text-rose-700 dark:text-rose-400">Eliminar producto</h2>
        </div>

        <div class="px-5 py-5">
          <p class="text-sm text-neutral-700 dark:text-neutral-300">
            Esta acción no se puede deshacer.
            @php
              $usesSoftDeletes = in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses($product));
            @endphp
            @if($usesSoftDeletes)
              (Se aplicará <span class="font-medium">borrado lógico</span>).
            @endif
          </p>

          <div class="mt-4 flex items-center justify-end">
            <button
              type="button"
              @click="openDel(@js($product->name), @js(route('products.destroy', $product)))"
              class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3 6h18M8 6l1 12a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2l1-12M10 6V4a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v2"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Eliminar producto
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- Columna lateral (stock) --}}
    <div class="space-y-6">
      <div class="rounded-2xl bg-white dark:bg-neutral-900 ring-1 ring-neutral-200/70 dark:ring-neutral-800 shadow-sm">
        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-800">
          <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Stock</h2>
        </div>

        <form action="{{ route('products.update-stock', $product) }}" method="POST" class="px-5 py-5 space-y-4">
          @csrf
          @method('PATCH')

          <div>
            <label for="stock" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">Cantidad</label>
            <input
              id="stock" name="stock" type="number" min="0" required
              value="{{ old('stock', (int)($product->stock ?? 0)) }}"
              class="mt-1 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-neutral-800 placeholder-neutral-400
                     focus:border-indigo-500 focus:ring-indigo-500
                     dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:placeholder-neutral-500"
            >
          </div>

          <div class="pt-2 flex items-center justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50
                           dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
              Actualizar stock
            </button>
          </div>
        </form>
      </div>

      {{-- Mini vista previa (opcional si usas photo_path) --}}
      @php
        $photo = isset($product->photo_path) && $product->photo_path
          ? \Illuminate\Support\Facades\Storage::url($product->photo_path)
          : null;
      @endphp
      <div class="rounded-2xl bg-white dark:bg-neutral-900 ring-1 ring-neutral-200/70 dark:ring-neutral-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-800">
          <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Vista previa</h2>
        </div>
        <div class="aspect-[4/3] bg-neutral-100 dark:bg-neutral-800">
          @if($photo)
            <img src="{{ $photo }}" alt="Foto de {{ $product->name }}" class="h-full w-full object-cover">
          @else
            <div class="h-full w-full grid place-items-center">
              <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-300" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M7 15l3-3 3 3 4-4 2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- MODAL ELIMINAR --}}
  <div
    x-cloak
    x-show="open"
    x-transition.opacity
    @keydown.escape.window="closeDel()"
    @click.self="closeDel()"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    aria-modal="true" role="dialog"
  >
    <div
      x-show="open"
      x-transition
      class="w-[92vw] sm:w-[520px] rounded-2xl bg-white dark:bg-neutral-900 ring-1 ring-neutral-200/70 dark:ring-neutral-800 shadow-2xl overflow-hidden"
    >
      <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-800">
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Confirmar eliminación</h3>
      </div>
      <div class="px-5 py-4">
        <p class="text-sm text-neutral-700 dark:text-neutral-300">
          ¿Seguro que querés eliminar
          <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="name"></span>?
          Esta acción no se puede deshacer.
        </p>
      </div>
      <div class="px-5 pb-5 flex items-center justify-end gap-2">
        <button type="button"
                @click="closeDel()"
                class="rounded-lg border border-neutral-300 dark:border-neutral-700 px-4 py-2 text-sm text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800">
          Cancelar
        </button>
        <form :action="url" method="POST" class="inline">
          @csrf
          @method('DELETE')
          <button type="submit"
                  class="rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 text-sm font-medium">
            Eliminar definitivamente
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function deleteDialog() {
    return {
      open: false,
      name: '',
      url: '#',
      openDel(name, url) {
        this.name = name ?? '';
        this.url = url ?? '#';
        this.open = true;
        document.documentElement.classList.add('overflow-hidden');
      },
      closeDel() {
        this.open = false;
        document.documentElement.classList.remove('overflow-hidden');
      }
    }
  }
</script>
@endpush
