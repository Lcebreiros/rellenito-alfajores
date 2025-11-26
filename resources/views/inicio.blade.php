@extends('layouts.app')

@section('no_sidebar', '1')

@section('header')
  @php
    $levelLabel = null;
    if (Auth::check()) {
        $roles = Auth::user()->getRoleNames()->toArray();
        $firstRole = $roles[0] ?? null;
        if ($firstRole) {
            $roleMap = [
                'company' => 'Empresa',
                'admin'   => 'Sucursal',
                'user'    => 'Usuario',
                'master'  => 'Master',
            ];
            $levelLabel = $roleMap[$firstRole] ?? Str::title(str_replace(['-', '_'], ' ', $firstRole));
        } else {
            switch (Auth::user()->hierarchy_level) {
                case \App\Models\User::HIERARCHY_MASTER:  $levelLabel = 'Master'; break;
                case \App\Models\User::HIERARCHY_COMPANY: $levelLabel = 'Empresa'; break;
                case \App\Models\User::HIERARCHY_ADMIN:   $levelLabel = 'Sucursal'; break;
                case \App\Models\User::HIERARCHY_USER:    $levelLabel = 'Usuario'; break;
                default: $levelLabel = null; break;
            }
        }
    }
  @endphp
  <div class="grid grid-cols-3 items-center">
    <div class="flex items-center gap-3">
      <img src="{{ asset('images/Gestior.png') }}" alt="Gestior" class="h-10 w-auto" />
      @if($levelLabel)
        <span class="text-lg text-neutral-500 dark:text-neutral-400 font-bold">{{ $levelLabel }}</span>
      @endif
    </div>
    <div class="text-center">
      <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Bienvenido/a {{ auth()->user()->name }}</h1>
    </div>
    <div></div>
  </div>
@endsection

@section('content')
<div class="w-full max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
  <style>
    /* Animaciones sutiles */
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; }

    /* Botón grande - Crear Pedido (Windows 8 style) */
    .tile-large {
      @apply group relative rounded-lg overflow-hidden
             bg-white dark:bg-neutral-900
             border border-neutral-200 dark:border-neutral-800
             transition-all duration-200
             shadow-sm hover:shadow-md hover:-translate-y-0.5;
    }

    /* Botones rectangulares horizontales (logo left, text right) */
    .tile-wide {
      @apply group relative rounded-lg overflow-hidden
             bg-white dark:bg-neutral-900
             border border-neutral-200 dark:border-neutral-800
             flex items-center gap-3 px-3 py-2
             transition-all duration-200
             shadow-sm hover:shadow-md hover:-translate-y-0.5;
    }

    /* Hover colors para cada módulo en temas default/dark/neon */
    .tile-orders:hover:not(.theme-custom *) {
      border-color: rgb(var(--module-orders-500) / 0.5);
      background: rgb(var(--module-orders-50) / 0.4);
    }
    .dark .tile-orders:hover:not(.theme-custom *) {
      background: rgb(var(--module-orders-500) / 0.12);
    }

    .tile-products:hover:not(.theme-custom *) {
      border-color: rgb(var(--module-products-500) / 0.5);
      background: rgb(var(--module-products-50) / 0.4);
    }
    .dark .tile-products:hover:not(.theme-custom *) {
      background: rgb(var(--module-products-500) / 0.12);
    }

    .tile-stock:hover:not(.theme-custom *) {
      border-color: rgb(var(--module-stock-500) / 0.5);
      background: rgb(var(--module-stock-50) / 0.4);
    }
    .dark .tile-stock:hover:not(.theme-custom *) {
      background: rgb(var(--module-stock-500) / 0.12);
    }

    .tile-clients:hover:not(.theme-custom *) {
      border-color: rgb(var(--module-clients-500) / 0.5);
      background: rgb(var(--module-clients-50) / 0.4);
    }
    .dark .tile-clients:hover:not(.theme-custom *) {
      background: rgb(var(--module-clients-500) / 0.12);
    }

    .tile-expenses:hover:not(.theme-custom *) {
      border-color: rgb(var(--module-expenses-500) / 0.5);
      background: rgb(var(--module-expenses-50) / 0.4);
    }
    .dark .tile-expenses:hover:not(.theme-custom *) {
      background: rgb(var(--module-expenses-500) / 0.12);
    }

    .tile-services:hover:not(.theme-custom *) {
      border-color: rgb(var(--module-services-500) / 0.5);
      background: rgb(var(--module-services-50) / 0.4);
    }
    .dark .tile-services:hover:not(.theme-custom *) {
      background: rgb(var(--module-services-500) / 0.12);
    }

    .tile-payment:hover:not(.theme-custom *) {
      border-color: rgb(var(--module-payment-500) / 0.5);
      background: rgb(var(--module-payment-50) / 0.4);
    }
    .dark .tile-payment:hover:not(.theme-custom *) {
      background: rgb(var(--module-payment-500) / 0.12);
    }

    .tile-company:hover:not(.theme-custom *) {
      border-color: rgb(var(--module-company-500) / 0.5);
      background: rgb(var(--module-company-50) / 0.4);
    }
    .dark .tile-company:hover:not(.theme-custom *) {
      background: rgb(var(--module-company-500) / 0.12);
    }

    .tile-employees:hover:not(.theme-custom *) {
      border-color: rgb(var(--module-employees-500) / 0.5);
      background: rgb(var(--module-employees-50) / 0.4);
    }
    .dark .tile-employees:hover:not(.theme-custom *) {
      background: rgb(var(--module-employees-500) / 0.12);
    }

    .tile-dashboard:hover:not(.theme-custom *) {
      border-color: rgb(var(--module-dashboard-500) / 0.5);
      background: rgb(var(--module-dashboard-50) / 0.4);
    }
    .dark .tile-dashboard:hover:not(.theme-custom *) {
      background: rgb(var(--module-dashboard-500) / 0.12);
    }

    /* Hover para tema custom */
    .theme-custom .tile-large:hover,
    .theme-custom .tile-wide:hover {
      border-color: rgb(var(--custom-color-rgb) / 0.5);
      background: color-mix(in srgb, rgb(var(--custom-color-rgb)) 8%, rgb(255 255 255));
    }
    .dark.theme-custom .tile-large:hover,
    .dark.theme-custom .tile-wide:hover {
      background: color-mix(in srgb, rgb(var(--custom-color-rgb)) 12%, rgb(0 0 0));
    }
  </style>

  @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('success') }}
    </div>
  @endif

  {{-- Dashboard como primer elemento --}}
  <div class="mb-3 fade-in-up">
    <a href="{{ route('dashboard') }}" class="tile-wide tile-dashboard block">
      <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard" class="w-8 h-8 sm:w-9 sm:h-9 object-contain dark:invert flex-shrink-0">
      <div class="min-w-0">
        <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Dashboard</div>
        <div class="text-xs text-neutral-600 dark:text-neutral-400">Resumen y métricas</div>
      </div>
    </a>
  </div>

  {{-- SECCIÓN: OPERACIONES --}}
  <div class="mb-4 fade-in-up" style="animation-delay: 0.1s;">
    <h2 class="text-base sm:text-lg font-bold text-neutral-800 dark:text-neutral-200 mb-2">Operaciones</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
      {{-- Crear Pedido - Grande (2 filas en desktop) --}}
      <div class="md:row-span-2">
        <a href="{{ route('orders.create') }}" class="tile-large tile-orders flex flex-col items-center justify-center h-full min-h-[120px] sm:min-h-[140px] md:min-h-[160px] p-4">
          <img src="{{ asset('images/crear-pedido.png') }}" alt="Crear pedido" class="w-12 h-12 sm:w-14 sm:h-14 object-contain dark:invert mb-2 transition-transform group-hover:scale-110">
          <div class="text-center">
            <div class="text-base sm:text-lg font-bold text-neutral-900 dark:text-neutral-100">Crear pedido</div>
            <div class="text-xs text-neutral-600 dark:text-neutral-400 mt-0.5">Carga rápida</div>
          </div>
        </a>
      </div>

      {{-- Pedidos --}}
      <div class="md:col-span-2 lg:col-span-2">
        <a href="{{ route('orders.index') }}" class="tile-wide tile-orders">
          <img src="{{ asset('images/pedidos.png') }}" alt="Pedidos" class="w-8 h-8 sm:w-9 sm:h-9 object-contain dark:invert flex-shrink-0">
          <div class="min-w-0">
            <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Pedidos</div>
            <div class="text-xs text-neutral-600 dark:text-neutral-400">Historial y gestión</div>
          </div>
        </a>
      </div>

      {{-- Stock --}}
      <div class="md:col-span-2 lg:col-span-2">
        <a href="{{ route('stock.index') }}#stock" class="tile-wide tile-stock">
          <img src="{{ asset('images/stock.png') }}" alt="Stock" class="w-8 h-8 sm:w-9 sm:h-9 object-contain dark:invert flex-shrink-0">
          <div class="min-w-0">
            <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Stock</div>
            <div class="text-xs text-neutral-600 dark:text-neutral-400">Inventario</div>
          </div>
        </a>
      </div>
    </div>

    {{-- Productos (debajo) --}}
    <div class="mt-2 sm:mt-3 md:ml-[calc(33.333%+0.5rem)] lg:ml-[calc(33.333%+0.75rem)]">
      <a href="{{ route('products.index') }}" class="tile-wide tile-products">
        <img src="{{ asset('images/productos.png') }}" alt="Productos" class="w-8 h-8 sm:w-9 sm:h-9 object-contain dark:invert flex-shrink-0">
        <div class="min-w-0">
          <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Productos</div>
          <div class="text-xs text-neutral-600 dark:text-neutral-400">Catálogo y precios</div>
        </div>
      </a>
    </div>
  </div>

  {{-- SECCIÓN: GESTIÓN --}}
  <div class="mb-4 fade-in-up" style="animation-delay: 0.2s;">
    <h2 class="text-base sm:text-lg font-bold text-neutral-800 dark:text-neutral-200 mb-2">Gestión</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">

      {{-- Clientes --}}
      <a href="{{ route('clients.index') }}" class="tile-wide tile-clients">
        <img src="{{ asset('images/clientes.png') }}" alt="Clientes" class="w-9 h-9 sm:w-10 sm:h-10 object-contain dark:invert flex-shrink-0">
        <div class="min-w-0">
          <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Clientes</div>
          <div class="text-xs text-neutral-600 dark:text-neutral-400">CRM básico</div>
        </div>
      </a>

      {{-- Gastos --}}
      <a href="{{ route('expenses.index') }}" class="tile-wide tile-expenses">
        <img src="{{ asset('images/calcular-costos.png') }}" alt="Gastos" class="w-9 h-9 sm:w-10 sm:h-10 object-contain dark:invert flex-shrink-0">
        <div class="min-w-0">
          <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Gastos</div>
          <div class="text-xs text-neutral-600 dark:text-neutral-400">Gestión de gastos</div>
        </div>
      </a>

      {{-- Servicios --}}
      <a href="{{ route('services.index') }}" class="tile-wide tile-services">
        <img src="{{ asset('images/servicios.png') }}" alt="Servicios" class="w-9 h-9 sm:w-10 sm:h-10 object-contain dark:invert flex-shrink-0">
        <div class="min-w-0">
          <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Servicios</div>
          <div class="text-xs text-neutral-600 dark:text-neutral-400">Catálogo y precios</div>
        </div>
      </a>

      {{-- Métodos de Pago --}}
      <a href="{{ route('payment-methods.index') }}" class="tile-wide tile-payment">
        <img src="{{ asset('images/payment.png') }}" alt="Métodos de Pago" class="w-9 h-9 sm:w-10 sm:h-10 object-contain dark:invert flex-shrink-0">
        <div class="min-w-0">
          <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Métodos de Pago</div>
          <div class="text-xs text-neutral-600 dark:text-neutral-400">Configurar pagos</div>
        </div>
      </a>
    </div>
  </div>

  {{-- SECCIONES: MI NEGOCIO Y SISTEMA (lado a lado) --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 mb-8">

    {{-- MI NEGOCIO --}}
    @auth
      @if(auth()->user()->isMaster() || auth()->user()->isCompany())
        <div class="fade-in-up" style="animation-delay: 0.3s;">
          <h2 class="text-xl font-bold text-neutral-800 dark:text-neutral-200 mb-4">Mi Negocio</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3 sm:gap-4">

            {{-- Sucursales --}}
            <a href="{{ route('company.branches.index') }}" class="tile-wide tile-company">
              <img src="{{ asset('images/sucursales.png') }}" alt="Sucursales" class="w-9 h-9 sm:w-10 sm:h-10 object-contain dark:invert flex-shrink-0">
              <div class="min-w-0">
                <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Sucursales</div>
                <div class="text-xs text-neutral-600 dark:text-neutral-400">Gestión de sedes</div>
              </div>
            </a>

            {{-- Personal --}}
            <a href="{{ route('company.employees.index') }}" class="tile-wide tile-employees">
              <img src="{{ asset('images/empleados.png') }}" alt="Personal" class="w-9 h-9 sm:w-10 sm:h-10 object-contain dark:invert flex-shrink-0">
              <div class="min-w-0">
                <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Personal</div>
                <div class="text-xs text-neutral-600 dark:text-neutral-400">Empleados</div>
              </div>
            </a>
          </div>
        </div>
      @endif
    @endauth

    {{-- SISTEMA --}}
    <div class="fade-in-up" style="animation-delay: 0.3s;">
      <h2 class="text-xl font-bold text-neutral-800 dark:text-neutral-200 mb-4">Sistema</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3 sm:gap-4">

        {{-- Configuración --}}
        <a href="{{ route('settings') }}" class="tile-wide tile-dashboard">
          <img src="{{ asset('images/configuraciones.png') }}" alt="Configuración" class="w-9 h-9 sm:w-10 sm:h-10 object-contain dark:invert flex-shrink-0">
          <div class="min-w-0">
            <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Configuración</div>
            <div class="text-xs text-neutral-600 dark:text-neutral-400">Preferencias del sistema</div>
          </div>
        </a>

        {{-- Soporte --}}
        <a href="{{ route('support.index') }}" class="tile-wide tile-dashboard">
          <img src="{{ asset('images/soporte.png') }}" alt="Soporte" class="w-9 h-9 sm:w-10 sm:h-10 object-contain dark:invert flex-shrink-0">
          <div class="min-w-0">
            <div class="text-sm sm:text-base font-bold text-neutral-900 dark:text-neutral-100 truncate">Soporte</div>
            <div class="text-xs text-neutral-600 dark:text-neutral-400">Reclamos y mensajes</div>
          </div>
        </a>
      </div>
    </div>
  </div>

  {{-- Master: invitaciones y usuarios --}}
  @auth
    @if(auth()->user()->isMaster())
      <div class="mb-8 fade-in-up" style="animation-delay: 0.4s;">
        <h2 class="text-xl font-bold text-neutral-800 dark:text-neutral-200 mb-4">Master</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">

          <a href="{{ route('master.invitations.index') }}" class="tile-wide tile-dashboard">
            <img src="{{ asset('images/agregar-user.png') }}" alt="Invitaciones" class="w-10 h-10 sm:w-12 sm:h-12 object-contain dark:invert flex-shrink-0">
            <div class="min-w-0">
              <div class="text-base sm:text-lg font-bold text-neutral-900 dark:text-neutral-100 truncate">Generar usuarios</div>
              <div class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400">Invitaciones</div>
            </div>
          </a>

          <a href="{{ route('master.users.index') }}" class="tile-wide tile-dashboard">
            <img src="{{ asset('images/gestionar-user.png') }}" alt="Usuarios" class="w-10 h-10 sm:w-12 sm:h-12 object-contain dark:invert flex-shrink-0">
            <div class="min-w-0">
              <div class="text-base sm:text-lg font-bold text-neutral-900 dark:text-neutral-100 truncate">Gestionar usuarios</div>
              <div class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400">Administración</div>
            </div>
          </a>
        </div>
      </div>
    @endif
  @endauth
</div>
@endsection
