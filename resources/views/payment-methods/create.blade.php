@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Nuevo método de pago</h1>
@endsection

@section('content')
<div class="max-w-3xl mx-auto px-3 sm:px-6">
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800">
    <form method="POST" action="{{ route('payment-methods.store') }}" class="p-6 space-y-5">
      @csrf

      @if($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
          @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
      @endif

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1 text-neutral-700 dark:text-neutral-300">Nombre *</label>
          <input name="name" value="{{ old('name') }}" required
                 placeholder="Ej: Efectivo, MercadoPago"
                 class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 px-3 py-2.5">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1 text-neutral-700 dark:text-neutral-300">Slug * <small class="text-neutral-500">(identificador único)</small></label>
          <input name="slug" value="{{ old('slug') }}" required
                 placeholder="Ej: efectivo, mercadopago"
                 class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 px-3 py-2.5 font-mono text-sm">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1 text-neutral-700 dark:text-neutral-300">Descripción</label>
        <textarea name="description" rows="2"
                  class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 px-3 py-2.5">{{ old('description') }}</textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1 text-neutral-700 dark:text-neutral-300">Icono <small class="text-neutral-500">(heroicon)</small></label>
          <input name="icon" value="{{ old('icon', 'currency-dollar') }}"
                 placeholder="Ej: banknotes, credit-card"
                 class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 px-3 py-2.5">
          <p class="text-xs text-neutral-500 mt-1">Ver iconos en <a href="https://heroicons.com" target="_blank" class="text-indigo-600 hover:underline">heroicons.com</a></p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1 text-neutral-700 dark:text-neutral-300">Orden</label>
          <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                 class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 px-3 py-2.5">
        </div>
      </div>

      <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4">
        <h3 class="text-sm font-semibold mb-3 text-neutral-700 dark:text-neutral-300">Integración de pasarela (opcional)</h3>

        <div class="space-y-3">
          <div class="flex items-center gap-2">
            <input type="checkbox" name="requires_gateway" id="requires_gateway" value="1"
                   {{ old('requires_gateway') ? 'checked' : '' }}
                   class="rounded border-neutral-300 dark:border-neutral-700 text-indigo-600 focus:ring-indigo-500">
            <label for="requires_gateway" class="text-sm text-neutral-700 dark:text-neutral-300">Requiere integración con API de pasarela</label>
          </div>

          <div id="gateway_fields" class="space-y-3 {{ old('requires_gateway') ? '' : 'hidden' }}">
            <div>
              <label class="block text-sm font-medium mb-1 text-neutral-700 dark:text-neutral-300">Proveedor de pasarela</label>
              <select name="gateway_provider"
                      class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 px-3 py-2.5">
                <option value="">Seleccionar...</option>
                <option value="mercadopago" {{ old('gateway_provider') === 'mercadopago' ? 'selected' : '' }}>MercadoPago</option>
                <option value="paypal" {{ old('gateway_provider') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                <option value="stripe" {{ old('gateway_provider') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                <option value="crypto" {{ old('gateway_provider') === 'crypto' ? 'selected' : '' }}>Criptomonedas</option>
                <option value="other" {{ old('gateway_provider') === 'other' ? 'selected' : '' }}>Otro</option>
              </select>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
              <p class="text-xs text-blue-800 dark:text-blue-300">
                <x-heroicon-s-information-circle class="w-4 h-4 inline" />
                La configuración de API keys se podrá agregar después de crear el método.
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1"
               {{ old('is_active', true) ? 'checked' : '' }}
               class="rounded border-neutral-300 dark:border-neutral-700 text-indigo-600 focus:ring-indigo-500">
        <label for="is_active" class="text-sm text-neutral-700 dark:text-neutral-300">Activar método de pago</label>
      </div>

      <div class="flex gap-2 pt-4">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
          Crear método de pago
        </button>
        <a href="{{ route('payment-methods.index') }}" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-200">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('requires_gateway').addEventListener('change', function() {
  const fields = document.getElementById('gateway_fields');
  if (this.checked) {
    fields.classList.remove('hidden');
  } else {
    fields.classList.add('hidden');
  }
});
</script>
@endsection
