@extends('layouts.app')

@section('title', 'Editar Sucursal')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white dark:bg-neutral-900 rounded-lg shadow-md border border-transparent dark:border-neutral-800 text-neutral-900 dark:text-neutral-100">
    <h1 class="text-2xl font-bold mb-6 text-neutral-900 dark:text-neutral-100">Editar Sucursal</h1>

    {{-- Errores --}}
    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-300 rounded">
            <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('company.branches.update', $branch->slug) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Nombre --}}
        <div>
            <label for="name" class="block font-semibold mb-1">Nombre</label>
            <input id="name" name="name" type="text"
                   value="{{ old('name', $branch->name) }}"
                   class="w-full rounded border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 p-2">
        </div>

        {{-- Slug (readonly) --}}
        <div>
            <label for="slug" class="block font-semibold mb-1">Slug</label>
            <input id="slug" name="slug" type="text"
                   value="{{ old('slug', $branch->slug) }}"
                   class="w-full rounded border-gray-200 dark:border-neutral-800 p-2 bg-gray-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-200" readonly>
            <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">El slug se usa para el binding en rutas. Cambiarlo puede afectar URLs públicas.</p>
        </div>

        {{-- Dirección --}}
        <div>
            <label for="address" class="block font-semibold mb-1">Dirección</label>
            <textarea id="address" name="address" rows="2" class="w-full rounded border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 p-2">{{ old('address', $branch->address) }}</textarea>
        </div>

        {{-- Teléfono --}}
        <div>
            <label for="phone" class="block font-semibold mb-1">Teléfono</label>
            <input id="phone" name="phone" type="text"
                   value="{{ old('phone', $branch->phone) }}"
                   class="w-full rounded border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 p-2">
        </div>

        {{-- Email de contacto --}}
        <div>
            <label for="contact_email" class="block font-semibold mb-1">Email de contacto</label>
            <input id="contact_email" name="contact_email" type="email"
                   value="{{ old('contact_email', $branch->contact_email) }}"
                   class="w-full rounded border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 p-2">
        </div>

        {{-- Logo actual / Subir nuevo logo --}}
        <div class="flex flex-col md:flex-row gap-4 items-start">
            <div class="flex-1">
                <label class="block font-semibold mb-1">Logo actual</label>

                @if ($branch->logoUrl())
                    <img src="{{ $branch->logoUrl() }}" alt="Logo" class="h-28 w-28 object-contain rounded border">
                @else
                    <div class="h-28 w-28 flex items-center justify-center bg-gray-100 dark:bg-neutral-800 rounded border border-gray-200 dark:border-neutral-700 text-gray-500 dark:text-neutral-400">
                        Sin logo
                    </div>
                @endif
            </div>

            <div class="flex-1">
                <label for="logo" class="block font-semibold mb-1">Cambiar logo (opcional)</label>
                <input id="logo" name="logo" type="file" accept="image/*" class="w-full">
                <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">Máx: recomendado 2MB. Se almacenará en el disco configurado (p.ej. public).</p>
                @if ($errors->has('logo'))
                    <p class="text-red-600 text-sm mt-1">{{ $errors->first('logo') }}</p>
                @endif
            </div>
        </div>

        {{-- Estado activo (del representante / de la sucursal) --}}
        <div class="flex items-center gap-3">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" name="is_active" type="checkbox" value="1"
                   {{ old('is_active', $branch->is_active ? '1' : '') ? 'checked' : '' }}
                   class="h-4 w-4">
            <label for="is_active" class="font-semibold">Sucursal activa</label>
        </div>

        <hr class="my-4 border-gray-200 dark:border-neutral-800">

        {{-- Datos del usuario representante (morphOne) --}}
        <h2 class="text-lg font-semibold">Usuario representante</h2>
        <p class="text-sm text-gray-500 dark:text-neutral-400 mb-3">Editar datos básicos del usuario que representa esta sucursal.</p>

        <div>
            <label for="rep_name" class="block font-semibold mb-1">Nombre del representante</label>
            <input id="rep_name" name="representative[name]" type="text"
                   value="{{ old('representative.name', optional($branch->user)->name) }}"
                   class="w-full rounded border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 p-2">
        </div>

        <div>
            <label for="rep_email" class="block font-semibold mb-1">Email de acceso</label>
            <input id="rep_email" name="representative[email]" type="email"
                   value="{{ old('representative.email', optional($branch->user)->email) }}"
                   class="w-full rounded border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 p-2">
            <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">Si cambias el email, el representante usará el nuevo email para acceder.</p>
        </div>

        {{-- Opcional: cambiar contraseña (si querés permitirlo) --}}
        <div>
            <label for="rep_password" class="block font-semibold mb-1">Nueva contraseña (opcional)</label>
            <input id="rep_password" name="representative[password]" type="password"
                   class="w-full rounded border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 p-2" autocomplete="new-password">
            <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">Dejar vacío para mantener la contraseña actual.</p>
        </div>

        {{-- Botones --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('company.branches.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-200 rounded-lg hover:bg-gray-300 dark:hover:bg-neutral-700">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95 transition-all duration-200">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection
