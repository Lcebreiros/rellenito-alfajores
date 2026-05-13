@extends('layouts.app')

@section('header')
<div class="flex items-center justify-between gap-3 flex-wrap">
  <div>
    <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ __('rentals.spaces_title') }}</h1>
    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ __('rentals.spaces_subtitle') }}</p>
  </div>
  <div class="flex items-center gap-2">
    <a href="{{ route('rentals.calendar') }}"
       class="text-sm px-3 py-1.5 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      {{ __('rentals.calendar_btn') }}
    </a>
    <button @click="$dispatch('open-create-space-modal')"
            class="text-sm px-4 py-1.5 rounded-lg bg-violet-600 hover:bg-violet-700 text-white font-medium transition-colors flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      {{ __('rentals.add_space_title') }}
    </button>
  </div>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-3 sm:px-6 space-y-5"
     x-data="{ createModalOpen: false }"
     @open-create-space-modal.window="createModalOpen = true">

  @if(session('ok'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-2.5 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300 flex items-center gap-2">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>
      {{ session('ok') }}
    </div>
  @endif
  @if($errors->any())
    <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-2.5 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- ── Categorías ──────────────────────────────────────────────────────── --}}
  <div class="container-glass shadow-sm px-4 sm:px-5 py-3.5"
       x-data="{ addingCat: false }">
    <div class="flex items-center gap-3 flex-wrap">
      <span class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide flex-shrink-0">
        {{ __('rentals.categories_title') }}
      </span>

      {{-- Chips de categorías existentes --}}
      <div class="flex items-center gap-1.5 flex-wrap flex-1">
        @foreach($categories as $cat)
          <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full
                       bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300
                       border border-neutral-200 dark:border-neutral-700">
            {{ $cat->name }}
            @if(($cat->spaces_count ?? 0) > 0)
              <span class="text-neutral-400 dark:text-neutral-500">{{ $cat->spaces_count }}</span>
            @endif
          </span>
        @endforeach
        @if($categories->isEmpty())
          <span class="text-xs text-neutral-400 dark:text-neutral-500 italic">Sin categorías aún</span>
        @endif
      </div>

      {{-- Botón / form agregar categoría --}}
      <div class="flex items-center gap-2 flex-shrink-0">
        <button type="button" @click="addingCat = !addingCat"
                class="text-xs px-2.5 py-1 rounded-lg border border-dashed border-neutral-300 dark:border-neutral-600
                       text-neutral-500 dark:text-neutral-400 hover:border-violet-400 hover:text-violet-600
                       dark:hover:border-violet-600 dark:hover:text-violet-400 transition-colors flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nueva categoría
        </button>
      </div>
    </div>

    <div x-show="addingCat" x-cloak class="mt-3 pt-3 border-t border-neutral-100 dark:border-neutral-800">
      <form method="POST" action="{{ route('rentals.space-categories.store') }}" class="flex gap-2 items-center">
        @csrf
        <input name="name" required maxlength="100" autofocus
               class="flex-1 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                      px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40"
               placeholder="{{ __('rentals.category_name_ph') }}">
        <button type="submit"
                class="px-3 py-1.5 text-sm rounded-lg bg-violet-600 hover:bg-violet-700 text-white transition-colors">
          {{ __('rentals.add_btn') }}
        </button>
        <button type="button" @click="addingCat = false"
                class="px-3 py-1.5 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
          {{ __('rentals.cancel_btn') }}
        </button>
      </form>
    </div>
  </div>

  {{-- ── Grid de espacios ────────────────────────────────────────────────── --}}
  @if($spaces->isEmpty())
    <div class="container-glass shadow-sm p-12 text-center">
      <div class="w-14 h-14 rounded-2xl bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
      </div>
      <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Sin espacios todavía</p>
      <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-4">Creá tu primera cancha o salón para empezar a gestionar reservas.</p>
      <button @click="createModalOpen = true"
              class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-violet-600 hover:bg-violet-700 text-white transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('rentals.add_space_title') }}
      </button>
    </div>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @foreach($spaces as $space)
        @php
          $options = $space->activeDurationOptions;
          $fmtDuration = function(int $m): string {
              $h = intdiv($m, 60); $min = $m % 60;
              return $h > 0 ? ($h . ' h' . ($min > 0 ? ' ' . $min . ' min' : '')) : ($min . ' min');
          };
        @endphp

        <div class="container-glass shadow-sm overflow-hidden group"
             x-data="{ editing: false, addingOption: false }">

          {{-- Barra de color superior --}}
          <div class="h-1.5 w-full" style="background-color: {{ $space->color ?? '#6366f1' }};"></div>

          {{-- Cuerpo de la tarjeta --}}
          <div class="p-4 sm:p-5">

            {{-- Header: nombre + badges + acciones --}}
            <div class="flex items-start justify-between gap-3 mb-3">
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                  <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 leading-tight">
                    {{ $space->name }}
                  </h3>
                  @if($space->category)
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-medium
                                 bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300
                                 border border-violet-200/60 dark:border-violet-800/60">
                      {{ $space->category->name }}
                    </span>
                  @endif
                </div>
                @if($space->description)
                  <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-snug line-clamp-2">
                    {{ $space->description }}
                  </p>
                @endif
              </div>

              {{-- Acciones --}}
              <div class="flex items-center gap-0.5 flex-shrink-0">
                <a href="{{ route('rentals.spaces.show', $space) }}"
                   title="Ver agenda"
                   class="p-1.5 rounded-lg text-neutral-400 hover:text-violet-600 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </a>
                <button @click="editing = !editing; addingOption = false"
                        title="Editar"
                        class="p-1.5 rounded-lg text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                        :class="editing ? 'bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200' : ''">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <form method="POST" action="{{ route('rentals.spaces.destroy', $space) }}" class="inline"
                      onsubmit="return confirm(@json(__('rentals.confirm_delete_space')))">
                  @csrf @method('DELETE')
                  <button type="submit"
                          title="Eliminar"
                          class="p-1.5 rounded-lg text-neutral-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </form>
              </div>
            </div>

            {{-- Tarifas / duraciones --}}
            <div class="mb-3">
              @if($options->count())
                <div class="flex flex-wrap gap-1.5">
                  @foreach($options as $option)
                    @php
                      $durLabel = $fmtDuration((int)$option->minutes);
                    @endphp
                    <div class="group/opt inline-flex items-center gap-1.5 rounded-xl border
                                border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/60
                                pl-2.5 pr-1.5 py-1">
                      <div>
                        <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">
                          {{ $option->label ?: $durLabel }}
                        </span>
                        <span class="text-[11px] text-neutral-400 dark:text-neutral-500 ml-1">{{ $durLabel }}</span>
                      </div>
                      <span class="text-xs font-bold text-violet-700 dark:text-violet-300 bg-violet-50 dark:bg-violet-900/30 px-1.5 py-0.5 rounded-lg">
                        ${{ number_format($option->price, 0, ',', '.') }}
                      </span>
                      <form method="POST" action="{{ route('rentals.duration-options.destroy', $option) }}" class="inline opacity-0 group-hover/opt:opacity-100 transition-opacity">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-neutral-300 hover:text-rose-500 dark:text-neutral-600 dark:hover:text-rose-400 transition-colors leading-none p-0.5">
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                          </svg>
                        </button>
                      </form>
                    </div>
                  @endforeach
                </div>
              @else
                <p class="text-xs text-neutral-400 dark:text-neutral-500 italic">Sin tarifas definidas</p>
              @endif
            </div>

            {{-- Footer: estado + agregar tarifa --}}
            <div class="flex items-center justify-between pt-3 border-t border-neutral-100 dark:border-neutral-800">
              <div class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $space->is_active ? 'bg-emerald-500' : 'bg-neutral-400' }}"></span>
                <span class="text-xs {{ $space->is_active ? 'text-emerald-700 dark:text-emerald-400' : 'text-neutral-500 dark:text-neutral-400' }}">
                  {{ $space->is_active ? __('rentals.status_active') : __('rentals.status_inactive') }}
                </span>
              </div>
              <button @click="addingOption = !addingOption; editing = false"
                      class="text-xs text-violet-600 dark:text-violet-400 hover:text-violet-800 dark:hover:text-violet-200
                             flex items-center gap-1 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('rentals.add_duration_btn') }}
              </button>
            </div>
          </div>

          {{-- Panel: agregar tarifa --}}
          <div x-show="addingOption" x-cloak
               class="border-t border-neutral-100 dark:border-neutral-800 bg-violet-50/40 dark:bg-violet-900/10 px-4 sm:px-5 py-4">
            <p class="text-xs font-semibold text-violet-700 dark:text-violet-300 uppercase tracking-wide mb-3">
              Nueva tarifa
            </p>
            <form method="POST" action="{{ route('rentals.spaces.duration-options.store', $space) }}"
                  class="space-y-3">
              @csrf
              <div class="grid grid-cols-3 gap-2">
                <div class="col-span-3 sm:col-span-1">
                  <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">{{ __('rentals.opt_label_name') }}</label>
                  <input name="label" required maxlength="100"
                         placeholder="{{ __('rentals.opt_name_ph') }}"
                         class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                                px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40">
                </div>
                <div>
                  <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">{{ __('rentals.opt_label_minutes') }}</label>
                  <input name="minutes" type="number" required min="15" max="1440" placeholder="60"
                         class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                                px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40">
                </div>
                <div>
                  <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">{{ __('rentals.opt_label_price') }}</label>
                  <div class="relative">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-neutral-500 pointer-events-none font-medium">$</span>
                    <input name="price" type="number" required min="0" step="0.01" placeholder="0"
                           class="w-full rounded-lg border border-violet-200 dark:border-violet-800 bg-violet-50/50 dark:bg-violet-900/10
                                  pl-5 pr-3 py-1.5 text-sm text-violet-700 dark:text-violet-300 font-semibold
                                  focus:outline-none focus:ring-2 focus:ring-violet-500/40">
                  </div>
                </div>
              </div>
              <div class="flex gap-2">
                <button type="submit"
                        class="px-3 py-1.5 text-sm font-medium rounded-lg bg-violet-600 hover:bg-violet-700 text-white transition-colors">
                  {{ __('rentals.add_btn') }}
                </button>
                <button type="button" @click="addingOption = false"
                        class="px-3 py-1.5 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-white dark:hover:bg-neutral-800 transition-colors">
                  {{ __('rentals.cancel_btn') }}
                </button>
              </div>
            </form>
          </div>

          {{-- Panel: editar espacio --}}
          <div x-show="editing" x-cloak
               class="border-t border-neutral-100 dark:border-neutral-800 px-4 sm:px-5 py-4 bg-neutral-50/60 dark:bg-neutral-800/30">
            <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-3">
              {{ __('rentals.edit_space_label') }}
            </p>
            <form method="POST" action="{{ route('rentals.spaces.update', $space) }}" class="space-y-3">
              @csrf @method('PUT')
              <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                  <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('rentals.field_name') }}</label>
                  <input name="name" required maxlength="100" value="{{ $space->name }}"
                         class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                                px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40">
                </div>
                <div class="col-span-2">
                  <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('rentals.field_description') }}</label>
                  <input name="description" maxlength="500" value="{{ $space->description }}"
                         class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                                px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40"
                         placeholder="{{ __('rentals.field_description_ph') }}">
                </div>
                <div>
                  <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('rentals.field_category') }}</label>
                  <select name="category_id"
                          class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40">
                    <option value="">{{ __('rentals.no_category_opt') }}</option>
                    @foreach($categories as $cat)
                      <option value="{{ $cat->id }}" {{ $space->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('rentals.field_color') }}</label>
                  <input type="color" name="color" value="{{ $space->color }}"
                         class="w-full h-[38px] rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-1 py-1 cursor-pointer">
                </div>
              </div>
              <label class="flex items-center gap-2 cursor-pointer w-fit">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ $space->is_active ? 'checked' : '' }}
                       class="rounded border-neutral-300 dark:border-neutral-700 text-violet-600 focus:ring-violet-500">
                <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ __('rentals.space_active_label') }}</span>
              </label>
              <div class="flex gap-2 pt-1">
                <button type="submit"
                        class="px-4 py-1.5 text-sm font-medium rounded-lg bg-violet-600 hover:bg-violet-700 text-white transition-colors">
                  {{ __('rentals.save_changes_btn') }}
                </button>
                <button type="button" @click="editing = false"
                        class="px-3 py-1.5 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 hover:bg-white dark:hover:bg-neutral-800 transition-colors">
                  {{ __('rentals.cancel_btn') }}
                </button>
              </div>
            </form>
          </div>

        </div>
      @endforeach
    </div>
  @endif

{{-- ── MODAL: Crear espacio ─────────────────────────────────────────────── --}}
<div x-show="createModalOpen" x-cloak
     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-sm"
     @keydown.escape.window="createModalOpen = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

  <div class="bg-white dark:bg-neutral-900 rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-lg max-h-[92vh] overflow-y-auto"
       @click.outside="createModalOpen = false"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="translate-y-4 sm:scale-95 opacity-0"
       x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
       x-data="{ options: [{ label: '', minutes: 60, price: '' }] }">

    {{-- Header del modal --}}
    <div class="sticky top-0 bg-white dark:bg-neutral-900 px-5 py-4
                border-b border-neutral-200 dark:border-neutral-700
                flex items-center justify-between z-10">
      <div>
        <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">{{ __('rentals.add_space_title') }}</h3>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Definí el nombre, color y tarifas del espacio.</p>
      </div>
      <button @click="createModalOpen = false"
              class="p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
        <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <form method="POST" action="{{ route('rentals.spaces.store') }}" class="p-5 space-y-5">
      @csrf

      {{-- Nombre --}}
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
          {{ __('rentals.field_name') }} <span class="text-rose-500">*</span>
        </label>
        <input name="name" required maxlength="100"
               class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                      px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40"
               placeholder="{{ __('rentals.field_name_ph') }}">
      </div>

      {{-- Descripción --}}
      <div>
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">
          {{ __('rentals.field_description') }}
          <span class="font-normal text-neutral-400 text-xs">{{ __('rentals.field_description_opt') }}</span>
        </label>
        <input name="description" maxlength="500"
               class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                      px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40"
               placeholder="{{ __('rentals.field_description_ph') }}">
      </div>

      {{-- Categoría + Color --}}
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('rentals.field_category') }}</label>
          <select name="category_id"
                  class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40">
            <option value="">{{ __('rentals.no_category_opt') }}</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">{{ __('rentals.field_color') }}</label>
          <input type="color" name="color" value="#7c3aed"
                 class="w-full h-[38px] rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-1 py-1 cursor-pointer">
        </div>
      </div>

      {{-- Duraciones y tarifas --}}
      <div>
        <div class="mb-3">
          <p class="text-sm font-medium text-neutral-700 dark:text-neutral-200">
            {{ __('rentals.durations_title') }} <span class="text-rose-500">*</span>
          </p>
          <p class="text-xs text-neutral-400 mt-0.5">{{ __('rentals.durations_hint') }}</p>
        </div>

        <div class="space-y-2">
          <template x-for="(opt, i) in options" :key="i">
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700
                        bg-neutral-50/60 dark:bg-neutral-800/40 p-3 relative">
              <button type="button" @click="options.splice(i,1)" x-show="options.length > 1"
                      class="absolute top-2.5 right-2.5 p-0.5 text-neutral-400 hover:text-rose-500 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pr-6">
                <div class="sm:col-span-1">
                  <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">{{ __('rentals.opt_name_label') }}</label>
                  <input type="text" :name="`duration_options[${i}][label]`" x-model="opt.label"
                         placeholder="1 hora" maxlength="100"
                         class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                                px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40">
                </div>
                <div>
                  <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">{{ __('rentals.opt_duration_label') }}</label>
                  <input type="number" :name="`duration_options[${i}][minutes]`" x-model="opt.minutes"
                         min="15" max="1440" placeholder="60"
                         class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
                                px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/40">
                  <p class="text-[10px] text-neutral-400 mt-0.5"
                     x-text="opt.minutes >= 60
                       ? Math.floor(opt.minutes/60)+'h '+(opt.minutes%60 ? opt.minutes%60+'min' : '')
                       : (opt.minutes ? opt.minutes+'min' : '')"></p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">{{ __('rentals.opt_price_label') }}</label>
                  <div class="relative">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-violet-500 font-semibold pointer-events-none">$</span>
                    <input type="number" :name="`duration_options[${i}][price]`" x-model="opt.price"
                           min="0" step="1" placeholder="0"
                           class="w-full rounded-lg border border-violet-200 dark:border-violet-800
                                  bg-violet-50/60 dark:bg-violet-900/10 pl-5 pr-2.5 py-1.5 text-sm
                                  text-violet-700 dark:text-violet-300 font-semibold
                                  focus:outline-none focus:ring-2 focus:ring-violet-500/40">
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>

        <button type="button" @click="options.push({ label: '', minutes: 60, price: '' })"
                class="mt-2 text-xs text-violet-600 dark:text-violet-400 hover:text-violet-800 dark:hover:text-violet-200
                       flex items-center gap-1 transition-colors">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          {{ __('rentals.add_option_btn') }}
        </button>
      </div>

      {{-- Botones --}}
      <div class="flex gap-2 pt-1 border-t border-neutral-100 dark:border-neutral-800">
        <button type="button" @click="createModalOpen = false"
                class="px-4 py-2.5 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700
                       text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
          {{ __('rentals.cancel_btn') }}
        </button>
        <button type="submit"
                class="flex-1 py-2.5 text-sm font-medium rounded-lg bg-violet-600 hover:bg-violet-700 text-white transition-colors">
          {{ __('rentals.create_space_btn') }}
        </button>
      </div>
    </form>

  </div>
</div>
</div>
@endsection
