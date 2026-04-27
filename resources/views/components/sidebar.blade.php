@php
    $ordersUrl = \Illuminate\Support\Facades\Route::has('orders.index')
        ? route('orders.index')
        : route('orders.create');

    $fullName  = trim(Auth::user()->name ?? '');
    $firstName = $fullName !== '' ? preg_split('/\s+/', $fullName)[0] : null;
    $panelText = $firstName ? ($firstName.' Panel') : 'Panel';

    // Estados activo/inactivo — colores controlados por CSS del sidebar púrpura
    $active = 'font-semibold sidebar-nav-active';
    $idle   = 'sidebar-nav-idle';
@endphp

<style>
  /* ─── Sidebar púrpura — paleta Nexum ─────────────────────────────── */
  :root {
    --sb-purple-from: #a78bfa;
    --sb-purple-mid:  #7c3aed;
    --sb-purple-to:   #5b21b6;
  }

  /* ─── Contenedor principal ───────────────────────────────────────── */
  .sidebar-container {
    background: #7c3aed;
    border-radius: 0 1.5rem 1.5rem 0; /* redondeo hacia el contenido */
    box-shadow: none;
    border: none;
    /* Solo width anima — overlay mode, sin mover el contenido */
    transition: width .28s cubic-bezier(.16,1,.3,1);
    will-change: width;
    overflow: hidden;
  }

  /* ─── Header / footer / nav: fondo transparente (hereda gradiente) ─ */
  .sidebar-header,
  .sidebar-footer,
  .sidebar-toggle,
  .sidebar-nav {
    background: transparent;
    border-color: transparent;
  }

  /* ─── Íconos: siempre blancos ────────────────────────────────────── */
  .nav-icon {
    width: 1.25rem;
    height: 1.25rem;
    position: relative;
    z-index: 1;
    /* PNG icons → blancos */
    filter: brightness(0) invert(1);
    transition: transform .22s cubic-bezier(.34,1.56,.64,1);
    transform-origin: center;
  }

  /* SVG con currentColor: heredan el color del texto (blanco) */
  .nav-link svg.nav-icon {
    filter: none;
    color: inherit;
  }

  /* ─── Links de navegación ────────────────────────────────────────── */
  .nav-link {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    color: rgba(255,255,255,0.78);
    border-radius: 0.875rem;
    position: relative;
    overflow: hidden;
    transition: background .18s ease, color .18s ease;
  }

  .nav-link:hover {
    background: transparent;
    color: #ffffff;
  }

  /* Barra sutil en hover */
  .nav-link:hover::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 45%;
    background: rgba(255,255,255,0.4);
    border-radius: 0 3px 3px 0;
  }

  .nav-link:hover .nav-icon {
    transform: scale(1.12);
  }

  /* ─── Item activo: sin contenedor, solo barra + texto marcado ─────── */
  .sidebar-nav-active {
    background: transparent !important;
    color: #ffffff !important;
    border: none !important;
    box-shadow: none;
    font-size: 1.03em;
  }

  /* Barra blanca a la izquierda */
  .sidebar-nav-active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 60%;
    background: #ffffff;
    border-radius: 0 3px 3px 0;
  }

  /* Icono PNG activo */
  .sidebar-nav-active .nav-icon {
    filter: brightness(0) invert(1);
    transform: scale(1.08);
  }

  .sidebar-nav-idle {
    position: relative;
  }

  /* ─── Textos del header ──────────────────────────────────────────── */
  .user-info  { color: #ffffff; }
  .user-email { color: rgba(255,255,255,0.65); }

  .user-avatar {
    box-shadow: 0 0 0 2px rgba(255,255,255,0.3);
    transition: transform .2s ease, box-shadow .2s ease;
  }
  .user-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 0 0 3px rgba(255,255,255,0.5);
  }

  /* ─── Botón de notificaciones ────────────────────────────────────── */
  .sidebar-button {
    background: rgba(255,255,255,0.12);
    border-color: rgba(255,255,255,0.18);
    color: rgba(255,255,255,0.8);
    transition: background .18s ease, color .18s ease, transform .18s ease;
  }
  .sidebar-button:hover {
    background: rgba(255,255,255,0.22);
    color: #ffffff;
    transform: translateY(-1px);
  }

  /* ─── Scrollbar: invisible ───────────────────────────────────────── */
  .custom-scrollbar {
    scrollbar-width: none;
  }
  .custom-scrollbar::-webkit-scrollbar { display: none; }

  /* ─── Etiquetas: se desvanecen al colapsar (header + nav) ──────── */
  .nav-label {
    opacity: 1;
    transition: opacity 0.15s ease 0.1s;
    white-space: nowrap;
    min-width: 0;
  }
  aside[data-collapsed="true"] .nav-label {
    opacity: 0;
    transition: opacity 0.07s ease;
  }

  /* ─── Mobile ─────────────────────────────────────────────────────── */
  @media (max-width: 768px) {
    .sidebar-container {
      border-radius: 0 1.25rem 1.25rem 0;
    }
  }
</style>

<aside
  x-data="{
      collapsed: true,
      animating: false,
      collapseTimeout: null,
      init() {
        // Siempre iniciamos contraído
        this.collapsed = true;

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
      expand() {
        if (this.animating || window.innerWidth <= 768) return;
        // Cancelar cualquier timeout de contracción pendiente
        if (this.collapseTimeout) {
          clearTimeout(this.collapseTimeout);
          this.collapseTimeout = null;
        }
        this.collapsed = false;
        this.sync();
      },
      contract() {
        if (this.animating || window.innerWidth <= 768) return;
        // Agregar un pequeño delay antes de contraer para dar tiempo al usuario
        this.collapseTimeout = setTimeout(() => {
          this.collapsed = true;
          this.sync();
          this.collapseTimeout = null;
        }, 300);
      },
      sync(){
        document.documentElement.classList.toggle('sb-collapsed', this.collapsed === true);
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
  @mouseleave="contract()"
  x-effect="sync()"
  x-bind:data-collapsed="collapsed ? 'true' : 'false'"
  :class="collapsed ? 'w-16' : 'w-64 sm:w-64 lg:w-64'"
  class="sidebar-container fixed inset-y-0 left-0 z-50 overflow-hidden hidden lg:block
         transition-[width] duration-[280ms] ease-[cubic-bezier(0.16,1,0.3,1)]"
  style="height: 100vh; height: calc(var(--vh, 1vh) * 100);">

  <div class="h-full flex flex-col">
    <!-- Área expandible: Header + Nav -->
    <div @mouseenter="expand()" class="flex-1 min-h-0 flex flex-col">
    <!-- Header -->
    <div class="sidebar-header flex-shrink-0 h-16 flex items-center px-3">
      <a href="{{ route('inicio') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-2.5 w-full transition-opacity duration-200 hover:opacity-90"
         title="Gestior" aria-label="Gestior">
        <span class="shrink-0 w-10 flex items-center justify-center">
          <x-application-mark class="h-7 w-auto filter drop-shadow-sm" />
        </span>
@php
    // Determinar etiqueta legible para el nivel/rol
    $levelLabel = null;

    if (Auth::check()) {
        $roles = Auth::user()->getRoleNames()->toArray();
        $firstRole = $roles[0] ?? null;

        if ($firstRole) {
            $roleMap = [
                'company' => __('nav.role_company'),
                'admin'   => __('nav.role_admin'),
                'user'    => __('nav.role_user'),
                'master'  => __('nav.role_master'),
            ];
            $levelLabel = $roleMap[$firstRole] ?? Str::title(str_replace(['-', '_'], ' ', $firstRole));
        } else {
            switch (Auth::user()->hierarchy_level) {
                case \App\Models\User::HIERARCHY_MASTER:
                    $levelLabel = __('nav.role_master');
                    break;
                case \App\Models\User::HIERARCHY_COMPANY:
                    $levelLabel = __('nav.role_company');
                    break;
                case \App\Models\User::HIERARCHY_ADMIN:
                    $levelLabel = __('nav.role_admin');
                    break;
                case \App\Models\User::HIERARCHY_USER:
                    $levelLabel = __('nav.role_user');
                    break;
                default:
                    $levelLabel = null;
            }
        }
    }
@endphp

        <span class="nav-label user-info flex flex-col leading-tight">
          <span class="font-bold text-base text-white truncate">Gestior</span>
          @if($levelLabel)
            <span class="text-[11px] font-semibold text-white/70 truncate uppercase tracking-wide">{{ $levelLabel }}</span>
          @endif
        </span>

      </a>
    </div>

    <!-- NAV -->
    <nav class="sidebar-nav flex-1 min-h-0 overflow-y-auto pt-4 pb-2 space-y-1 custom-scrollbar px-3"
         :class="animating ? 'pointer-events-none select-none' : ''">

      <!-- Dashboard -->
      <a href="{{ route('dashboard') }}" wire:navigate data-turbo="false" data-module="dashboard"
         class="nav-link {{ request()->routeIs('dashboard') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.dashboard') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/dashboard.png') }}" alt="{{ __('nav.dashboard') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.dashboard') }}</span>
      </a>

      <!-- Nexum -->
      <a href="{{ route('nexum') }}" wire:navigate data-turbo="false" data-module="nexum"
         class="nav-link {{ request()->routeIs('nexum') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.nexum') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <span style="font-size:1.05rem; font-weight:900; letter-spacing:.04em; background:linear-gradient(135deg,#ffffff 0%,#d8ccff 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; display:inline-block; line-height:1;">N</span>
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.nexum') }}</span>
      </a>

      <!-- Crear venta -->
      <a href="{{ route('orders.create') }}" wire:navigate data-turbo="false" data-module="orders"
         class="nav-link {{ request()->routeIs('orders.create') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.create_sale') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/crear-venta.png') }}" alt="{{ __('nav.create_sale') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.create_sale') }}</span>
      </a>

      <!-- Lista de ventas -->
      <a href="{{ $ordersUrl }}" wire:navigate data-turbo="false" data-module="orders"
         class="nav-link {{ request()->routeIs('orders.index') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.sales_list') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/ventas.png') }}" alt="{{ __('nav.sales_list') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.sales_list') }}</span>
      </a>

      <!-- Productos -->
      @if(auth()->user()->hasModule('productos'))
      <a href="{{ route('products.index') }}" wire:navigate data-turbo="false" data-module="products"
         class="nav-link {{ request()->routeIs('products.*') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.products') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/productos.png') }}" alt="{{ __('nav.products') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.products') }}</span>
      </a>
      @endif

      <!-- Stock -->
      <a href="{{ route('stock.index') }}#stock" wire:navigate data-turbo="false" data-module="stock"
         class="nav-link {{ request()->fullUrlIs(route('stock.index').'#stock') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.stock') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/stock.png') }}" alt="{{ __('nav.stock') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.stock') }}</span>
      </a>

      <!-- Servicios -->
      @if(auth()->user()->hasModule('servicios'))
      <a href="{{ route('services.index') }}" wire:navigate data-turbo="false" data-module="services"
         class="nav-link {{ request()->routeIs('services.*') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.services') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/servicios.png') }}" alt="{{ __('nav.services') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.services') }}</span>
      </a>
      @endif

      <!-- Clientes -->
      @if(auth()->user()->hasModule('clientes'))
      <a href="{{ route('clients.index') }}" wire:navigate data-turbo="false" data-module="clients"
         class="nav-link {{ request()->routeIs('clients.*') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.clients') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/clientes.png') }}" alt="{{ __('nav.clients') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.clients') }}</span>
      </a>
      @endif

      <!-- Métodos de Pago -->
      <a href="{{ route('payment-methods.index') }}" wire:navigate data-turbo="false" data-module="payment"
         class="nav-link {{ request()->routeIs('payment-methods.*') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.payment_methods') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/payment.png') }}" alt="{{ __('nav.payment_methods') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.payment_methods') }}</span>
      </a>

      <!-- Descuentos -->
      <a href="{{ route('discounts.index') }}" wire:navigate data-turbo="false" data-module="discounts"
         class="nav-link {{ request()->routeIs('discounts.*') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.discounts') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <svg class="nav-icon w-5 h-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
          </svg>
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.discounts') }}</span>
      </a>

      @if(auth()->user()->isMaster() || auth()->user()->hasModule('alquileres'))
      <!-- Calendario de alquileres -->
      <a href="{{ Route::has('rentals.calendar') ? route('rentals.calendar') : '#' }}" wire:navigate data-turbo="false" data-module="alquileres"
         class="nav-link {{ request()->routeIs('rentals.calendar') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.rentals') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <svg class="nav-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.rentals') }}</span>
      </a>

      <!-- Reservas -->
      <a href="{{ Route::has('rentals.bookings.index') ? route('rentals.bookings.index') : '#' }}" wire:navigate data-turbo="false" data-module="alquileres"
         class="nav-link {{ request()->routeIs('rentals.bookings.*') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.bookings') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <svg class="nav-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.bookings') }}</span>
      </a>

      <!-- Espacios -->
      <a href="{{ Route::has('rentals.spaces.index') ? route('rentals.spaces.index') : '#' }}" wire:navigate data-turbo="false" data-module="alquileres"
         class="nav-link {{ request()->routeIs('rentals.spaces.*') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.spaces') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <svg class="nav-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.spaces') }}</span>
      </a>
      @endif

      @if(Route::has('invoices.configuration'))
      <!-- Facturación (BETA) -->
      <a href="{{ route('invoices.configuration') }}" wire:navigate data-turbo="false" data-module="dashboard"
         class="nav-link {{ request()->routeIs('invoices.*') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.invoicing') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/arca.png') }}" alt="{{ __('nav.invoicing') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3 flex items-center gap-1">
          <span class="truncate">{{ __('nav.invoicing') }}</span>
          <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 uppercase tracking-wide">BETA</span>
        </span>
      </a>
      @endif

      <!-- Gastos -->
      <a href="{{ route('expenses.index') }}" wire:navigate data-turbo="false" data-module="expenses"
         class="nav-link {{ request()->routeIs('costs.*') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.expenses') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/calcular-costos.png') }}" alt="{{ __('nav.expenses') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.expenses') }}</span>
      </a>

@auth
    @if((auth()->user()->isMaster() || auth()->user()->isCompany()) && auth()->user()->hasModule('sucursales'))
        <a href="{{ route('company.branches.index') }}" wire:navigate data-turbo="false" data-module="company"
           class="nav-link {{ request()->routeIs('company.branches.*') ? $active : $idle }}"
                      :title="collapsed ? '{{ __('nav.branches') }}' : null">
          <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
            <img src="{{ asset('images/sucursales.png') }}" alt="{{ __('nav.branches') }}" class="nav-icon">
          </span>
          <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.branches') }}</span>
        </a>
    @endif
@endauth

@auth
    @if((auth()->user()->isMaster() || auth()->user()->isCompany()) && auth()->user()->hasModule('empleados'))
        <a href="{{ route('company.employees.index') }}" wire:navigate data-turbo="false" data-module="employees"
           class="nav-link {{ request()->routeIs('company.branches.*') ? $active : $idle }}"
                      :title="collapsed ? '{{ __('nav.employees') }}' : null">
          <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
            <img src="{{ asset('images/empleados.png') }}" alt="{{ __('nav.employees') }}" class="nav-icon">
          </span>
          <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.employees') }}</span>
        </a>
    @endif
@endauth



@auth
    @if(auth()->user()->isMaster())
        <!-- Master - Agregar Usuarios -->
        <a href="{{ route('master.invitations.index') }}" wire:navigate data-turbo="false" data-module="company"
           class="nav-link {{ request()->routeIs('master.invitations.*') ? $active : $idle }}"
                      :title="collapsed ? '{{ __('nav.generate_users') }}' : null">
          <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
            <img src="{{ asset('images/agregar-user.png') }}" alt="{{ __('nav.generate_users') }}" class="nav-icon">
          </span>
          <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.generate_users') }}</span>
        </a>
    @endif
@endauth

@auth
    @if(auth()->user()->isMaster())
    <!-- Master - Gestionar usuarios -->
    <a href="{{ route('master.users.index') }}" wire:navigate data-turbo="false" data-module="company"
       class="nav-link {{ request()->routeIs('master.users.*') ? $active : $idle }}"
              :title="collapsed ? '{{ __('nav.manage_users') }}' : null">
      <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
        <img src="{{ asset('images/gestionar-user.png') }}" alt="{{ __('nav.manage_users') }}" class="nav-icon">
      </span>
      <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.manage_users') }}</span>
    </a>
@endif
@endauth

      <!-- Solicitudes de Prueba (solo Master y si la ruta existe) -->
      @auth
        @if(auth()->user()->isMaster() && Route::has('trial-requests'))
          <a href="{{ route('trial-requests') }}" wire:navigate data-turbo="false"
             class="nav-link {{ request()->routeIs('trial-requests') ? $active : $idle }}"
                          :title="collapsed ? '{{ __('nav.requests') }}' : null">
            <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
              <svg class="w-6 h-6 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </span>
            <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.requests') }}</span>
          </a>
        @endif
      @endauth

      <!-- Configuración -->
      <a href="{{ route('settings') }}" wire:navigate data-turbo="false" data-module="company"
         class="nav-link {{ request()->routeIs('settings') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.settings') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/configuraciones.png') }}" alt="{{ __('nav.settings') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.settings') }}</span>
      </a>

      <!-- Soporte -->
      <a href="{{ route('support.index') }}" wire:navigate data-turbo="false" data-module="company"
         class="nav-link {{ request()->routeIs('support.*') ? $active : $idle }}"
                  :title="collapsed ? '{{ __('nav.support') }}' : null">
        <span class="shrink-0 w-10 flex items-center justify-center py-2.5">
          <img src="{{ asset('images/soporte.png') }}" alt="{{ __('nav.support') }}" class="nav-icon">
        </span>
        <span class="nav-label text-sm font-semibold pr-3">{{ __('nav.support') }}</span>
      </a>
    </nav>
    </div>
    <!-- Fin área expandible -->

    <!-- Notificaciones (no expande) -->
    <div class="sidebar-toggle flex-shrink-0 pt-2 pb-3 px-3">
      <div class="w-full flex items-center justify-center">
        <x-notifications-bell />
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
