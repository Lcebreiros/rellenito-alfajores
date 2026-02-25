@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">Espacios de alquiler</h1>
      <p class="text-sm text-neutral-600 dark:text-neutral-400">Canchas, salones y todo lo que alquilás.</p>
    </div>
    <a href="{{ route('rentals.calendar') }}"
       class="text-sm px-3 py-1.5 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      Calendario
    </a>
  </div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-3 sm:px-6 space-y-4">

  @if(session('ok'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif
  @if($errors->any())
    <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Formulario crear espacio --}}
    <div class="lg:col-span-1">
      <div class="container-glass shadow-sm overflow-hidden" x-data="{ open: true }">
        <div class="px-4 sm:px-5 py-3 bg-neutral-100/70 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between cursor-pointer"
             @click="open = !open">
          <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Agregar espacio</h2>
          <svg class="w-4 h-4 text-neutral-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </div>

        <form method="POST" action="{{ route('rentals.spaces.store') }}" x-show="open" class="p-4 sm:p-5 space-y-4">
          @csrf
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Nombre <span class="text-rose-500">*</span></label>
            <input name="name" required maxlength="100"
                   class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                   placeholder="Cancha 1, Salón A...">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Descripción <span class="text-neutral-400 font-normal text-xs">(opcional)</span></label>
            <input name="description" maxlength="500"
                   class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                   placeholder="Cancha de pádel techada, 4 jugadores...">
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Categoría</label>
              <select name="category_id"
                      class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                <option value="">Sin categoría</option>
                @foreach($categories as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Color identificador</label>
              <input type="color" name="color" value="#6366f1"
                     class="w-full h-[38px] rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-1 py-1 cursor-pointer">
            </div>
          </div>

          {{-- Opciones de duración / Tarifas --}}
          <div x-data="{ options: [{label:'', minutes:60, price:''}] }">
            <div class="mb-2">
              <p class="text-sm font-medium text-neutral-700 dark:text-neutral-200">Duraciones y tarifas <span class="text-rose-500">*</span></p>
              <p class="text-xs text-neutral-400 mt-0.5">Definí las opciones de tiempo y su precio. Ej: "1 hora" → 60 min → $5.000</p>
            </div>

            <div class="space-y-3">
              <template x-for="(opt, i) in options" :key="i">
                <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50/60 dark:bg-neutral-800/40 p-3 relative">
                  <button type="button" @click="options.splice(i,1)" x-show="options.length > 1"
                          class="absolute top-2 right-2 p-0.5 text-neutral-400 hover:text-rose-500 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                  <div class="grid grid-cols-1 gap-2 pr-4">
                    <div>
                      <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Nombre de la opción</label>
                      <input type="text" :name="`duration_options[${i}][label]`" x-model="opt.label"
                             placeholder="Ej: 1 hora, Media jornada, Día completo..." maxlength="100"
                             class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-2.5 py-1.5 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                      <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Duración (en minutos)</label>
                        <input type="number" :name="`duration_options[${i}][minutes]`" x-model="opt.minutes"
                               min="15" max="1440" placeholder="60"
                               class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-2.5 py-1.5 text-sm">
                        <p class="text-xs text-neutral-400 mt-0.5" x-text="opt.minutes >= 60 ? Math.floor(opt.minutes/60)+'h '+(opt.minutes%60 ? opt.minutes%60+'min' : '') : (opt.minutes ? opt.minutes+'min' : '')"></p>
                      </div>
                      <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Precio</label>
                        <div class="relative">
                          <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-sm text-neutral-500 font-medium pointer-events-none">$</span>
                          <input type="number" :name="`duration_options[${i}][price]`" x-model="opt.price"
                                 min="0" step="1" placeholder="0"
                                 class="w-full rounded-lg border border-violet-200 dark:border-violet-800 bg-violet-50/50 dark:bg-violet-900/10 pl-6 pr-2.5 py-1.5 text-sm font-medium text-violet-700 dark:text-violet-300">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <button type="button" @click="options.push({label:'',minutes:60,price:''})"
                    class="mt-2 text-xs text-violet-600 dark:text-violet-400 hover:underline flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Agregar otra opción
            </button>
          </div>

          <button type="submit"
                  class="w-full py-2 text-sm font-medium rounded-lg bg-violet-600 hover:bg-violet-700 text-white transition-colors">
            Crear espacio
          </button>
        </form>
      </div>

      {{-- Categorías --}}
      <div class="container-glass shadow-sm overflow-hidden mt-4">
        <div class="px-4 sm:px-5 py-3 bg-neutral-100/70 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-700">
          <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Categorías</h2>
        </div>
        <div class="p-4 sm:p-5">
          <form method="POST" action="{{ route('rentals.space-categories.store') }}" class="flex gap-2 mb-3">
            @csrf
            <input name="name" required maxlength="100"
                   class="flex-1 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                   placeholder="Pádel, Fútbol 5...">
            <button type="submit"
                    class="px-3 py-2 text-sm rounded-lg bg-neutral-700 dark:bg-neutral-600 hover:bg-neutral-800 dark:hover:bg-neutral-500 text-white transition-colors">
              Agregar
            </button>
          </form>
          @foreach($categories as $cat)
            <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 dark:border-neutral-800 last:border-0">
              <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $cat->name }}</span>
              <span class="text-xs text-neutral-400">{{ $cat->spaces_count ?? 0 }} espacios</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Lista de espacios --}}
    <div class="lg:col-span-2 space-y-3">
      @forelse($spaces as $space)
        <div class="container-glass shadow-sm overflow-hidden" x-data="{ editing: false, addOption: false }">
          {{-- Header del espacio --}}
          <div class="px-4 sm:px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <span class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $space->color }};"></span>
              <div>
                <div class="font-semibold text-neutral-900 dark:text-neutral-100 text-sm">{{ $space->name }}</div>
                @if($space->description)
                  <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $space->description }}</div>
                @endif
              </div>
              @if($space->category)
                <span class="text-xs px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400">
                  {{ $space->category->name }}
                </span>
              @endif
            </div>
            <div class="flex items-center gap-1">
              <span class="text-xs px-2 py-0.5 rounded-full {{ $space->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400' }}">
                {{ $space->is_active ? 'Activo' : 'Inactivo' }}
              </span>
              <a href="{{ route('rentals.spaces.show', $space) }}"
                 class="p-1.5 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors"
                 title="Ver horarios">
                <svg class="w-3.5 h-3.5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </a>
              <button @click="editing = !editing"
                      class="p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                <svg class="w-3.5 h-3.5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <form method="POST" action="{{ route('rentals.spaces.destroy', $space) }}" class="inline"
                    onsubmit="return confirm('¿Eliminar este espacio?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                  <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </form>
            </div>
          </div>

          {{-- Opciones de duración --}}
          @if($space->activeDurationOptions->count())
            <div class="px-4 sm:px-5 pb-3">
              <div class="flex flex-wrap gap-1.5">
                @foreach($space->activeDurationOptions as $option)
                  <div class="group inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                    <span>{{ $option->label }}</span>
                    <span class="text-neutral-500">· ${{ number_format($option->price, 0, ',', '.') }}</span>
                    <form method="POST" action="{{ route('rentals.duration-options.destroy', $option) }}" class="inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="text-neutral-400 hover:text-rose-500 transition-colors leading-none">×</button>
                    </form>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

          {{-- Form agregar opción de duración --}}
          <div x-show="addOption" class="px-4 sm:px-5 pb-3">
            <form method="POST" action="{{ route('rentals.spaces.duration-options.store', $space) }}"
                  class="flex flex-wrap gap-2 items-end">
              @csrf
              <div>
                <label class="block text-xs text-neutral-500 mb-1">Nombre</label>
                <input name="label" required maxlength="100" placeholder="1 hora y media"
                       class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-1.5 text-sm w-40">
              </div>
              <div>
                <label class="block text-xs text-neutral-500 mb-1">Minutos</label>
                <input name="minutes" type="number" required min="15" max="1440" placeholder="90"
                       class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-1.5 text-sm w-20">
              </div>
              <div>
                <label class="block text-xs text-neutral-500 mb-1">Precio</label>
                <input name="price" type="number" required min="0" step="0.01" placeholder="0"
                       class="rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-1.5 text-sm w-24">
              </div>
              <button type="submit" class="px-3 py-1.5 text-sm rounded-lg bg-violet-600 hover:bg-violet-700 text-white">
                Agregar
              </button>
              <button type="button" @click="addOption=false" class="px-3 py-1.5 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400">
                Cancelar
              </button>
            </form>
          </div>

          <div class="px-4 sm:px-5 pb-3" x-show="!addOption">
            <button @click="addOption=true" class="text-xs text-violet-600 dark:text-violet-400 hover:underline flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Agregar opción de duración
            </button>
          </div>

          {{-- Form editar espacio --}}
          <div x-show="editing" class="border-t border-neutral-200 dark:border-neutral-700 p-4 sm:p-5">
            <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-3">Editar espacio</p>
            <form method="POST" action="{{ route('rentals.spaces.update', $space) }}" class="space-y-3">
              @csrf
              @method('PUT')
              <div>
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-200 mb-1">Nombre</label>
                <input name="name" required maxlength="100" value="{{ $space->name }}"
                       class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
              </div>
              <div>
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-200 mb-1">Descripción</label>
                <input name="description" maxlength="500" value="{{ $space->description }}"
                       class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                       placeholder="Descripción del espacio...">
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-200 mb-1">Categoría</label>
                  <select name="category_id"
                          class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                    <option value="">Sin categoría</option>
                    @foreach($categories as $cat)
                      <option value="{{ $cat->id }}" {{ $space->category_id == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-200 mb-1">Color identificador</label>
                  <input type="color" name="color" value="{{ $space->color }}"
                         class="w-full h-[38px] rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-1 py-1 cursor-pointer">
                </div>
              </div>
              <div>
                <label class="flex items-center gap-2 cursor-pointer w-fit">
                  <input type="hidden" name="is_active" value="0">
                  <input type="checkbox" name="is_active" value="1" {{ $space->is_active ? 'checked' : '' }}
                         class="rounded border-neutral-300 dark:border-neutral-700 text-violet-600">
                  <span class="text-sm text-neutral-700 dark:text-neutral-300">Espacio activo</span>
                </label>
              </div>
              <div class="flex gap-2 justify-end pt-1">
                <button type="button" @click="editing=false"
                        class="px-3 py-1.5 text-sm rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400">
                  Cancelar
                </button>
                <button type="submit" class="px-4 py-1.5 text-sm font-medium rounded-lg bg-violet-600 hover:bg-violet-700 text-white">
                  Guardar cambios
                </button>
              </div>
            </form>
          </div>
        </div>
      @empty
        <div class="container-glass shadow-sm p-8 text-center text-neutral-500 dark:text-neutral-400">
          <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
          </svg>
          <p class="text-sm">Todavía no tenés espacios. Creá uno desde el formulario.</p>
        </div>
      @endforelse
    </div>
  </div>
</div>
@endsection
