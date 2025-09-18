<div class="max-w-3xl mx-auto space-y-6">

  {{-- AVISO OK --}}
  @if (session('ok'))
    <div class="p-3 rounded-md bg-green-100 text-green-800
                dark:bg-green-900 dark:text-green-200">
      {{ session('ok') }}
    </div>
  @endif

  {{-- CARD: Información de la aplicación --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900
              flex flex-col items-center text-center">
    {{-- Logo centrado --}}
    <div class="mb-4">
      <img src="{{ asset('images/Gestior.png') }}" alt="Gestior Logo" class="w-80 h-54">
    </div>

    {{-- Info --}}
    <p class="text-sm text-neutral-600 dark:text-neutral-400">
      Versión {{ config('app.version', '1.0.0') }}
    </p>
    <p class="text-xs text-neutral-500 dark:text-neutral-500 mt-2">
      Herramienta de gestión y control económico
    </p>
  </div>

  {{-- CARD: Apariencia (switch) --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Apariencia</h2>

    <div class="flex items-center justify-between gap-6">
      <div>
        <p class="text-neutral-800 dark:text-neutral-200 font-medium">Tema</p>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">Cambiá entre Modo Día y Modo Noche</p>
      </div>

      {{-- BOTÓN SIMPLE PARA MÓVIL --}}
      <div x-data="{ 
          t: @entangle('theme').live,
          isChanging: false,

          async toggleTheme() {
              if (this.isChanging) return;
              
              this.isChanging = true;
              const newTheme = this.t === 'light' ? 'dark' : 'light';
              
              try {
                  // Cambio optimista
                  this.t = newTheme;
                  this.applyThemeImmediate(newTheme);
                  
                  // Enviar a Livewire
                  await $wire.setTheme(newTheme);
                  
                  // Respaldo en localStorage
                  localStorage.setItem('theme', newTheme);
                  
              } catch (error) {
                  console.error('Error al cambiar tema:', error);
                  // Revertir en caso de error
                  this.t = this.t === 'light' ? 'dark' : 'light';
                  this.applyThemeImmediate(this.t);
              } finally {
                  setTimeout(() => this.isChanging = false, 400);
              }
          },

          applyThemeImmediate(theme) {
              if (theme === 'dark') {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }" class="block md:hidden">

          <button type="button"
                  @click="toggleTheme()"
                  :disabled="isChanging"
                  class="relative w-14 h-14 rounded-full border-2 shadow-lg
                         transition-all duration-300 ease-out
                         focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2
                         touch-manipulation disabled:opacity-70
                         flex items-center justify-center"
                  :class="t === 'dark' ? 
                      'bg-black border-neutral-700 text-white focus-visible:ring-neutral-400' : 
                      'bg-white border-neutral-300 text-neutral-900 focus-visible:ring-indigo-400'">

              {{-- Contenedor del ícono con animación de giro --}}
              <div class="transition-transform duration-300 ease-out"
                   :class="isChanging ? 'rotate-180' : 'rotate-0'">
                  
                  {{-- Sol (Day Mode) --}}
                  <template x-if="t === 'light'">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                          <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"></circle>
                          <path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5L19 19M5 19l1.5-1.5M17.5 6.5L19 5"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      </svg>
                  </template>
                  
                  {{-- Luna (Night Mode) --}}
                  <template x-if="t === 'dark'">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                          <path d="M21 12.8a9 9 0 1 1-9.8-9 7 7 0 0 0 9.8 9z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                  </template>
              </div>

              {{-- Loading indicator --}}
              <div x-show="isChanging" 
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 scale-50"
                   x-transition:enter-end="opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-200"
                   x-transition:leave-start="opacity-100 scale-100"
                   x-transition:leave-end="opacity-0 scale-50"
                   class="absolute inset-0 flex items-center justify-center bg-current/10 rounded-full">
                  <div class="w-3 h-3 border border-current border-t-transparent rounded-full animate-spin"></div>
              </div>
          </button>
      </div>

      {{-- SWITCH COMPLEJO PARA DESKTOP --}}
      <div x-data="{ 
          t: @entangle('theme').live,
          isChanging: false,
          
          async toggleTheme() {
              if (this.isChanging) return;
              
              this.isChanging = true;
              const newTheme = this.t === 'light' ? 'dark' : 'light';
              
              try {
                  this.t = newTheme;
                  this.applyThemeImmediate(newTheme);
                  await $wire.setTheme(newTheme);
                  localStorage.setItem('theme', newTheme);
              } catch (error) {
                  console.error('Error al cambiar tema:', error);
                  this.t = this.t === 'light' ? 'dark' : 'light';
                  this.applyThemeImmediate(this.t);
              } finally {
                  setTimeout(() => this.isChanging = false, 300);
              }
          },

          applyThemeImmediate(theme) {
              if (theme === 'dark') {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }" class="hidden md:block select-none">

          <button type="button"
                  @click="toggleTheme()"
                  @keydown.space.prevent="toggleTheme()"
                  @keydown.enter.prevent="toggleTheme()"
                  role="switch" 
                  :aria-checked="t === 'dark'"
                  :aria-label="t === 'dark' ? 'Night mode' : 'Day mode'"
                  :disabled="isChanging"
                  class="relative w-[11.5rem] h-14 rounded-full border p-1 overflow-hidden
                         transition-all duration-300 ease-out focus:outline-none 
                         focus-visible:ring-2 focus-visible:ring-neutral-400
                         disabled:opacity-70"
                  :class="t === 'dark' ? 'bg-black border-black' : 'bg-neutral-200 border-neutral-300'">

              {{-- Label DAY --}}
              <span x-cloak
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold whitespace-nowrap
                           transition-opacity duration-200 pointer-events-none"
                    :class="t === 'light' ? 'opacity-100 text-neutral-900' : 'opacity-0'">
                  DAY MODE
              </span>

              {{-- Label NIGHT --}}
              <span x-cloak
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold whitespace-nowrap
                           transition-opacity duration-200 pointer-events-none"
                    :class="t === 'dark' ? 'opacity-100 text-white' : 'opacity-0'">
                  NIGHT MODE
              </span>

              {{-- KNOB --}}
              <div class="absolute top-1 h-12 w-12 rounded-full border shadow flex items-center justify-center
                          transition-all duration-300 ease-out will-change-transform
                          bg-white border-neutral-300 text-neutral-900"
                   :style="{ left: t === 'dark' ? '0.25rem' : 'calc(100% - 3.25rem)' }">
                  
                  <template x-if="t === 'light'">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                          <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"></circle>
                          <path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5L19 19M5 19l1.5-1.5M17.5 6.5L19 5"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      </svg>
                  </template>
                  
                  <template x-if="t === 'dark'">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                          <path d="M21 12.8a9 9 0 1 1-9.8-9 7 7 0 0 0 9.8 9z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                  </template>
              </div>
          </button>
      </div>
    </div>

    <p class="mt-2 text-xs text-neutral-600 dark:text-neutral-400">Se aplica sin recargar.</p>
  </div>

{{-- CARD: Zona horaria (mejorada y optimizada para solapes) --}}
<div class="rounded-2xl bg-neutral-50/50 p-6 transition-all duration-300
            dark:bg-neutral-800/30">
  
  {{-- Header con icono --}}
  <div class="flex items-center gap-3 mb-6">
    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-neutral-100 dark:bg-neutral-700/50">
      <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">
      Zona horaria
    </h2>
  </div>

  {{-- x-data container RELATIVE: sirve como anchor para todos los dropdowns --}}
  <div
    x-data="timezoneSelector({
      tz: @entangle('timezone').live,
      list: @js($this->timezones ?? []),
      recommended: 'America/Argentina/Buenos_Aires'
    })"
    class="space-y-5 relative">

    {{-- Label principal --}}
    <div>
      <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
        Seleccioná tu zona horaria
      </label>
      <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">
        Configurá tu zona horaria para ver fechas y horarios correctos en toda la aplicación
      </p>
    </div>

    {{-- GRID: dos columnas en sm+, una columna en xs --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      
      {{-- Country selector --}}
      <div class="relative">
        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-2">
          Filtrar por país
        </label>
        <button type="button"
                @click="countryOpen = !countryOpen"
                class="group w-full flex justify-between items-center rounded-xl px-4 py-3 text-left transition-all duration-200
                       bg-white/60 hover:bg-white/80 focus:ring-2 focus:ring-neutral-500/20 focus:bg-white/80
                       dark:bg-neutral-900/60 dark:hover:bg-neutral-900/80 dark:focus:bg-neutral-900/80">
          
          <div class="flex items-center gap-3">
            <svg class="w-4 h-4 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
            </svg>
            <span class="font-medium" x-text="selectedCountryLabel"></span>
          </div>
          
          <svg class="w-4 h-4 text-neutral-400 transition-transform duration-200 group-hover:text-neutral-600 dark:group-hover:text-neutral-300"
               :class="countryOpen ? 'rotate-180' : ''" 
               fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        {{-- Country dropdown --}}
        <div x-cloak x-show="countryOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.outside="countryOpen = false"
             class="absolute left-0 right-0 mt-2 top-full z-40 max-h-64 overflow-auto rounded-xl
                    bg-white/95 backdrop-blur-sm shadow-xl
                    dark:bg-neutral-900/95">
          
          <div class="p-1">
            <template x-for="c in countries" :key="c">
              <button type="button"
                      @click="selectCountry(c)"
                      class="group w-full text-left px-3 py-2.5 rounded-lg transition-all duration-150 hover:bg-neutral-100/80 dark:hover:bg-neutral-800/80"
                      :class="selectedCountry === c ? 'bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 font-medium' : 'text-neutral-700 dark:text-neutral-300'">
                <span x-text="c"></span>
              </button>
            </template>

            <div class="px-3 py-4 text-xs text-neutral-500 dark:text-neutral-400 text-center" x-show="countries.length === 0">
              No hay países disponibles.
            </div>
          </div>
        </div>
      </div>

      {{-- Search + acciones --}}
      <div class="relative">
        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-2">
          Buscar zona horaria
        </label>
        
        {{-- Search input --}}
        <div class="relative">
          <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <svg class="w-4 h-4 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          
          <input type="search"
                 x-model="q"
                 @input="open = true"
                 @focus="open = true"
                 placeholder="Ciudad o zona (ej. Buenos Aires, London)"
                 class="w-full pl-10 pr-4 py-3 rounded-xl transition-all duration-200
                        bg-white/60 hover:bg-white/80 focus:ring-2 focus:ring-neutral-500/20 focus:bg-white/80
                        dark:bg-neutral-900/60 dark:hover:bg-neutral-900/80 dark:focus:bg-neutral-900/80 dark:text-neutral-100 
                        placeholder:text-neutral-400 dark:placeholder:text-neutral-500 text-sm" />
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center gap-2 mt-3">
          <button type="button"
                  @click="select(recommended)"
                  class="flex-1 px-3 py-2 text-xs rounded-lg bg-neutral-100 text-neutral-700 
                         hover:bg-neutral-200 transition-colors duration-200 font-medium
                         dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700">
            🇦🇷 Usar Argentina
          </button>
          <button type="button"
                  @click="clearSelection"
                  class="px-3 py-2 text-xs rounded-lg bg-neutral-100/50 text-neutral-600 
                         hover:bg-neutral-100 transition-colors duration-200
                         dark:bg-neutral-800/50 dark:text-neutral-400 dark:hover:bg-neutral-800">
            Limpiar
          </button>
        </div>
      </div>
    </div> {{-- end grid --}}

    {{-- Dropdown de zonas --}}
    <div x-cloak x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.outside="open = false"
         class="absolute left-0 right-0 top-full z-30 mt-4 max-h-80 overflow-auto rounded-xl
                bg-white/95 backdrop-blur-sm shadow-xl
                dark:bg-neutral-900/95">
      
      <div class="p-1">
        <template x-for="tzObj in filtered.slice(0, 400)" :key="tzObj.tz">
          <button type="button"
                  @click="select(tzObj.tz)"
                  class="group w-full px-4 py-3 text-left rounded-lg transition-all duration-150 hover:bg-neutral-100/80 dark:hover:bg-neutral-800/80"
                  :class="tzObj.tz === tz ? 'bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100' : 'text-neutral-700 dark:text-neutral-300'">
            <div class="flex items-center justify-between">
              <div class="min-w-0 flex-1">
                <div class="font-medium text-sm truncate" x-text="tzObj.label"></div>
                <div class="text-xs text-neutral-500 dark:text-neutral-400 truncate mt-0.5" x-text="tzObj.tz"></div>
              </div>
              <div class="ml-3 text-xs text-neutral-400 dark:text-neutral-500 font-medium" x-text="tzObj.country"></div>
            </div>
          </button>
        </template>

        <div x-show="filtered.length === 0" class="px-4 py-8 text-center">
          <svg class="w-8 h-8 mx-auto text-neutral-300 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <p class="text-sm text-neutral-500 dark:text-neutral-400">Sin resultados</p>
          <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Probá con otros términos de búsqueda</p>
        </div>
      </div>
    </div>

    {{-- Status y actions --}}
    <div class="flex items-center justify-between pt-4">
      <div class="flex items-center gap-2">
        <div class="w-2 h-2 rounded-full bg-neutral-600 dark:bg-neutral-300" x-show="tz"></div>
        <div class="w-2 h-2 rounded-full bg-neutral-300 dark:bg-neutral-600" x-show="!tz"></div>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
          <span x-show="tz">Configurada:</span>
          <span x-show="!tz">Sin configurar</span>
          <span class="font-mono text-xs ml-1" x-text="tz || '—'"></span>
        </p>
      </div>

      <button wire:click="saveTimezone"
              wire:loading.attr="disabled"
              wire:loading.class="opacity-50 cursor-not-allowed"
              class="group px-6 py-2.5 rounded-xl bg-neutral-800 hover:bg-neutral-900 
                     text-white font-medium text-sm transition-all duration-200 shadow-sm hover:shadow-md
                     focus:ring-2 focus:ring-neutral-500/20 disabled:opacity-60 disabled:cursor-not-allowed
                     dark:bg-neutral-200 dark:hover:bg-neutral-100 dark:text-neutral-800">
        <span wire:loading.remove>Guardar configuración</span>
        <span wire:loading class="flex items-center gap-2">
          <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Guardando...
        </span>
      </button>
    </div>

    @error('timezone')
      <div class="flex items-center gap-2 p-3 rounded-lg bg-neutral-100 dark:bg-neutral-800">
        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-sm text-neutral-700 dark:text-neutral-200">{{ $message }}</span>
      </div>
    @enderror
  </div>

  {{-- Help text mejorado --}}
  <div class="mt-6 p-4 rounded-xl bg-neutral-100/50 dark:bg-neutral-800/50">
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0 mt-0.5">
        <svg class="w-4 h-4 text-neutral-500 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
          <strong>Sugerencia:</strong> Si trabajás en Argentina, seleccioná 
          <code class="px-2 py-1 rounded-md bg-neutral-200/70 dark:bg-neutral-700/70 text-neutral-800 dark:text-neutral-200 font-mono text-xs">America/Argentina/Buenos_Aires</code>
          para obtener la hora local correcta.
        </p>
      </div>
    </div>
  </div>
</div>

{{-- Alpine helper (sin cambios en la lógica) --}}
<script>
function timezoneSelector({ tz = '', list = [], recommended = '' }) {
  const normalized = list.map(item => {
    if (typeof item === 'string') {
      const parts = item.split('/');
      const label = parts.length ? parts[parts.length - 1].replace(/_/g, ' ') : item;
      const country = parts.length >= 2 ? parts[1].replace(/_/g, ' ') : parts[0].replace(/_/g, ' ');
      return { tz: item, country, label };
    } else {
      return {
        tz: item.tz ?? item.zone ?? '',
        country: item.country ?? (item.tz ? (item.tz.split('/')[1] || '') : ''),
        label: item.label ?? (item.tz ? item.tz.split('/').slice(-1)[0].replace(/_/g,' ') : '')
      };
    }
  });

  const countrySet = Array.from(new Set(normalized.map(i => i.country).filter(Boolean))).sort((a,b)=> a.localeCompare(b));
  return {
    tz,
    list: normalized,
    q: '',
    open: false,
    countryOpen: false,
    selectedCountry: 'Todos',
    countries: ['Todos', ...countrySet],
    recommended,
    get selectedCountryLabel() { return this.selectedCountry; },
    get filtered() {
      const q = (this.q || '').toLowerCase().trim();
      let items = this.list;
      if (this.selectedCountry && this.selectedCountry !== 'Todos') {
        items = items.filter(i => i.country === this.selectedCountry);
      }
      if (!q) return items;
      return items.filter(i =>
        i.label.toLowerCase().includes(q) ||
        i.tz.toLowerCase().includes(q) ||
        (i.country && i.country.toLowerCase().includes(q))
      );
    },
    selectCountry(c) {
      this.selectedCountry = c;
      this.countryOpen = false;
      this.open = true;
    },
    select(v){
      this.tz = v;
      this.q = '';
      this.open = false;
      this.countryOpen = false;
    },
    clearSelection(){
      this.tz = '';
      this.q = '';
    }
  };
}
</script>

  {{-- CARD: Título del sitio --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Identidad</h2>

    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Título del sitio</label>
    <div class="flex items-center gap-3">
      <input type="text" wire:model.defer="site_title"
             class="flex-1 rounded-lg border-neutral-300 dark:border-neutral-700
                    dark:bg-neutral-950 dark:text-neutral-100
                    focus:ring-2 focus:ring-indigo-500"
             placeholder="Ej. Rellenito Alfajores">
      <button wire:click="save"
              wire:loading.attr="disabled"
              class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white
                     dark:bg-indigo-500 dark:hover:bg-indigo-600 transition disabled:opacity-60">
        Guardar
      </button>
    </div>
  </div>

  {{-- CARD: Logo del comprobante --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Logo del Comprobante</h2>

    <div class="flex items-start gap-6">
      {{-- Dropzone / input --}}
      <label class="flex-1 border-2 border-dashed rounded-xl cursor-pointer
                     border-neutral-300 dark:border-neutral-600
                     hover:border-indigo-400 dark:hover:border-indigo-500
                     bg-neutral-50 dark:bg-neutral-950/40 transition p-6 text-center">
        <input type="file" class="hidden" wire:model="receipt_logo" accept=".png,.jpg,.jpeg,.webp" />
        <div class="space-y-2">
          <div class="mx-auto w-12 h-12 rounded-full border border-neutral-300 dark:border-neutral-600 flex items-center justify-center">
            <svg class="w-6 h-6 text-neutral-500 dark:text-neutral-400" viewBox="0 0 24 24" fill="none">
              <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <p class="font-medium text-neutral-700 dark:text-neutral-200">Arrastrá una imagen o hacé clic</p>
          <p class="text-xs text-neutral-500 dark:text-neutral-400">Acepta .png, .jpg, .jpeg, .webp (máx 2 MB)</p>
        </div>
      </label>

      {{-- Preview actual / nueva --}}
      <div class="w-48">
        <p class="text-xs uppercase tracking-wide text-neutral-600 dark:text-neutral-400 mb-2">Vista previa</p>

        @if ($receipt_logo)
          <img src="{{ $receipt_logo->temporaryUrl() }}" class="max-h-16 object-contain rounded shadow" alt="preview">
        @elseif($receipt_logo_url)
          <img src="{{ $receipt_logo_url }}" class="max-h-16 object-contain rounded shadow" alt="logo comprobante">
        @else
          <div class="h-16 rounded bg-neutral-200 dark:bg-neutral-700"></div>
        @endif

        <div class="mt-3 grid grid-cols-2 gap-2">
          <button wire:click="saveReceiptLogo"
                  wire:loading.attr="disabled"
                  class="px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm transition disabled:opacity-60">
            Guardar
          </button>
          <button wire:click="removeReceiptLogo"
                  wire:loading.attr="disabled"
                  class="px-3 py-2 rounded-lg border text-sm
                         hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
            Eliminar
          </button>
        </div>

        @error('receipt_logo')
          <div class="mt-3 text-sm text-rose-600">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <p class="mt-3 text-xs text-neutral-600 dark:text-neutral-400">
      Consejo: subí una versión horizontal con fondo transparente para que se vea bien en el ticket.
    </p>
  </div>
</div>