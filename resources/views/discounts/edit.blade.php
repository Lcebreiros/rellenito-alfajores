@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ __('discounts.edit_title') }}</h1>
    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
      {{ __('discounts.edit_subtitle', ['name' => $discount->name]) }}
    </p>
  </div>

  <div class="container-glass shadow-sm overflow-hidden">
    <form method="POST" action="{{ route('discounts.update', $discount) }}">
      @csrf
      @method('PUT')

      <div class="p-6 space-y-6">
        <div>
          <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
            {{ __('discounts.field_name') }} <span class="text-rose-600">*</span>
          </label>
          <input type="text" id="name" name="name" value="{{ old('name', $discount->name) }}" required
                 class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                 placeholder="Ej: Restaurant La Esquina - Hora Gratis">
          @error('name')
            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
          @enderror
        </div>

        <div>
          <label for="code" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
            {{ __('discounts.field_code') }}
          </label>
          <input type="text" id="code" name="code" value="{{ old('code', $discount->code) }}"
                 class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm font-mono uppercase"
                 placeholder="Ej: REST-ESQUINA">
          <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
            {{ __('discounts.field_code_hint') }}
          </p>
          @error('code')
            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
          @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
              {{ __('discounts.field_type') }} <span class="text-rose-600">*</span>
            </label>
            <select id="type" name="type" required
                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                    onchange="updateValueLabel()">
              <option value="free_minutes" {{ old('type', $discount->type) === 'free_minutes' ? 'selected' : '' }}>{{ __('discounts.type_free_minutes_option') }}</option>
              <option value="percentage" {{ old('type', $discount->type) === 'percentage' ? 'selected' : '' }}>{{ __('discounts.type_percentage') }}</option>
              <option value="fixed_amount" {{ old('type', $discount->type) === 'fixed_amount' ? 'selected' : '' }}>{{ __('discounts.type_fixed_amount') }}</option>
            </select>
            @error('type')
              <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
            @enderror
          </div>

          <div>
            <label for="value" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
              <span id="value-label">{{ __('discounts.field_value') }}</span> <span class="text-rose-600">*</span>
            </label>
            <input type="number" id="value" name="value" value="{{ old('value', $discount->value) }}" required min="0" step="0.01"
                   class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1" id="value-hint">
              {{ __('discounts.js_hint_default') }}
            </p>
            @error('value')
              <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div>
          <label for="partner" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
            {{ __('discounts.field_partner') }}
          </label>
          <input type="text" id="partner" name="partner" value="{{ old('partner', $discount->partner) }}"
                 class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                 placeholder="Ej: Restaurant La Esquina">
          <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
            {{ __('discounts.field_partner_hint') }}
          </p>
          @error('partner')
            <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
          @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="starts_at" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
              {{ __('discounts.field_starts_at') }}
            </label>
            <input type="date" id="starts_at" name="starts_at" value="{{ old('starts_at', $discount->starts_at?->format('Y-m-d')) }}"
                   class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            @error('starts_at')
              <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
            @enderror
          </div>

          <div>
            <label for="ends_at" class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
              {{ __('discounts.field_ends_at') }}
            </label>
            <input type="date" id="ends_at" name="ends_at" value="{{ old('ends_at', $discount->ends_at?->format('Y-m-d')) }}"
                   class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            @error('ends_at')
              <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="flex items-center gap-2">
          <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $discount->is_active) ? 'checked' : '' }}
                 class="rounded border-neutral-300 dark:border-neutral-700 text-blue-600 focus:ring-blue-500">
          <label for="is_active" class="text-sm font-medium text-neutral-700 dark:text-neutral-200">
            {{ __('discounts.active_label') }}
          </label>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 border-t border-neutral-200 px-6 py-4 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-800/50">
        <a href="{{ route('discounts.index') }}"
           class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
          {{ __('discounts.cancel_btn') }}
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          {{ __('discounts.save_changes_btn') }}
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function updateValueLabel() {
  const type = document.getElementById('type').value;
  const valueLabel = document.getElementById('value-label');
  const valueHint = document.getElementById('value-hint');

  switch(type) {
    case 'free_minutes':
      valueLabel.textContent = @json(__('discounts.js_label_minutes'));
      valueHint.textContent = @json(__('discounts.js_hint_minutes'));
      break;
    case 'percentage':
      valueLabel.textContent = @json(__('discounts.js_label_percent'));
      valueHint.textContent = @json(__('discounts.js_hint_percent'));
      break;
    case 'fixed_amount':
      valueLabel.textContent = @json(__('discounts.js_label_fixed'));
      valueHint.textContent = @json(__('discounts.js_hint_fixed'));
      break;
  }
}

document.addEventListener('DOMContentLoaded', updateValueLabel);
</script>
@endsection
