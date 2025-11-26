<div class="max-w-3xl mx-auto space-y-6">

  {{-- AVISO OK --}}
  @if (session('ok'))
    <div class="p-3 rounded-md bg-green-100 text-green-800
                dark:bg-green-900 dark:text-green-200">
      {{ session('ok') }}
    </div>
  @endif

  {{-- AVISO SUCCESS --}}
  @if (session('success'))
    <div class="p-3 rounded-md bg-green-100 text-green-800
                dark:bg-green-900 dark:text-green-200">
      {{ session('success') }}
    </div>
  @endif

  {{-- AVISO ERROR --}}
  @if (session('error'))
    <div class="p-3 rounded-md bg-red-100 text-red-800
                dark:bg-red-900 dark:text-red-200">
      {{ session('error') }}
    </div>
  @endif

  {{-- CARD: Información de la aplicación --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900
              flex flex-col items-center text-center">
    {{-- Logo centrado --}}
    <div class="mb-4">
      <img src="{{ route('branding.plan-logo') }}" alt="Gestior Logo" class="w-80 h-54">
    </div>

    {{-- Info --}}
    <p class="text-sm text-neutral-600 dark:text-neutral-400">
      Versión {{ config('app.version', '1.0.1') }}  
    </p>
    <p class="text-xs text-neutral-500 dark:text-neutral-500 mt-2">
      Herramienta de gestión y control económico
    </p>
  </div>

  {{-- CARD: Selección de temas --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">Temas</h2>
    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-6">
      Elegí un tema visual para personalizar tu experiencia
    </p>

    <div x-data="{
        t: @entangle('theme').live,
        isChanging: false,

        async selectTheme(themeId) {
            if (this.isChanging || this.t === themeId) return;

            this.isChanging = true;

            try {
                this.t = themeId;
                this.applyThemeImmediate(themeId);
                await $wire.setTheme(themeId);
                localStorage.setItem('theme', themeId);
            } catch (error) {
                console.error('Error al cambiar tema:', error);
            } finally {
                setTimeout(() => this.isChanging = false, 300);
            }
        },

        applyThemeImmediate(theme) {
            // Remover todas las clases de tema
            document.documentElement.classList.remove(
                'dark', 'theme-neon', 'theme-custom'
            );

            // Agregar la clase del nuevo tema
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (theme === 'neon') {
                // Neon hereda dark + agrega efectos neón
                document.documentElement.classList.add('dark', 'theme-neon');
            } else if (theme === 'custom') {
                document.documentElement.classList.add('theme-custom');
                // Aplicar el color personalizado guardado
                if (window.applyCustomColor) {
                    window.applyCustomColor('{{ $custom_color }}');
                }
            } else if (theme !== 'light') {
                document.documentElement.classList.add('theme-' + theme);
            }
        }
    }" class="space-y-6">

      {{-- Grid de temas --}}
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($availableThemes as $themeItem)
        <button type="button"
                @click="selectTheme('{{ $themeItem['id'] }}')"
                :disabled="isChanging"
                class="relative rounded-xl p-5 border-2 transition-all duration-200
                       bg-white dark:bg-neutral-900
                       hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500"
                :class="t === '{{ $themeItem['id'] }}'
                    ? 'border-indigo-600 shadow-sm'
                    : 'border-neutral-200 dark:border-neutral-700 hover:border-neutral-300 dark:hover:border-neutral-600'">

            {{-- Contenido --}}
            <div class="flex flex-col items-center gap-3 text-center">
                <span class="text-base font-semibold text-neutral-900 dark:text-neutral-100">
                    {{ $themeItem['name'] }}
                </span>
                <span class="text-xs text-neutral-600 dark:text-neutral-400">
                    {{ $themeItem['description'] }}
                </span>
            </div>

            {{-- Check mark cuando está seleccionado --}}
            <div x-show="t === '{{ $themeItem['id'] }}'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-50"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute top-3 right-3 w-5 h-5 rounded-full bg-indigo-600
                        flex items-center justify-center text-white">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </button>
        @endforeach
      </div>

      {{-- Color picker para tema personalizado --}}
      <div x-show="t === 'custom'"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0 -translate-y-2"
           x-transition:enter-end="opacity-100 translate-y-0"
           class="p-5 rounded-xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700">
        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
          Seleccioná tu color personalizado
        </label>

        {{-- Layout responsivo: columna en móvil, fila en desktop --}}
        <div class="flex flex-col sm:flex-row gap-3">
          {{-- Contenedor de color picker y texto --}}
          <div class="flex items-center gap-3 flex-1">
            <input type="color"
                   wire:model.defer="custom_color"
                   class="w-16 h-12 sm:w-20 rounded-lg border-2 border-neutral-300 dark:border-neutral-600
                          cursor-pointer transition-all hover:border-indigo-400 flex-shrink-0">
            <input type="text"
                   wire:model.defer="custom_color"
                   placeholder="#6366f1"
                   class="flex-1 h-12 rounded-lg px-4 py-2 border-2
                          border-neutral-300 dark:border-neutral-600
                          dark:bg-neutral-900 dark:text-neutral-100
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                          font-mono text-sm">
          </div>

          {{-- Botón de guardar --}}
          <button wire:click="saveCustomColor"
                  wire:loading.attr="disabled"
                  wire:loading.class="opacity-50 cursor-not-allowed"
                  class="w-full sm:w-auto px-5 py-3 rounded-lg bg-indigo-600 hover:bg-indigo-700
                         text-white font-medium text-sm transition-all duration-200 shadow-sm hover:shadow-md
                         disabled:opacity-60 disabled:cursor-not-allowed flex-shrink-0
                         dark:bg-indigo-500 dark:hover:bg-indigo-600">
            <span wire:loading.remove>Guardar Color</span>
            <span wire:loading>Guardando...</span>
          </button>
        </div>

        @error('custom_color')
          <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
        <p class="mt-3 text-xs text-neutral-600 dark:text-neutral-400">
          Ingresá un código hexadecimal válido (ejemplo: #6366f1) o usá el selector de color.
        </p>
      </div>

      <p class="text-xs text-neutral-600 dark:text-neutral-400">
        Los cambios se aplican instantáneamente sin recargar la página.
      </p>
    </div>
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

  {{-- CARD: Notificaciones de Stock --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900">
    <div class="flex items-center gap-3 mb-6">
      <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30">
        <i class="fas fa-bell text-indigo-600 dark:text-indigo-400"></i>
      </div>
      <div>
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Notificaciones de Stock</h2>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">Configurá alertas automáticas</p>
      </div>
    </div>

    <div class="space-y-5">
      {{-- Alerta de stock bajo --}}
      <div class="p-4 rounded-xl bg-amber-50/50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/30">
        <div class="flex items-start justify-between gap-4 mb-4">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <i class="fas fa-triangle-exclamation text-amber-600 dark:text-amber-400"></i>
              <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">Alerta de Stock Bajo</h3>
            </div>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
              Recibí una notificación cuando el stock esté por debajo del umbral configurado
            </p>
          </div>

          {{-- Toggle switch --}}
          <button type="button"
                  x-data="{ on: @entangle('notify_low_stock').live }"
                  @click="on = !on"
                  wire:ignore
                  role="switch"
                  :aria-checked="on.toString()"
                  class="relative inline-flex h-7 w-14 flex-shrink-0 items-center rounded-full border-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-colors ease-in-out duration-200"
                  :class="on ? 'bg-amber-600 border-amber-600' : 'bg-gray-200 dark:bg-neutral-700 border-gray-300 dark:border-neutral-600'">
            <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow-lg transition-transform ease-in-out duration-200"
                  :class="on ? 'translate-x-[1.875rem]' : 'translate-x-0.5'"></span>
          </button>
        </div>

        {{-- Threshold input --}}
        @if($notify_low_stock)
          <div class="mt-4 pt-4 border-t border-amber-200 dark:border-amber-900/30">
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              Umbral de stock bajo
            </label>
            <div class="flex items-center gap-3">
              <div class="relative flex-1">
                <input type="number"
                       wire:model.defer="low_stock_threshold"
                       min="1"
                       max="1000"
                       class="w-full px-4 py-2.5 rounded-lg border-neutral-300 dark:border-neutral-600
                              dark:bg-neutral-900 dark:text-neutral-100
                              focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                       placeholder="Ej: 5">
                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-neutral-500 dark:text-neutral-400 pointer-events-none">
                  unidades
                </div>
              </div>
              <div class="text-xs text-neutral-500 dark:text-neutral-400 flex-shrink-0">
                <i class="fas fa-info-circle mr-1"></i>
                Mínimo: 1
              </div>
            </div>
            @error('low_stock_threshold')
              <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-neutral-600 dark:text-neutral-400">
              Te notificaremos cuando un producto tenga {{ $low_stock_threshold }} o menos unidades en stock.
            </p>
          </div>
        @endif
      </div>

      {{-- Alerta sin stock --}}
      <div class="p-4 rounded-xl bg-rose-50/50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-900/30">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <i class="fas fa-circle-xmark text-rose-600 dark:text-rose-400"></i>
              <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">Alerta Sin Stock</h3>
            </div>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
              Recibí una notificación cuando un producto se quede sin stock (0 unidades)
            </p>
          </div>

          {{-- Toggle switch --}}
          <button type="button"
                  x-data="{ on: @entangle('notify_out_of_stock').live }"
                  @click="on = !on"
                  wire:ignore
                  role="switch"
                  :aria-checked="on.toString()"
                  class="relative inline-flex h-7 w-14 flex-shrink-0 items-center rounded-full border-2 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-colors ease-in-out duration-200"
                  :class="on ? 'bg-rose-600 border-rose-600' : 'bg-gray-200 dark:bg-neutral-700 border-gray-300 dark:border-neutral-600'">
            <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow-lg transition-transform ease-in-out duration-200"
                  :class="on ? 'translate-x-[1.875rem]' : 'translate-x-0.5'"></span>
          </button>
        </div>
      </div>

      {{-- Botón guardar --}}
      <div class="flex items-center justify-between pt-4 border-t border-neutral-200 dark:border-neutral-700">
        <div class="flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
          <i class="fas fa-lightbulb text-neutral-400"></i>
          <span>Las notificaciones se aplican a todos tus productos</span>
        </div>

        <button wire:click="saveStockNotifications"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
                class="group px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700
                       text-white font-medium text-sm transition-all duration-200 shadow-sm hover:shadow-md
                       focus:ring-2 focus:ring-indigo-500/20 disabled:opacity-60 disabled:cursor-not-allowed
                       dark:bg-indigo-500 dark:hover:bg-indigo-600">
          <span wire:loading.remove class="flex items-center gap-2">
            <i class="fas fa-save"></i>
            Guardar configuración
          </span>
          <span wire:loading class="flex items-center gap-2">
            <i class="fas fa-spinner fa-spin"></i>
            Guardando...
          </span>
        </button>
      </div>
    </div>
  </div>

  {{-- CARD: Google Calendar --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900">
    <div class="flex items-center gap-3 mb-6">
      <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30">
        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
          <path d="M19 4h-1V2h-2v2H8V2H6v2H5C3.9 4 3 4.9 3 6v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
        </svg>
      </div>
      <div>
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Google Calendar</h2>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">Sincronización automática de eventos</p>
      </div>
    </div>

    @if(auth()->user()->google_access_token && auth()->user()->google_refresh_token)
      {{-- CONECTADO --}}
      <div class="space-y-4">
        {{-- Estado --}}
        <div class="p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30">
          <div class="flex items-center gap-3 mb-2">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <p class="font-semibold text-neutral-900 dark:text-neutral-100">Cuenta conectada</p>
              @if(auth()->user()->google_email)
                <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ auth()->user()->google_email }}</p>
              @endif
            </div>
          </div>
        </div>

        {{-- Switch de sincronización --}}
        <div class="p-4 rounded-xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-1">
                Sincronización automática
              </h3>
              <p class="text-xs text-neutral-600 dark:text-neutral-400">
                Los pedidos agendados se guardarán automáticamente en tu Google Calendar
              </p>
            </div>
            <div class="flex-shrink-0">
              <form action="{{ route('google.toggle-sync') }}" method="POST" id="google-sync-form">
                @csrf
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox"
                         name="sync_enabled"
                         value="1"
                         class="sr-only peer"
                         {{ auth()->user()->google_calendar_sync_enabled ? 'checked' : '' }}
                         onchange="this.form.submit()">
                  <div class="w-11 h-6 bg-neutral-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300
                              dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-neutral-600
                              peer-checked:after:translate-x-full peer-checked:after:border-white
                              after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                              after:bg-white after:border-neutral-300 after:border after:rounded-full
                              after:h-5 after:w-5 after:transition-all dark:border-neutral-600
                              peer-checked:bg-blue-600"></div>
                </label>
              </form>
            </div>
          </div>
        </div>

        @if(auth()->user()->google_calendar_sync_enabled)
          {{-- Qué se sincroniza (solo si está habilitado) --}}
          <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-3">
              <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Se sincroniza automáticamente:
              </span>
            </h3>
            <div class="space-y-2">
              <div class="flex items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300">
                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span>Pedidos agendados (cuando creás o modificás un pedido con fecha)</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300">
                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span>Actualización automática al cambiar fechas o detalles</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300">
                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span>Eliminación automática al cancelar o eliminar pedidos</span>
              </div>
            </div>
          </div>
        @else
          {{-- Mensaje cuando está deshabilitado --}}
          <div class="p-4 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-900/30">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
              <div class="text-sm text-neutral-700 dark:text-neutral-300">
                <strong class="text-neutral-900 dark:text-neutral-100">Sincronización desactivada</strong>
                <p class="mt-1 text-xs">Los pedidos nuevos solo se guardarán en Gestior. Activá la sincronización para ver tus pedidos en Google Calendar.</p>
              </div>
            </div>
          </div>
        @endif

        {{-- Información de privacidad --}}
        <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30">
          <div class="flex items-start gap-2">
            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-xs text-neutral-700 dark:text-neutral-300">
              <strong>Privacidad:</strong> Solo accedemos a tu calendario para crear eventos de tus pedidos. No compartimos tu información con terceros.
              Podés desconectar tu cuenta en cualquier momento.
            </div>
          </div>
        </div>

        {{-- Botón desconectar --}}
        <form action="{{ route('google.disconnect') }}" method="POST">
          @csrf
          <button type="submit"
                  onclick="return confirm('¿Estás seguro de que querés desconectar tu cuenta de Google Calendar?\n\nLos eventos existentes permanecerán en tu calendario, pero no se sincronizarán nuevos pedidos.')"
                  class="w-full px-4 py-3 rounded-xl bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30
                         text-red-700 dark:text-red-400 font-medium text-sm transition-all duration-200
                         border border-red-200 dark:border-red-900/30">
            <div class="flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              <span>Desconectar Google Calendar</span>
            </div>
          </button>
        </form>
      </div>
    @else
      {{-- NO CONECTADO --}}
      <div class="space-y-4">
        {{-- Info qué obtendrás --}}
        <div class="p-4 rounded-xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700">
          <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Al conectar tu Google Calendar:</h3>
          <div class="space-y-2">
            <div class="flex items-start gap-2 text-sm text-neutral-700 dark:text-neutral-300">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <span>Tus pedidos agendados aparecerán automáticamente en tu calendario</span>
            </div>
            <div class="flex items-start gap-2 text-sm text-neutral-700 dark:text-neutral-300">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <span>Recibirás recordatorios automáticos antes de cada pedido</span>
            </div>
            <div class="flex items-start gap-2 text-sm text-neutral-700 dark:text-neutral-300">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <span>Verás tus pedidos en todos tus dispositivos sincronizados</span>
            </div>
            <div class="flex items-start gap-2 text-sm text-neutral-700 dark:text-neutral-300">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <span>Los cambios se actualizan en tiempo real</span>
            </div>
          </div>
        </div>

        {{-- Información de privacidad --}}
        <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30">
          <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <div class="text-sm text-neutral-700 dark:text-neutral-300">
              <strong class="text-neutral-900 dark:text-neutral-100">Tu privacidad es importante:</strong>
              <ul class="mt-2 space-y-1 text-xs">
                <li>• Solo accedemos a tu calendario para crear eventos de pedidos</li>
                <li>• No leemos tus otros eventos ni información personal</li>
                <li>• No compartimos tus datos con terceros</li>
                <li>• Podés desconectar en cualquier momento</li>
              </ul>
            </div>
          </div>
        </div>

        {{-- Botón conectar --}}
        <a href="{{ route('google.connect') }}"
           class="block w-full px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600
                  text-white font-medium text-sm transition-all duration-200 shadow-sm hover:shadow-md
                  text-center">
          <div class="flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/>
            </svg>
            <span>Conectar con Google Calendar</span>
          </div>
        </a>

        <p class="text-xs text-center text-neutral-500 dark:text-neutral-400">
          Serás redirigido a Google para autorizar el acceso de forma segura
        </p>
      </div>
    @endif
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
