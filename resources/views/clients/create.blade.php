@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Nuevo cliente</h1>
@endsection

@section('content')
<div class="max-w-3xl mx-auto px-3 sm:px-6">
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800">
    <form method="POST" action="{{ route('clients.store') }}" class="p-6 space-y-5">
      @csrf

      @if($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
          @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
      @endif

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Nombre *</label>
          <input name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Contacto</label>
          <input name="phone" value="{{ old('phone') }}" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">DNI (opcional)</label>
          <input name="document_number" value="{{ old('document_number') }}" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Ciudad</label>
          <input name="city" value="{{ old('city') }}" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Provincia</label>
          <input name="province" value="{{ old('province') }}" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">País</label>
          <input name="country" value="{{ old('country') }}" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Dirección</label>
        <input name="address" value="{{ old('address') }}" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Tags (separar por coma)</label>
        <input name="tags[]" value="" placeholder="vip, mayorista" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5"
               oninput="this.name='tags';">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Notas</label>
        <textarea name="notes" rows="4" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5">{{ old('notes') }}</textarea>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <a href="{{ route('clients.index') }}" class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200">Cancelar</a>
        <button class="px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95 transition-all duration-200">Guardar</button>
      </div>
    </form>
  </div>
</div>
@endsection

