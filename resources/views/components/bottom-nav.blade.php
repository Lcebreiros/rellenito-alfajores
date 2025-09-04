@php
  use Illuminate\Support\Facades\Route;

  // Helper de rutas seguras
  $safeRoute = function (array $names, string $fallback = '#') {
      foreach ($names as $n) if (Route::has($n)) return route($n);
      return $fallback;
  };

  // URLs
  $ordersUrl   = Route::has('orders.index') ? route('orders.index') : route('orders.create');
  $settingsUrl = $safeRoute(['settings', 'settings.index', 'profile.show'], '#');
  $profileUrl  = $safeRoute(['profile.show'], '#');

  // helpers
  $isActive = function ($pattern) { return request()->routeIs($pattern); };

  // clases
  $bar = 'fixed bottom-0 inset-x-0 z-50 md:hidden
          border-t border-neutral-200/50 dark:border-neutral-800/50
          bg-white/80 dark:bg-neutral-950/80 backdrop-blur-xl
          supports-[backdrop-filter]:bg-white/70 supports-[backdrop-filter]:dark:bg-neutral-950/70';
  $wrap = 'mx-auto max-w-3xl px-2 sm:px-3 relative';
  $grid = 'grid grid-cols-6 items-center h-16 gap-1 relative z-10';
  $pillLabel = 'text-[10px] sm:text-[11px] font-semibold leading-tight tracking-tight text-center whitespace-normal break-words min-w-0';

  // índice activo
  $activeIndex = 0;
  if ($isActive('orders.create')) $activeIndex = 1;
  elseif ($isActive('orders.index')) $activeIndex = 2;
  elseif ($isActive('products.*')) $activeIndex = 3;
  elseif ($isActive('stock.index')) $activeIndex = 4;
@endphp

<nav
  x-data="{
    moreOpen: false,
    activeIndex: {{ $activeIndex }},
    updateIndicator() {
      const tabsWrap = this.$refs.tabsWrap
      const ind = this.$refs.indicator
      const track = this.$refs.track
      if (!tabsWrap || !ind || !track) return
      const tabs = tabsWrap.querySelectorAll('[data-tab]')
      if (!tabs.length) return

      const i = Math.max(0, Math.min(this.activeIndex, tabs.length - 1))
      const rect = tabs[i].getBoundingClientRect()
      const parentRect = track.getBoundingClientRect()
      ind.style.width = `${rect.width}px`
      ind.style.transform = `translateX(${rect.left - parentRect.left}px)`
    },
    init() {
      this.$nextTick(() => this.updateIndicator())
      this.$watch('activeIndex', () => this.updateIndicator())
      const ro = new ResizeObserver(() => this.updateIndicator())
      ro.observe(this.$refs.track)
      window.addEventListener('resize', this.updateIndicator)
      window.addEventListener('livewire:navigated', () => this.updateIndicator())
    },
  }"
  class="{{ $bar }}"
  aria-label="Navegación inferior"
>
  {{-- PASTILLA --}}
  <div class="absolute inset-x-0 top-0 h-16 px-2 sm:px-3 pointer-events-none">
    <div class="mx-auto max-w-3xl h-full relative" x-ref="track">
      <div x-ref="indicator"
           class="absolute top-2 h-12 rounded-2xl
                  bg-gradient-to-br from-white to-neutral-50 
                  dark:from-neutral-100 dark:to-neutral-200
                  shadow-lg shadow-neutral-900/10 dark:shadow-black/20
                  ring-1 ring-black/[0.08] dark:ring-white/[0.1]
                  transition-[transform,width] duration-400
                  ease-[cubic-bezier(0.34,1.56,0.64,1)]
                  will-change-transform">
        <div class="absolute inset-x-2 top-0.5 h-px bg-white/60 rounded-full"></div>
      </div>
    </div>
  </div>

  <div class="{{ $wrap }}">
    <div class="{{ $grid }}" x-ref="tabsWrap">
      {{-- Dashboard --}}
      <a href="{{ route('dashboard') }}" wire:navigate data-turbo="false"
         @click="activeIndex = 0"
         data-tab
         class="group flex justify-center touch-manipulation transition-all duration-200 hover:scale-110 active:scale-95"
         aria-current="{{ $isActive('dashboard') ? 'page' : 'false' }}">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px] transition-all duration-300">
          <div class="relative">
            <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard"
                 class="w-6 h-6 object-contain transition-all duration-300 {{ $isActive('dashboard') ? 'scale-110 drop-shadow-sm' : 'opacity-70 group-hover:opacity-90 group-hover:scale-105' }}">
            @if($isActive('dashboard'))
              <div class="absolute inset-0 w-6 h-6 bg-blue-400/20 rounded-full blur-sm animate-pulse"></div>
            @endif
          </div>
          <span class="{{ $pillLabel }} transition-all duration-300 {{ $isActive('dashboard') ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300' }}">
            Dashboard
          </span>
        </div>
      </a>

      {{-- Crear pedido --}}
      <a href="{{ route('orders.create') }}" wire:navigate data-turbo="false"
         @click="activeIndex = 1"
         data-tab
         class="group flex justify-center touch-manipulation transition-all duration-200 hover:scale-110 active:scale-95"
         aria-current="{{ $isActive('orders.create') ? 'page' : 'false' }}">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px] transition-all duration-300">
          <div class="relative">
            <img src="{{ asset('images/crear-pedido.png') }}" alt="Crear pedido"
                 class="w-6 h-6 object-contain transition-all duration-300 {{ $isActive('orders.create') ? 'scale-110 drop-shadow-sm' : 'opacity-70 group-hover:opacity-90 group-hover:scale-105' }}">
            @if($isActive('orders.create'))
              <div class="absolute inset-0 w-6 h-6 bg-green-400/20 rounded-full blur-sm animate-pulse"></div>
            @endif
          </div>
          <span class="{{ $pillLabel }} transition-all duration-300 {{ $isActive('orders.create') ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300' }}">
            Crear
          </span>
        </div>
      </a>

      {{-- Lista de pedidos --}}
      <a href="{{ $ordersUrl }}" wire:navigate data-turbo="false"
         @click="activeIndex = 2"
         data-tab
         class="group flex justify-center touch-manipulation transition-all duration-200 hover:scale-110 active:scale-95"
         aria-current="{{ $isActive('orders.index') ? 'page' : 'false' }}">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px] transition-all duration-300">
          <div class="relative">
            <img src="{{ asset('images/pedidos.png') }}" alt="Pedidos"
                 class="w-6 h-6 object-contain transition-all duration-300 {{ $isActive('orders.index') ? 'scale-110 drop-shadow-sm' : 'opacity-70 group-hover:opacity-90 group-hover:scale-105' }}">
            @if($isActive('orders.index'))
              <div class="absolute inset-0 w-6 h-6 bg-blue-400/20 rounded-full blur-sm animate-pulse"></div>
            @endif
          </div>
          <span class="{{ $pillLabel }} transition-all duration-300 {{ $isActive('orders.index') ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300' }}">
            Pedidos
          </span>
        </div>
      </a>

      {{-- Productos --}}
      <a href="{{ route('products.index') }}" wire:navigate data-turbo="false"
         @click="activeIndex = 3"
         data-tab
         class="group flex justify-center touch-manipulation transition-all duration-200 hover:scale-110 active:scale-95"
         aria-current="{{ $isActive('products.*') ? 'page' : 'false' }}">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px] transition-all duration-300">
          <div class="relative">
            <img src="{{ asset('images/productos.png') }}" alt="Productos"
                 class="w-6 h-6 object-contain transition-all duration-300 {{ $isActive('products.*') ? 'scale-110 drop-shadow-sm' : 'opacity-70 group-hover:opacity-90 group-hover:scale-105' }}">
            @if($isActive('products.*'))
              <div class="absolute inset-0 w-6 h-6 bg-purple-400/20 rounded-full blur-sm animate-pulse"></div>
            @endif
          </div>
          <span class="{{ $pillLabel }} transition-all duration-300 {{ $isActive('products.*') ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300' }}">
            Productos
          </span>
        </div>
      </a>

      {{-- Stock --}}
      <a href="{{ route('stock.index') }}#stock" wire:navigate data-turbo="false"
         @click="activeIndex = 4"
         data-tab
         class="group flex justify-center touch-manipulation transition-all duration-200 hover:scale-110 active:scale-95"
         aria-current="{{ $isActive('stock.index') ? 'page' : 'false' }}">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px] transition-all duration-300">
          <div class="relative">
            <img src="{{ asset('images/stock.png') }}" alt="Stock"
                 class="w-6 h-6 object-contain transition-all duration-300 {{ $isActive('stock.index') ? 'scale-110 drop-shadow-sm' : 'opacity-70 group-hover:opacity-90 group-hover:scale-105' }}">
            @if($isActive('stock.index'))
              <div class="absolute inset-0 w-6 h-6 bg-orange-400/20 rounded-full blur-sm animate-pulse"></div>
            @endif
          </div>
          <span class="{{ $pillLabel }} transition-all duration-300 {{ $isActive('stock.index') ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300' }}">
            Stock
          </span>
        </div>
      </a>

      {{-- Más --}}
      <div class="relative flex justify-center">
        <button type="button"
                @click.stop="activeIndex = 5; moreOpen = !moreOpen"
                @keydown.escape.window="moreOpen = false"
                @click.outside="moreOpen = false"
                data-tab
                class="group touch-manipulation transition-all duration-200 hover:scale-110 active:scale-95"
                aria-haspopup="menu"
                :aria-expanded="moreOpen">
          <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px] transition-all duration-300"
               :class="moreOpen ? 'scale-110' : ''">
            <div class="relative">
              @auth
                <img class="w-6 h-6 rounded-full object-cover ring-2 transition-all duration-300"
                     :class="moreOpen ? 'ring-white dark:ring-neutral-200 scale-110 drop-shadow-sm' : 'ring-transparent opacity-70 group-hover:opacity-90 group-hover:scale-105'"
                     src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
              @else
                <div class="w-6 h-6 rounded-full bg-neutral-200 dark:bg-neutral-700"></div>
              @endauth
              <div x-show="moreOpen" class="absolute inset-0 w-6 h-6 bg-indigo-400/20 rounded-full blur-sm animate-pulse"></div>
            </div>
            <span class="{{ $pillLabel }} transition-all duration-300"
                  :class="moreOpen ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300'">
              Más
            </span>
          </div>
        </button>

        {{-- Popover --}}
        <div x-cloak x-show="moreOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             @click.stop
             class="absolute bottom-[calc(100%+12px)] right-0 w-56 rounded-3xl
                    border border-neutral-200/60 dark:border-neutral-700/60
                    bg-white/95 dark:bg-neutral-900/95 backdrop-blur-2xl
                    shadow-2xl shadow-neutral-900/20 dark:shadow-black/40
                    overflow-hidden z-50 ring-1 ring-black/5 dark:ring-white/10">
          {{-- Header --}}
          <div class="px-4 py-3 border-b border-neutral-200/50 dark:border-neutral-800/50 bg-neutral-50/50 dark:bg-neutral-800/30">
            <div class="flex items-center gap-3">
              @auth
                <img class="w-8 h-8 rounded-full object-cover ring-2 ring-neutral-300 dark:ring-neutral-600"
                     src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
              @else
                <div class="w-8 h-8 rounded-full bg-neutral-200 dark:bg-neutral-700"></div>
              @endauth
              <div class="flex-1 min-w-0">
                @auth
                  <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 truncate">{{ Auth::user()->name }}</p>
                  <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ Auth::user()->email }}</p>
                @else
                  <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Invitado</p>
                @endauth
              </div>
            </div>
          </div>

          {{-- Items --}}
          <div class="py-2">
            {{-- Configuración (fall back seguro) --}}
            <a href="{{ $settingsUrl }}" wire:navigate data-turbo="false"
               @click="moreOpen = false"
               class="flex items-center gap-3 px-4 py-3 text-sm group
                      hover:bg-neutral-100/80 dark:hover:bg-neutral-800/50
                      active:bg-neutral-200/80 dark:active:bg-neutral-700/50 transition-all duration-200 touch-manipulation">
              <div class="w-8 h-8 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center group-hover:bg-neutral-200 dark:group-hover:bg-neutral-700 transition-colors">
                <img src="{{ asset('images/configuraciones.png') }}" alt="Configuración" class="w-4 h-4 opacity-75">
              </div>
              <span class="font-medium text-neutral-700 dark:text-neutral-300 group-hover:text-neutral-900 dark:group-hover:text-neutral-100">Configuración</span>
            </a>

            {{-- Perfil (fall back seguro) --}}
            <a href="{{ $profileUrl }}" wire:navigate data-turbo="false"
               @click="moreOpen = false"
               class="flex items-center gap-3 px-4 py-3 text-sm group
                      hover:bg-neutral-100/80 dark:hover:bg-neutral-800/50
                      active:bg-neutral-200/80 dark:active:bg-neutral-700/50 transition-all duration-200 touch-manipulation">
              <div class="w-8 h-8 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center group-hover:bg-neutral-200 dark:group-hover:bg-neutral-700 transition-colors">
                @auth
                  <img class="w-5 h-5 rounded-lg object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                @else
                  <img src="{{ asset('images/productos.png') }}" alt="Perfil" class="w-4 h-4 opacity-75">
                @endauth
              </div>
              <span class="font-medium text-neutral-700 dark:text-neutral-300 group-hover:text-neutral-900 dark:group-hover:text-neutral-100">Perfil</span>
            </a>
          </div>

          <div class="mx-4 border-t border-neutral-200/60 dark:border-neutral-800/60"></div>

          {{-- Salir --}}
          <div class="py-2">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit"
                      @click="moreOpen = false"
                      class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm group
                             hover:bg-red-50/80 dark:hover:bg-red-950/30
                             active:bg-red-100/80 dark:active:bg-red-900/30 transition-all duration-200 touch-manipulation">
                <div class="w-8 h-8 rounded-xl bg-red-50 dark:bg-red-950/50 flex items-center justify-center group-hover:bg-red-100 dark:group-hover:bg-red-900/50 transition-colors">
                  <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                </div>
                <span class="font-medium text-red-600 dark:text-red-400 group-hover:text-red-700 dark:group-hover:text-red-300">Cerrar sesión</span>
              </button>
            </form>
          </div>
        </div>
      </div>
      {{-- /Más --}}
    </div>
  </div>

  {{-- Safe area iOS --}}
  <div class="h-[env(safe-area-inset-bottom)] min-h-[8px] bg-gradient-to-t from-neutral-50/50 to-transparent dark:from-neutral-900/50"></div>
</nav>
