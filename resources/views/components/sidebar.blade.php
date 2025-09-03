@php
    $ordersUrl = \Illuminate\Support\Facades\Route::has('orders.index')
        ? route('orders.index')
        : route('orders.create');

    $fullName  = trim(Auth::user()->name ?? '');
    $firstName = $fullName !== '' ? preg_split('/\s+/', $fullName)[0] : null;
    $panelText = $firstName ? ($firstName.' Panel') : 'Panel';

    // Estados activo/inactivo (neutral/zinc, sin tinte azul)
    $active = 'text-neutral-900 dark:text-white font-semibold bg-neutral-100 dark:bg-neutral-800';
    $idle   = 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-50 dark:hover:bg-neutral-800/70';
@endphp

<style>
  :root {
    --sidebar-bg: #ffffff;
    --sidebar-border: #e5e5e5;
    --sidebar-text: #3f3f46;
    --sidebar-text-secondary: #737373;
    --sidebar-hover-bg: #fafafa;
    --sidebar-active-bg: #f5f5f5;
    --sidebar-button-bg: #fafafa;
    --sidebar-button-hover: #f5f5f5;
    --sidebar-ring: #e5e5e5;
    --sidebar-shadow: 0 1px 3px 0 rgba(0,0,0,.08), 0 1px 2px 0 rgba(0,0,0,.04);

    /* opcional: exponer ancho como var (si querés usarla) */
    --sb-width: 18rem;
  }
  .dark {
    --sidebar-bg: #0f0f10;
    --sidebar-border: #27272a;
    --sidebar-text: #fafafa;
    --sidebar-text-secondary: #a1a1aa;
    --sidebar-hover-bg: #18181b;
    --sidebar-active-bg: #27272a;
    --sidebar-button-bg: #18181b;
    --sidebar-button-hover: #27272a;
    --sidebar-ring: #3f3f46;
    --sidebar-shadow: 0 6px 16px -4px rgba(0,0,0,.45), 0 3px 6px -2px rgba(0,0,0,.35);
  }

  .nav-link{
    transform: translateZ(0);
    transition: all .28s cubic-bezier(.34,1.56,.64,1);
    will-change: transform;
    border-radius: 0.5rem;
    position: relative;
    overflow: hidden;
  }
  .nav-link:hover{
    transform: scale(1.02);
    box-shadow: var(--sidebar-shadow);
  }
  .nav-link::before{
    content:'';
    position:absolute; inset:0 auto 0 -100%;
    width:100%; height:100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.08), transparent);
    transition:left .5s ease; z-index:0;
  }
  .nav-link:hover::before{ left:100%; }

  .nav-icon{ width:1.5rem; height:1.5rem; transition:all .28s cubic-bezier(.34,1.56,.64,1); transform-origin:left center; position:relative; z-index:1; }
  .nav-link:hover .nav-icon{ transform:scale(1.4); }
  aside[data-collapsed="true"] .nav-icon{ transform-origin:center center; }

  .dark .nav-icon { filter: invert(1) hue-rotate(180deg) brightness(1.05) contrast(.9); }
  .dark .nav-icon.no-invert { filter: brightness(1.15) contrast(.95); }

  .sidebar-container{ background:var(--sidebar-bg); border-color:var(--sidebar-border); box-shadow:var(--sidebar-shadow); transition: all .3s ease; }
  .sidebar-header{ background:var(--sidebar-bg); border-color:var(--sidebar-border); }
  .sidebar-footer{ background:var(--sidebar-bg); border-color:var(--sidebar-border); }
  .sidebar-nav{ background:var(--sidebar-bg); }

  .sidebar-button{
    background:var(--sidebar-button-bg);
    border-color:var(--sidebar-border);
    color:var(--sidebar-text-secondary);
    transition: all .2s ease;
  }
  .sidebar-button:hover{
    background:var(--sidebar-button-hover);
    color:var(--sidebar-text);
    transform: translateY(-1px);
  }

  .user-avatar{ box-shadow: 0 0 0 2px var(--sidebar-ring) inset; transition: box-shadow .2s ease; }
  .user-info{ color:var(--sidebar-text); }
  .user-email{ color:var(--sidebar-text-secondary); }

  @media (max-width: 768px){
    .sidebar-container{ transform: translateX(-100%); transition: transform .3s ease; }
    .sidebar-container.mobile-open{ transform: translateX(0); }
  }

  aside[data-collapsed="true"] .sidebar-header{
    justify-content: center;
    padding-left: 0;
    padding-right: 0;
  }
  aside[data-collapsed="true"] .sidebar-header a{
    justify-content: center;
    width: 100%;
  }
  aside[data-collapsed="true"] .sidebar-header a > .user-info{
    display: none !important;
  }
</style>

<aside
  x-data="{
      collapsed: false,
      animating: false,
      init() {
        // estado inicial desde LS
        const saved = localStorage.getItem('sidebar:collapsed') === '1';
        this.collapsed = saved;

        // sincronizar inmediatamente la clase/var en <html>
        this.sync();

        // re-sincronizar después de cada navegación SPA
        window.addEventListener('livewire:navigated', () => this.sync());

        // observar cambios de tema (por si necesitás filtros de iconos)
        this.observeThemeChanges();
      },
      toggle() {
        if (this.animating) return;
        this.animating = true;
        this.collapsed = !this.collapsed;
        localStorage.setItem('sidebar:collapsed', this.collapsed ? '1' : '0');

        // seguir emitiendo el evento (si alguien más lo usa)
        window.dispatchEvent(new CustomEvent('sidebar:toggle', { detail: this.collapsed }));

        // sincronizar <html> al instante
        this.sync();

        setTimeout(() => this.animating = false, 520);
      },
      sync(){
        // Clase que usa tu layout (.sb-collapsed .app-main { margin-left: 5rem; })
        document.documentElement.classList.toggle('sb-collapsed', this.collapsed === true);
        // (opcional) variable para otras UIs
        document.documentElement.style.setProperty('--sb-width', this.collapsed ? '5rem' : '18rem');
      },
      observeThemeChanges() {
        const observer = new MutationObserver((m) => {
          for (const mu of m) if (mu.attributeName === 'class') this.updateIconFilters();
        });
        observer.observe(document.documentElement, { attributes:true, attributeFilter:['class'] });
        this.$nextTick(() => this.updateIconFilters());
      },
      updateIconFilters(){ /* hook si querés reglas especiales por icono */ }
  }"
  x-effect="sync()"  {{-- asegura sincronía si Alpine rehidrata --}}
  x-bind:data-collapsed="collapsed ? 'true' : 'false'"
  :class="collapsed ? 'w-20' : 'w-72'"
  class="sidebar-container fixed inset-y-0 left-0 z-40 overflow-hidden
         border-r transition-[width] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]">

  <div class="h-full flex flex-col">
    <!-- Header -->
    <div class="sidebar-header h-16 flex items-center px-4">
      <a href="{{ route('dashboard') }}" wire:navigate data-turbo="false"
         class="inline-flex items-center gap-3" title="{{ $panelText }}" aria-label="{{ $panelText }}">
        <x-application-mark x-bind:class="collapsed ? 'h-8 w-auto' : 'h-9 w-auto'" class="transition-all duration-300 filter drop-shadow-sm" />
        <span x-show="!collapsed" x-transition class="user-info font-bold text-lg truncate max-w-[9rem] sm:max-w-[12rem]">
          {{ $panelText }}
        </span>
      </a>
    </div>

    <!-- NAV -->
    <nav class="sidebar-nav flex-1 min-h-0 overflow-y-auto px-4 pt-4 pb-2 space-y-1
                scrollbar-thin scrollbar-thumb-neutral-300 dark:scrollbar-thumb-neutral-700"
         :class="animating ? 'pointer-events-none select-none' : ''">

      <!-- Dashboard -->
      <a href="{{ route('dashboard') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('dashboard') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Dashboard' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate relative z-1">Dashboard</span>
      </a>

      <!-- Crear pedido -->
      <a href="{{ route('orders.create') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('orders.create') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Crear pedido' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/crear-pedido.png') }}" alt="Crear pedido" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate relative z-1">Crear pedido</span>
      </a>

      <!-- Lista de pedidos -->
      <a href="{{ $ordersUrl }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('orders.index') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Lista de pedidos' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/pedidos.png') }}" alt="Pedidos" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate relative z-1">Lista de pedidos</span>
      </a>

      <!-- Productos -->
      <a href="{{ route('products.index') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('products.*') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Productos' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/productos.png') }}" alt="Productos" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate relative z-1">Productos</span>
      </a>

      <!-- Stock -->
      <a href="{{ route('stock.index') }}#stock" wire:navigate data-turbo="false"
         class="nav-link {{ request()->fullUrlIs(route('stock.index').'#stock') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Stock' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/stock.png') }}" alt="Stock" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate relative z-1">Stock</span>
      </a>

      <!-- Calcular costos -->
      <a href="{{ route('costing.calculator') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('costs.*') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Calcular costos' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/calcular-costos.png') }}" alt="Calcular costos" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate relative z-1">Calcular costos</span>
      </a>

      <!-- Configuración -->
      <a href="{{ route('settings') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('settings') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Configuración' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/configuraciones.png') }}" alt="Configuración" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate relative z-1">Configuración</span>
      </a>
    </nav>

    <!-- Toggle -->
    <div class="mt-3 pt-3 px-4">
      <button @click="toggle()" :disabled="animating"
              class="sidebar-button w-full flex items-center justify-center gap-2 text-xs py-2 px-3 rounded-lg border"
              :title="collapsed ? 'Expandir sidebar' : 'Contraer sidebar'">
        <svg x-show="!collapsed" x-transition.opacity class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path d="M11 5l-7 7 7 7M4 12h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg x-show="collapsed" x-transition.opacity class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path d="M13 19l7-7-7-7M4 12h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span x-show="!collapsed" x-transition.opacity class="font-medium">Contraer</span>
      </button>
    </div>

    <!-- Footer -->
    <div class="sidebar-footer p-4 border-t">
      <div class="flex items-center gap-3 mb-3" :class="collapsed ? 'justify-center' : ''">
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
          <img class="user-avatar w-9 h-9 rounded-full object-cover transition-all duration-200 hover:!shadow-[0_0_0_4px_var(--sidebar-ring)_inset]"
               src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
        @endif
        <div class="min-w-0" x-show="!collapsed" x-transition>
          <div class="user-info text-sm font-medium truncate">{{ Auth::user()->name }}</div>
          <div class="user-email text-xs truncate">{{ Auth::user()->email }}</div>
        </div>
      </div>

      <div class="grid" :class="collapsed ? 'grid-cols-1 gap-2' : 'grid-cols-2 gap-2'">
        <a href="{{ route('profile.show') }}" wire:navigate data-turbo="false"
           class="sidebar-button flex items-center justify-center gap-2 text-xs py-2 px-3 rounded-lg border"
           :title="collapsed ? 'Ver perfil' : null">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
            <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm7 9a7 7 0 0 0-14 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <span x-show="!collapsed" x-transition.opacity class="font-medium">Perfil</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button type="submit"
                  class="sidebar-button w-full flex items-center justify-center gap-2 text-xs py-2 px-3 rounded-lg border hover:!bg-red-50 dark:hover:!bg-red-900/20 hover:!text-red-600 dark:hover:!text-red-400"
                  :title="collapsed ? 'Cerrar sesión' : null">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
              <path d="M15 12H3m12 0-4-4m4 4-4 4M21 3v18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span x-show="!collapsed" x-transition.opacity class="font-medium">Salir</span>
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Overlay mobile -->
  <div x-show="!collapsed"
       x-transition:enter="transition-opacity ease-linear duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition-opacity ease-linear duration-300"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 bg-black/40 md:hidden"
       @click="collapsed = true"></div>
</aside>
