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
    --sidebar-bg: #fafafa;
    --sidebar-border: #e4e4e7;
    --sidebar-text: #18181b;
    --sidebar-text-secondary: #71717a;
    --sidebar-hover-bg: #ffffff;
    --sidebar-active-bg: #f4f4f5;
    --sidebar-button-bg: #ffffff;
    --sidebar-button-hover: #f4f4f5;
    --sidebar-ring: #e4e4e7;
    --sidebar-shadow: 0 1px 2px 0 rgba(0,0,0,.03), 0 1px 3px 0 rgba(0,0,0,.02);
    --sidebar-shadow-hover: 0 4px 6px -1px rgba(0,0,0,.06), 0 2px 4px -1px rgba(0,0,0,.03);
    --sb-width: 18rem;
  }
  
  .dark {
    --sidebar-bg: #0a0a0b;
    --sidebar-border: #27272a;
    --sidebar-text: #fafafa;
    --sidebar-text-secondary: #a1a1aa;
    --sidebar-hover-bg: #18181b;
    --sidebar-active-bg: #27272a;
    --sidebar-button-bg: #18181b;
    --sidebar-button-hover: #27272a;
    --sidebar-ring: #3f3f46;
    --sidebar-shadow: 0 2px 4px 0 rgba(0,0,0,.15), 0 1px 2px 0 rgba(0,0,0,.1);
    --sidebar-shadow-hover: 0 8px 16px -4px rgba(0,0,0,.3), 0 4px 6px -2px rgba(0,0,0,.2);
  }

  .nav-link {
    transform: translateZ(0);
    transition: all .28s cubic-bezier(.34,1.56,.64,1);
    will-change: transform, box-shadow;
    border-radius: 0.75rem;
    position: relative;
    overflow: hidden;
  }
  
  .nav-link:hover {
    transform: translateY(-1px);
    box-shadow: var(--sidebar-shadow-hover);
  }
  
  .nav-link::before {
    content: '';
    position: absolute;
    inset: 0 auto 0 -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.06), transparent);
    transition: left .6s cubic-bezier(.34,1.56,.64,1);
    z-index: 0;
  }
  
  .nav-link:hover::before {
    left: 100%;
  }

  .nav-icon {
    width: 1.25rem;
    height: 1.25rem;
    transition: all .28s cubic-bezier(.34,1.56,.64,1);
    transform-origin: center center;
    position: relative;
    z-index: 1;
  }
  
  .nav-link:hover .nav-icon {
    transform: scale(1.15);
  }

  .dark .nav-icon {
    filter: invert(1) brightness(1.1) contrast(.95);
  }

  .sidebar-container {
    background: var(--sidebar-bg);
    border-color: var(--sidebar-border);
    box-shadow: var(--sidebar-shadow);
    transition: all .3s cubic-bezier(.16,1,.3,1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
  }

  .sidebar-header {
    background: var(--sidebar-bg);
    border-color: var(--sidebar-border);
  }

  .sidebar-footer {
    background: var(--sidebar-bg);
    border-color: var(--sidebar-border);
  }

  .sidebar-nav {
    background: var(--sidebar-bg);
  }

  .sidebar-button {
    background: var(--sidebar-button-bg);
    border-color: var(--sidebar-border);
    color: var(--sidebar-text-secondary);
    transition: all .2s cubic-bezier(.34,1.56,.64,1);
    box-shadow: var(--sidebar-shadow);
  }
  
  .sidebar-button:hover {
    background: var(--sidebar-button-hover);
    color: var(--sidebar-text);
    transform: translateY(-1px);
    box-shadow: var(--sidebar-shadow-hover);
  }

  .user-avatar {
    box-shadow: 0 0 0 2px var(--sidebar-ring) inset;
    transition: all .2s cubic-bezier(.34,1.56,.64,1);
  }
  
  .user-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 0 0 3px var(--sidebar-ring) inset;
  }
  
  .user-info {
    color: var(--sidebar-text);
  }
  
  .user-email {
    color: var(--sidebar-text-secondary);
  }

  /* Scrollbar personalizado más sutil */
  .custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: transparent transparent;
  }
  
  .custom-scrollbar::-webkit-scrollbar {
    width: 4px;
  }
  
  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }
  
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,.1);
    border-radius: 2px;
  }
  
  .dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,.1);
  }
  
  .custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,.2);
  }
  
  .dark .custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,.2);
  }

  /* Responsive breakpoints mejorados */
  @media (max-width: 1024px) {
    :root {
      --sb-width: 16rem;
    }
  }
  
  @media (max-width: 768px) {
    .sidebar-container {
      transform: translateX(-100%);
      transition: transform .3s cubic-bezier(.16,1,.3,1);
    }
    
    .sidebar-container.mobile-open {
      transform: translateX(0);
    }
    
    :root {
      --sb-width: 16rem;
    }
  }
  
  @media (max-width: 640px) {
    :root {
      --sb-width: 14rem;
    }
  }

  /* Mejoras para estados colapsados */
  aside[data-collapsed="true"] .sidebar-header {
    justify-content: center;
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  aside[data-collapsed="true"] .sidebar-header a {
    justify-content: center;
    width: 100%;
  }
  
  aside[data-collapsed="true"] .sidebar-header a > .user-info {
    display: none !important;
  }
  
  aside[data-collapsed="true"] .nav-link {
    justify-content: center;
  }
  
  aside[data-collapsed="true"] .nav-icon {
    transform-origin: center center;
  }

  /* Animaciones de entrada más suaves */
  .fade-slide-enter {
    animation: fadeSlideIn .3s cubic-bezier(.34,1.56,.64,1) forwards;
  }
  
  @keyframes fadeSlideIn {
    from {
      opacity: 0;
      transform: translateX(-8px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
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
        
        // Ajustar altura en móviles para evitar scroll
        this.adjustMobileHeight();
        window.addEventListener('resize', () => this.adjustMobileHeight());
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
      updateIconFilters() { /* hook si querés reglas especiales por icono */ },
      adjustMobileHeight() {
        if (window.innerWidth <= 768) {
          const vh = window.innerHeight * 0.01;
          document.documentElement.style.setProperty('--vh', vh + 'px');
        }
      }
  }"
  x-effect="sync()"  {{-- asegura sincronía si Alpine rehidrata --}}
  x-bind:data-collapsed="collapsed ? 'true' : 'false'"
  :class="collapsed ? 'w-20' : 'w-72 sm:w-72 lg:w-72'"
  class="sidebar-container fixed inset-y-0 left-0 z-50 overflow-hidden
         border-r transition-[width] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
  style="height: 100vh; height: calc(var(--vh, 1vh) * 100);">

  <div class="h-full flex flex-col">
    <!-- Header -->
    <div class="sidebar-header flex-shrink-0 h-16 flex items-center px-4 border-b">
      <a href="{{ route('inicio') }}" wire:navigate data-turbo="false"
         class="inline-flex items-center gap-3 transition-all duration-300 hover:scale-105" 
         title="{{ $panelText }}" aria-label="{{ $panelText }}">
        <x-application-mark x-bind:class="collapsed ? 'h-7 w-auto' : 'h-8 w-auto'" 
                          class="transition-all duration-300 filter drop-shadow-sm" />
@php
    // Determinar etiqueta legible para el nivel/rol
    $levelLabel = null;

    if (Auth::check()) {
        $roles = Auth::user()->getRoleNames()->toArray();
        $firstRole = $roles[0] ?? null;

        if ($firstRole) {
            $roleMap = [
                'company' => 'Empresas',
                'admin'   => 'Sucursal',
                'user'    => 'Usuario',
                'master'  => 'Master',
            ];
            $levelLabel = $roleMap[$firstRole] ?? Str::title(str_replace(['-', '_'], ' ', $firstRole));
        } else {
            switch (Auth::user()->hierarchy_level) {
                case \App\Models\User::HIERARCHY_MASTER:
                    $levelLabel = 'Master';
                    break;
                case \App\Models\User::HIERARCHY_COMPANY:
                    $levelLabel = 'Empresa';
                    break;
                case \App\Models\User::HIERARCHY_ADMIN:
                    $levelLabel = 'Sucursal';
                    break;
                case \App\Models\User::HIERARCHY_USER:
                    $levelLabel = 'Usuario';
                    break;
                default:
                    $levelLabel = null;
            }
        }
    }
@endphp

<span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
      class="user-info font-bold text-lg truncate max-w-[8rem] sm:max-w-[10rem] lg:max-w-[12rem] flex items-baseline gap-1">
  <span class="truncate">{{ $panelText }}</span>

  @if($levelLabel)
    <span class="text-lg text-neutral-500 dark:text-neutral-400 font-bold truncate"
          style="margin-left: 0.25rem;">
      {{ $levelLabel }}
    </span>
  @endif
</span>

      </a>
    </div>

    <!-- NAV -->
    <nav class="sidebar-nav flex-1 min-h-0 overflow-y-auto px-3 pt-4 pb-2 space-y-1 custom-scrollbar"
         :class="animating ? 'pointer-events-none select-none' : ''">

      <!-- Dashboard -->
      <a href="{{ route('dashboard') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('dashboard') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Dashboard' : null">
        <span class="shrink-0 flex items-center justify-center w-7 h-7">
          <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
              class="text-sm font-semibold truncate relative z-1">Dashboard</span>
      </a>

      <!-- Crear pedido -->
      <a href="{{ route('orders.create') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('orders.create') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Crear pedido' : null">
        <span class="shrink-0 flex items-center justify-center w-7 h-7">
          <img src="{{ asset('images/crear-pedido.png') }}" alt="Crear pedido" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
              class="text-sm font-semibold truncate relative z-1">Crear pedido</span>
      </a>

      <!-- Lista de pedidos -->
      <a href="{{ $ordersUrl }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('orders.index') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Lista de pedidos' : null">
        <span class="shrink-0 flex items-center justify-center w-7 h-7">
          <img src="{{ asset('images/pedidos.png') }}" alt="Pedidos" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
              class="text-sm font-semibold truncate relative z-1">Lista de pedidos</span>
      </a>

      <!-- Productos -->
      <a href="{{ route('products.index') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('products.*') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Productos' : null">
        <span class="shrink-0 flex items-center justify-center w-7 h-7">
          <img src="{{ asset('images/productos.png') }}" alt="Productos" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
              class="text-sm font-semibold truncate relative z-1">Productos</span>
      </a>

      <!-- Servicios -->
      <a href="{{ route('services.index') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('services.*') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Servicios' : null">
        <span class="shrink-0 flex items-center justify-center w-7 h-7">
          <img src="{{ asset('images/servicios.png') }}" alt="Servicios" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
              class="text-sm font-semibold truncate relative z-1">Servicios</span>
      </a>

      <!-- Clientes -->
      <a href="{{ route('clients.index') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('clients.*') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Clientes' : null">
        <span class="shrink-0 flex items-center justify-center w-7 h-7">
          <img src="{{ asset('images/clientes.png') }}" alt="Clientes" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter"
              class="text-sm font-semibold truncate relative z-1">Clientes</span>
      </a>

      <!-- Métodos de Pago -->
      <a href="{{ route('payment-methods.index') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('payment-methods.*') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Métodos de Pago' : null">
        <span class="shrink-0 flex items-center justify-center w-7 h-7">
          <img src="{{ asset('images/payment.png') }}" alt="Métodos de Pago" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter"
              class="text-sm font-semibold truncate relative z-1">Métodos de Pago</span>
      </a>

      <!-- Stock -->
      <a href="{{ route('stock.index') }}#stock" wire:navigate data-turbo="false"
         class="nav-link {{ request()->fullUrlIs(route('stock.index').'#stock') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Stock' : null">
        <span class="shrink-0 flex items-center justify-center w-7 h-7">
          <img src="{{ asset('images/stock.png') }}" alt="Stock" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
              class="text-sm font-semibold truncate relative z-1">Stock</span>
      </a>

      <!-- Calcular costos -->
      <a href="{{ route('costing.calculator') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('costs.*') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Calcular costos' : null">
        <span class="shrink-0 flex items-center justify-center w-7 h-7">
          <img src="{{ asset('images/calcular-costos.png') }}" alt="Calcular costos" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
              class="text-sm font-semibold truncate relative z-1">Calcular costos</span>
      </a>

@auth
    @if(auth()->user()->isMaster() || auth()->user()->isCompany())
        <a href="{{ route('company.branches.index') }}" wire:navigate data-turbo="false"
           class="nav-link {{ request()->routeIs('company.branches.*') ? $active : $idle }}"
           :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
           :title="collapsed ? 'Sucursales' : null">
          <span class="shrink-0 flex items-center justify-center w-7 h-7">
            <img src="{{ asset('images/sucursales.png') }}" alt="Sucursales" class="nav-icon">
          </span>
          <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
                class="text-sm font-semibold truncate relative z-1">Sucursales</span>
        </a>
    @endif
@endauth

@auth
    @if(auth()->user()->isMaster() || auth()->user()->isCompany())
        <a href="{{ route('company.employees.index') }}" wire:navigate data-turbo="false"
           class="nav-link {{ request()->routeIs('company.branches.*') ? $active : $idle }}"
           :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
           :title="collapsed ? 'Personal' : null">
          <span class="shrink-0 flex items-center justify-center w-7 h-7">
            <img src="{{ asset('images/empleados.png') }}" alt="Personal" class="nav-icon">
          </span>
          <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
                class="text-sm font-semibold truncate relative z-1">Personal</span>
        </a>
    @endif
@endauth



@auth
    @if(auth()->user()->isMaster())
        <!-- Master - Agregar Usuarios -->
        <a href="{{ route('master.invitations.index') }}" wire:navigate data-turbo="false"
           class="nav-link {{ request()->routeIs('master.invitations.*') ? $active : $idle }}"
           :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
           :title="collapsed ? 'Gestionar usuarios' : null">
          <span class="shrink-0 flex items-center justify-center w-7 h-7">
            <img src="{{ asset('images/agregar-user.png') }}" alt="Generar usuarios" class="nav-icon">
          </span>
          <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
                class="text-sm font-semibold truncate relative z-1">Generar usuarios</span>
        </a>
    @endif
@endauth

@auth
    @if(auth()->user()->isMaster())
    <!-- Master - Gestionar usuarios -->
    <a href="{{ route('master.users.index') }}" wire:navigate data-turbo="false"
       class="nav-link {{ request()->routeIs('master.users.*') ? $active : $idle }}"
       :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
       :title="collapsed ? 'Gestionar usuarios' : null">
      <span class="shrink-0 flex items-center justify-center w-7 h-7">
        <img src="{{ asset('images/gestionar-user.png') }}" alt="Gestionar usuarios" class="nav-icon">
      </span>
      <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
            class="text-sm font-semibold truncate relative z-1">Gestionar usuarios</span>
    </a>
@endif
@endauth


      <!-- Configuración -->
      <a href="{{ route('settings') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('settings') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Configuración' : null">
        <span class="shrink-0 flex items-center justify-center w-7 h-7">
          <img src="{{ asset('images/configuraciones.png') }}" alt="Configuración" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
              class="text-sm font-semibold truncate relative z-1">Configuración</span>
      </a>

      <!-- Soporte -->
      <a href="{{ route('support.index') }}" wire:navigate data-turbo="false"
         class="nav-link {{ request()->routeIs('support.*') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Soporte' : null">
        <span class="shrink-0 flex items-center justify-center w-7 h-7">
          <img src="{{ asset('images/soporte.png') }}" alt="Soporte" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter" 
              class="text-sm font-semibold truncate relative z-1">Soporte</span>
      </a>
    </nav>

    <!-- Toggle -->
    <div class="flex-shrink-0 pt-2 pb-3 px-3">
      <button @click="toggle()" :disabled="animating"
              class="sidebar-button w-full flex items-center justify-center gap-2 text-xs py-2.5 px-3 rounded-xl border"
              :title="collapsed ? 'Expandir sidebar' : 'Contraer sidebar'">
        <svg x-show="!collapsed" x-transition:enter="fade-slide-enter" class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path d="M11 5l-7 7 7 7M4 12h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg x-show="collapsed" x-transition:enter="fade-slide-enter" class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path d="M13 19l7-7-7-7M4 12h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span x-show="!collapsed" x-transition:enter="fade-slide-enter" class="font-medium">Contraer</span>
      </button>
    </div>

    <!-- Footer -->
    <div class="sidebar-footer flex-shrink-0 p-3 border-t">
      <div class="flex items-center gap-3 mb-3" :class="collapsed ? 'justify-center' : ''">
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
          <img class="user-avatar w-9 h-9 rounded-full object-cover cursor-pointer"
               src="{{ Auth::user()->profile_photo_url }}" 
               alt="{{ Auth::user()->name }}"
               :title="collapsed ? '{{ Auth::user()->name }}' : null">
        @endif
        <div class="min-w-0 overflow-hidden" x-show="!collapsed" x-transition:enter="fade-slide-enter">
          <div class="user-info text-sm font-medium truncate">{{ Auth::user()->name }}</div>
          <div class="user-email text-xs truncate">{{ Auth::user()->email }}</div>
        </div>
      </div>

      <div class="grid gap-2" :class="collapsed ? 'grid-cols-1' : 'grid-cols-2'">
        <a href="{{ route('profile.show') }}" wire:navigate data-turbo="false"
           class="sidebar-button flex items-center justify-center gap-2 text-xs py-2 px-3 rounded-lg border"
           :title="collapsed ? 'Ver perfil' : null">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
            <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm7 9a7 7 0 0 0-14 0" 
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <span x-show="!collapsed" x-transition:enter="fade-slide-enter" class="font-medium">Perfil</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button type="submit"
                  class="sidebar-button w-full flex items-center justify-center gap-2 text-xs py-2 px-3 rounded-lg border hover:!bg-red-50 dark:hover:!bg-red-900/20 hover:!text-red-600 dark:hover:!text-red-400 hover:!border-red-200 dark:hover:!border-red-800"
                  :title="collapsed ? 'Cerrar sesión' : null">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
              <path d="M15 12H3m12 0-4-4m4 4-4 4M21 3v18" 
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span x-show="!collapsed" x-transition:enter="fade-slide-enter" class="font-medium">Salir</span>
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
       class="fixed inset-0 bg-black/40 md:hidden z-40"
       @click="collapsed = true"></div>
</aside>
