@extends('layouts.app')

@section('header')
  <div class="flex items-center gap-3">
    <a href="{{ route('rentals.calendar') }}"
       class="p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
      <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">Nueva reserva</h1>
    </div>
  </div>
@endsection

@section('content')
<div class="max-w-xl mx-auto px-3 sm:px-6">
  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="container-glass shadow-sm overflow-hidden"
       x-data="{
         selectedSpaceId: null,
         selectedOptionId: null,
         spaces: {{ $spaces->keyBy('id')->toJson() }},
         get durationOptions() {
           if (!this.selectedSpaceId) return [];
           const s = this.spaces[this.selectedSpaceId];
           return s ? s.active_duration_options : [];
         },
         selectOption(opt) {
           this.selectedOptionId = opt.id;
           document.getElementById('duration_minutes').value = opt.minutes;
           document.getElementById('total_amount').value = opt.price;
           document.getElementById('rental_duration_option_id').value = opt.id;
         }
       }">
    <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
      <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Datos de la reserva</h2>
    </div>

    <form method="POST" action="{{ route('rentals.bookings.store') }}" class="p-5 space-y-4">
      @csrf
      <input type="hidden" name="rental_duration_option_id" id="rental_duration_option_id" value="{{ old('rental_duration_option_id') }}">

      {{-- Espacio --}}
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Espacio *</label>
        <select name="rental_space_id" required
                x-model="selectedSpaceId"
                class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
          <option value="">Seleccioná un espacio</option>
          @foreach($spaces as $space)
            <option value="{{ $space->id }}" {{ old('rental_space_id') == $space->id ? 'selected' : '' }}>
              {{ $space->name }}
            </option>
          @endforeach
        </select>
        @error('rental_space_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Fecha y hora de inicio --}}
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Fecha y hora de inicio *</label>
        <input type="datetime-local" name="starts_at" required
               value="{{ old('starts_at', now()->addHour()->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
        @error('starts_at') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Duración --}}
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">Duración *</label>
        {{-- Opciones preestablecidas del espacio --}}
        <div x-show="durationOptions.length > 0" class="flex flex-wrap gap-2 mb-2">
          <template x-for="opt in durationOptions" :key="opt.id">
            <button type="button"
                    @click="selectOption(opt)"
                    class="px-3 py-1.5 rounded-lg text-sm border transition-colors"
                    :class="selectedOptionId == opt.id
                      ? 'bg-violet-600 border-violet-600 text-white'
                      : 'border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:border-violet-400'">
              <span x-text="opt.label"></span>
              <span class="text-xs opacity-75">· $<span x-text="parseFloat(opt.price).toLocaleString('es-AR')"></span></span>
            </button>
          </template>
        </div>
        <div class="flex items-center gap-2">
          <input type="number" name="duration_minutes" id="duration_minutes" required
                 min="15" max="1440" step="15"
                 value="{{ old('duration_minutes', 60) }}"
                 class="w-28 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
          <span class="text-sm text-neutral-500">minutos</span>
        </div>
        @error('duration_minutes') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Monto --}}
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Monto total</label>
        <div class="relative">
          <span class="absolute left-3 top-2 text-sm text-neutral-500">$</span>
          <input type="number" name="total_amount" id="total_amount"
                 min="0" step="0.01"
                 value="{{ old('total_amount', 0) }}"
                 class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 pl-7 pr-3 py-2 text-sm">
        </div>
      </div>

      {{-- Cliente --}}
      <div x-data="{ useRegistered: false }">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Cliente</label>
        <div class="flex gap-2 mb-2">
          <button type="button" @click="useRegistered = false"
                  class="text-xs px-2.5 py-1 rounded-full border transition-colors"
                  :class="!useRegistered ? 'bg-violet-600 border-violet-600 text-white' : 'border-neutral-300 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400'">
            Rápido
          </button>
          <button type="button" @click="useRegistered = true"
                  class="text-xs px-2.5 py-1 rounded-full border transition-colors"
                  :class="useRegistered ? 'bg-violet-600 border-violet-600 text-white' : 'border-neutral-300 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400'">
            Registrado
          </button>
        </div>

        <div x-show="!useRegistered" class="grid grid-cols-2 gap-2">
          <input type="text" name="client_name" placeholder="Nombre"
                 value="{{ old('client_name') }}"
                 class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
          <input type="text" name="client_phone" placeholder="Teléfono"
                 value="{{ old('client_phone') }}"
                 class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
        </div>

        <div x-show="useRegistered">
          <select name="client_id"
                  class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            <option value="">Seleccioná un cliente</option>
            @foreach($clients as $client)
              <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                {{ $client->name }}{{ $client->phone ? ' · '.$client->phone : '' }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Notas --}}
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Notas</label>
        <textarea name="notes" rows="2"
                  class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm resize-none"
                  placeholder="Observaciones, pedidos especiales...">{{ old('notes') }}</textarea>
      </div>

      <div class="flex gap-2 pt-1">
        <button type="submit"
                class="flex-1 py-2.5 text-sm font-medium rounded-lg bg-violet-600 hover:bg-violet-700 text-white transition-colors">
          Crear reserva
        </button>
        <a href="{{ route('rentals.calendar') }}"
           class="px-4 py-2.5 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
