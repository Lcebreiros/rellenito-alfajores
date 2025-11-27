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
    <div class="flex items-center gap-2 sm:gap-3">
      <img src="{{ asset('images/Gestior.png') }}" alt="Gestior" class="h-8 sm:h-9 lg:h-10 w-auto" />
      @if($levelLabel)
        <span class="text-sm sm:text-base lg:text-lg text-neutral-500 dark:text-neutral-400 font-bold">{{ $levelLabel }}</span>
      @endif
    </div>
    <div class="text-center">
      <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-neutral-900 dark:text-neutral-100">Bienvenido/a {{ auth()->user()->name }}</h1>
    </div>
    <div></div>
  </div>
@endsection

@section('content')
<div class="w-full h-[calc(100vh-8rem)] overflow-hidden px-2 sm:px-4 lg:px-6 py-2 sm:py-3">
  <style>
    /* Colores de módulos */
    :root {
      /* Orders - Blue */
      --module-orders-50: 239, 246, 255;
      --module-orders-100: 219, 234, 254;
      --module-orders-400: 96, 165, 250;
      --module-orders-500: 59, 130, 246;
      --module-orders-800: 30, 64, 175;
      --module-orders-900: 30, 58, 138;

      /* Products - Green */
      --module-products-50: 240, 253, 244;
      --module-products-100: 220, 252, 231;
      --module-products-400: 74, 222, 128;
      --module-products-500: 34, 197, 94;
      --module-products-800: 22, 101, 52;
      --module-products-900: 20, 83, 45;

      /* Stock - Amber */
      --module-stock-50: 255, 251, 235;
      --module-stock-100: 254, 243, 199;
      --module-stock-400: 251, 191, 36;
      --module-stock-500: 245, 158, 11;
      --module-stock-800: 146, 64, 14;
      --module-stock-900: 120, 53, 15;

      /* Clients - Purple */
      --module-clients-50: 250, 245, 255;
      --module-clients-100: 243, 232, 255;
      --module-clients-400: 192, 132, 252;
      --module-clients-500: 168, 85, 247;
      --module-clients-800: 107, 33, 168;
      --module-clients-900: 88, 28, 135;

      /* Expenses - Red */
      --module-expenses-50: 254, 242, 242;
      --module-expenses-100: 254, 226, 226;
      --module-expenses-400: 248, 113, 113;
      --module-expenses-500: 239, 68, 68;
      --module-expenses-800: 153, 27, 27;
      --module-expenses-900: 127, 29, 29;

      /* Services - Cyan */
      --module-services-50: 236, 254, 255;
      --module-services-100: 207, 250, 254;
      --module-services-400: 34, 211, 238;
      --module-services-500: 6, 182, 212;
      --module-services-800: 21, 94, 117;
      --module-services-900: 22, 78, 99;

      /* Payment - Emerald */
      --module-payment-50: 236, 253, 245;
      --module-payment-100: 209, 250, 229;
      --module-payment-400: 52, 211, 153;
      --module-payment-500: 16, 185, 129;
      --module-payment-800: 6, 95, 70;
      --module-payment-900: 6, 78, 59;

      /* Company - Indigo */
      --module-company-50: 238, 242, 255;
      --module-company-100: 224, 231, 255;
      --module-company-400: 129, 140, 248;
      --module-company-500: 99, 102, 241;
      --module-company-800: 55, 48, 163;
      --module-company-900: 49, 46, 129;

      /* Employees - Pink */
      --module-employees-50: 253, 242, 248;
      --module-employees-100: 252, 231, 243;
      --module-employees-400: 244, 114, 182;
      --module-employees-500: 236, 72, 153;
      --module-employees-800: 157, 23, 77;
      --module-employees-900: 131, 24, 67;

      /* Dashboard - Slate */
      --module-dashboard-50: 248, 250, 252;
      --module-dashboard-100: 241, 245, 249;
      --module-dashboard-400: 148, 163, 184;
      --module-dashboard-500: 100, 116, 139;
      --module-dashboard-800: 30, 41, 59;
      --module-dashboard-900: 15, 23, 42;
    }

    /* Animaciones sutiles */
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; }

    /* Tiles base - Contenedores simples */
    .tile-square,
    .tile-wide {
      position: relative;
      border-radius: 0.5rem;
      overflow: hidden;
      transition: all 0.25s ease;
      cursor: pointer;

      /* Modo claro */
      background: #ffffff;
      border: 1px solid rgba(0, 0, 0, 0.1);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    @media (min-width: 640px) {
      .tile-square,
      .tile-wide {
        border-radius: 0.625rem;
      }
    }

    @media (min-width: 1024px) {
      .tile-square,
      .tile-wide {
        border-radius: 0.75rem;
      }
    }

    /* Modo oscuro */
    .dark .tile-square,
    .dark .tile-wide {
      background: rgba(30, 30, 30, 0.95);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
    }

    .tile-square {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 0.875rem;
    }

    @media (min-width: 640px) {
      .tile-square {
        padding: 1rem;
      }
    }

    @media (min-width: 1024px) {
      .tile-square {
        padding: 1.5rem;
      }
    }

    .tile-wide {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.625rem 0.75rem;
      min-width: 0;
      width: 100%;
    }

    @media (min-width: 640px) {
      .tile-wide {
        gap: 0.625rem;
        padding: 0.625rem 0.75rem;
      }
    }

    @media (min-width: 1024px) {
      .tile-wide {
        gap: 0.75rem;
        padding: 0.75rem 1rem;
      }
    }

    /* Hover colors - Degradados muy suaves (del color base al color del módulo) */

    /* Orders - Blue */
    .tile-orders.tile-square:hover:not(.theme-custom *),
    .tile-orders.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(96, 165, 250, 0.85)) !important;
      border-color: rgba(96, 165, 250, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(96, 165, 250, 0.25);
    }
    .dark .tile-orders.tile-square:hover:not(.theme-custom *),
    .dark .tile-orders.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(30, 30, 30, 0.9), rgba(96, 165, 250, 0.4)) !important;
      border-color: rgba(96, 165, 250, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(96, 165, 250, 0.35);
    }

    /* Products - Green */
    .tile-products.tile-square:hover:not(.theme-custom *),
    .tile-products.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(74, 222, 128, 0.85)) !important;
      border-color: rgba(74, 222, 128, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(74, 222, 128, 0.25);
    }
    .dark .tile-products.tile-square:hover:not(.theme-custom *),
    .dark .tile-products.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(30, 30, 30, 0.9), rgba(74, 222, 128, 0.4)) !important;
      border-color: rgba(74, 222, 128, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(74, 222, 128, 0.35);
    }

    /* Stock - Amber */
    .tile-stock.tile-square:hover:not(.theme-custom *),
    .tile-stock.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(251, 191, 36, 0.85)) !important;
      border-color: rgba(251, 191, 36, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(251, 191, 36, 0.25);
    }
    .dark .tile-stock.tile-square:hover:not(.theme-custom *),
    .dark .tile-stock.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(30, 30, 30, 0.9), rgba(251, 191, 36, 0.4)) !important;
      border-color: rgba(251, 191, 36, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(251, 191, 36, 0.35);
    }

    /* Clients - Purple */
    .tile-clients.tile-square:hover:not(.theme-custom *),
    .tile-clients.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(192, 132, 252, 0.85)) !important;
      border-color: rgba(192, 132, 252, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(192, 132, 252, 0.25);
    }
    .dark .tile-clients.tile-square:hover:not(.theme-custom *),
    .dark .tile-clients.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(30, 30, 30, 0.9), rgba(192, 132, 252, 0.4)) !important;
      border-color: rgba(192, 132, 252, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(192, 132, 252, 0.35);
    }

    /* Expenses - Red */
    .tile-expenses.tile-square:hover:not(.theme-custom *),
    .tile-expenses.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(248, 113, 113, 0.85)) !important;
      border-color: rgba(248, 113, 113, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(248, 113, 113, 0.25);
    }
    .dark .tile-expenses.tile-square:hover:not(.theme-custom *),
    .dark .tile-expenses.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(30, 30, 30, 0.9), rgba(248, 113, 113, 0.4)) !important;
      border-color: rgba(248, 113, 113, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(248, 113, 113, 0.35);
    }

    /* Services - Cyan */
    .tile-services.tile-square:hover:not(.theme-custom *),
    .tile-services.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(34, 211, 238, 0.85)) !important;
      border-color: rgba(34, 211, 238, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(34, 211, 238, 0.25);
    }
    .dark .tile-services.tile-square:hover:not(.theme-custom *),
    .dark .tile-services.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(30, 30, 30, 0.9), rgba(34, 211, 238, 0.4)) !important;
      border-color: rgba(34, 211, 238, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(34, 211, 238, 0.35);
    }

    /* Payment - Emerald */
    .tile-payment.tile-square:hover:not(.theme-custom *),
    .tile-payment.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(52, 211, 153, 0.85)) !important;
      border-color: rgba(52, 211, 153, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(52, 211, 153, 0.25);
    }
    .dark .tile-payment.tile-square:hover:not(.theme-custom *),
    .dark .tile-payment.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(30, 30, 30, 0.9), rgba(52, 211, 153, 0.4)) !important;
      border-color: rgba(52, 211, 153, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(52, 211, 153, 0.35);
    }

    /* Company - Indigo */
    .tile-company.tile-square:hover:not(.theme-custom *),
    .tile-company.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(129, 140, 248, 0.85)) !important;
      border-color: rgba(129, 140, 248, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(129, 140, 248, 0.25);
    }
    .dark .tile-company.tile-square:hover:not(.theme-custom *),
    .dark .tile-company.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(30, 30, 30, 0.9), rgba(129, 140, 248, 0.4)) !important;
      border-color: rgba(129, 140, 248, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(129, 140, 248, 0.35);
    }

    /* Employees - Pink */
    .tile-employees.tile-square:hover:not(.theme-custom *),
    .tile-employees.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(244, 114, 182, 0.85)) !important;
      border-color: rgba(244, 114, 182, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(244, 114, 182, 0.25);
    }
    .dark .tile-employees.tile-square:hover:not(.theme-custom *),
    .dark .tile-employees.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(30, 30, 30, 0.9), rgba(244, 114, 182, 0.4)) !important;
      border-color: rgba(244, 114, 182, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(244, 114, 182, 0.35);
    }

    /* Dashboard - Slate */
    .tile-dashboard.tile-square:hover:not(.theme-custom *),
    .tile-dashboard.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(148, 163, 184, 0.85)) !important;
      border-color: rgba(148, 163, 184, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(148, 163, 184, 0.25);
    }
    .dark .tile-dashboard.tile-square:hover:not(.theme-custom *),
    .dark .tile-dashboard.tile-wide:hover:not(.theme-custom *) {
      background: linear-gradient(135deg, rgba(30, 30, 30, 0.9), rgba(148, 163, 184, 0.4)) !important;
      border-color: rgba(148, 163, 184, 0.6) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(148, 163, 184, 0.35);
    }

    /* Hover para tema custom */
    .theme-custom .tile-square:hover,
    .theme-custom .tile-wide:hover {
      background: linear-gradient(135deg,
        rgb(var(--custom-color-rgb)),
        rgba(var(--custom-color-rgb), 0.85)) !important;
      border-color: rgb(var(--custom-color-rgb)) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(var(--custom-color-rgb), 0.3);
    }
    .dark.theme-custom .tile-square:hover,
    .dark.theme-custom .tile-wide:hover {
      background: linear-gradient(135deg,
        rgb(var(--custom-color-rgb)),
        rgba(var(--custom-color-rgb), 0.85)) !important;
      border-color: rgb(var(--custom-color-rgb)) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(var(--custom-color-rgb), 0.4);
    }

    /* Texto en hover - mantiene el color original para mejor contraste con degradados suaves */
    .tile-square:hover .text-neutral-900,
    .tile-wide:hover .text-neutral-900 {
      color: rgb(17, 24, 39) !important;
    }
    .dark .tile-square:hover .dark\:text-neutral-100,
    .dark .tile-wide:hover .dark\:text-neutral-100 {
      color: rgb(255, 255, 255) !important;
    }
    .tile-square:hover .text-neutral-500,
    .tile-wide:hover .text-neutral-500 {
      color: rgb(71, 85, 105) !important;
    }
    .dark .tile-square:hover .dark\:text-neutral-400,
    .dark .tile-wide:hover .dark\:text-neutral-400 {
      color: rgb(209, 213, 219) !important;
    }
    /* Imagen mantiene el color original en modo claro, se invierte en modo oscuro */
    .dark .tile-square:hover img,
    .dark .tile-wide:hover img {
      filter: brightness(0) invert(1) !important;
    }
  </style>

  @if(session('success'))
    <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('success') }}
    </div>
  @endif

  {{-- CONTENEDOR PRINCIPAL - Layout vertical con secciones --}}
  <div class="h-full flex flex-col justify-evenly sm:justify-center w-full">
    <div class="max-w-7xl mx-auto w-full flex flex-col gap-4 sm:gap-5 lg:gap-8">

    {{-- OPERACIONES --}}
    <div class="fade-in-up">
      <h2 class="text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5 sm:mb-2 lg:mb-2.5 tracking-tight uppercase">Operaciones</h2>
      <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 gap-1.5 sm:gap-2 lg:gap-2.5">
        {{-- Crear Pedido - Ocupa 2 filas --}}
        <a href="{{ route('orders.create') }}" class="tile-square tile-orders row-span-2">
          <img src="{{ asset('images/crear-pedido.png') }}" alt="Crear pedido" class="w-8 h-8 sm:w-10 sm:h-10 lg:w-14 lg:h-14 object-contain dark:invert mb-1 sm:mb-1.5 lg:mb-2 transition-transform group-hover:scale-110">
          <div class="text-center">
            <div class="text-xs sm:text-sm lg:text-base font-bold text-neutral-900 dark:text-neutral-100">Crear pedido</div>
            <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Carga rápida</div>
          </div>
        </a>

        {{-- Pedidos --}}
        <a href="{{ route('orders.index') }}" class="tile-wide tile-orders">
          <img src="{{ asset('images/pedidos.png') }}" alt="Pedidos" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
          <div class="min-w-0">
            <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Pedidos</div>
            <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Historial</div>
          </div>
        </a>

        {{-- Stock --}}
        <a href="{{ route('stock.index') }}#stock" class="tile-wide tile-stock">
          <img src="{{ asset('images/stock.png') }}" alt="Stock" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
          <div class="min-w-0">
            <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Stock</div>
            <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Inventario</div>
          </div>
        </a>

        {{-- Productos (fila 2, columnas 2-3) --}}
        <a href="{{ route('products.index') }}" class="tile-wide tile-products col-span-2">
          <img src="{{ asset('images/productos.png') }}" alt="Productos" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
          <div class="min-w-0">
            <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Productos</div>
            <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Catálogo y precios</div>
          </div>
        </a>
      </div>
    </div>

    {{-- GESTIÓN --}}
    <div class="fade-in-up" style="animation-delay: 0.05s;">
      <h2 class="text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5 sm:mb-2 lg:mb-2.5 tracking-tight uppercase">Gestión</h2>
      <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-4 gap-1.5 sm:gap-2 lg:gap-2.5">
        <a href="{{ route('clients.index') }}" class="tile-wide tile-clients">
          <img src="{{ asset('images/clientes.png') }}" alt="Clientes" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
          <div class="min-w-0">
            <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Clientes</div>
            <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">CRM</div>
          </div>
        </a>

        <a href="{{ route('expenses.index') }}" class="tile-wide tile-expenses">
          <img src="{{ asset('images/calcular-costos.png') }}" alt="Gastos" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
          <div class="min-w-0">
            <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Gastos</div>
            <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Egresos</div>
          </div>
        </a>

        <a href="{{ route('services.index') }}" class="tile-wide tile-services">
          <img src="{{ asset('images/servicios.png') }}" alt="Servicios" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
          <div class="min-w-0">
            <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Servicios</div>
            <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Catálogo</div>
          </div>
        </a>

        <a href="{{ route('payment-methods.index') }}" class="tile-wide tile-payment">
          <img src="{{ asset('images/payment.png') }}" alt="Métodos de Pago" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
          <div class="min-w-0">
            <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Métodos Pago</div>
            <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Formas</div>
          </div>
        </a>
      </div>
    </div>

    {{-- ADMINISTRACIÓN Y SISTEMA en una fila --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-3 sm:gap-3.5 lg:gap-4">
      {{-- ADMINISTRACIÓN (si aplica) --}}
      @auth
        @if(auth()->user()->isMaster() || auth()->user()->isCompany())
          <div class="fade-in-up" style="animation-delay: 0.1s;">
            <h2 class="text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5 sm:mb-2 lg:mb-2.5 tracking-tight uppercase">Administración</h2>
            <div class="grid grid-cols-2 sm:grid-cols-2 gap-1.5 sm:gap-2 lg:gap-2.5">
              <a href="{{ route('company.branches.index') }}" class="tile-wide tile-company">
                <img src="{{ asset('images/sucursales.png') }}" alt="Sucursales" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
                <div class="min-w-0">
                  <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Sucursales</div>
                  <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Sedes</div>
                </div>
              </a>

              <a href="{{ route('company.employees.index') }}" class="tile-wide tile-employees">
                <img src="{{ asset('images/empleados.png') }}" alt="Personal" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
                <div class="min-w-0">
                  <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Personal</div>
                  <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Empleados</div>
                </div>
              </a>
            </div>
          </div>
        @endif
      @endauth

      {{-- SISTEMA --}}
      <div class="fade-in-up" style="animation-delay: 0.15s;">
        <h2 class="text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5 sm:mb-2 lg:mb-2.5 tracking-tight uppercase">Sistema</h2>
        <div class="grid grid-cols-2 sm:grid-cols-2 gap-1.5 sm:gap-2 lg:gap-2.5">
          <a href="{{ route('settings') }}" class="tile-wide tile-dashboard">
            <img src="{{ asset('images/configuraciones.png') }}" alt="Configuración" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
            <div class="min-w-0">
              <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Configuración</div>
              <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Ajustes</div>
            </div>
          </a>

          <a href="{{ route('support.index') }}" class="tile-wide tile-dashboard">
            <img src="{{ asset('images/soporte.png') }}" alt="Soporte" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
            <div class="min-w-0">
              <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Soporte</div>
              <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Ayuda</div>
            </div>
          </a>
        </div>
      </div>
    </div>

    {{-- Master (solo si es necesario, oculto por defecto como en la imagen) --}}
    @auth
      @if(auth()->user()->isMaster())
        <div class="hidden">
          <h2 class="text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5 sm:mb-2 lg:mb-2.5 tracking-tight uppercase">Master</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 sm:gap-2 lg:gap-2.5">
            <a href="{{ route('master.invitations.index') }}" class="tile-wide tile-dashboard">
              <img src="{{ asset('images/agregar-user.png') }}" alt="Invitaciones" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
              <div class="min-w-0">
                <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Generar usuarios</div>
                <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Invitaciones</div>
              </div>
            </a>

            <a href="{{ route('master.users.index') }}" class="tile-wide tile-dashboard">
              <img src="{{ asset('images/gestionar-user.png') }}" alt="Usuarios" class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 object-contain dark:invert flex-shrink-0">
              <div class="min-w-0">
                <div class="text-xs sm:text-sm font-bold text-neutral-900 dark:text-neutral-100 truncate">Gestionar usuarios</div>
                <div class="text-[10px] sm:text-xs text-neutral-500 dark:text-neutral-400">Administración</div>
              </div>
            </a>
          </div>
        </div>
      @endif
    @endauth

    </div>
  </div>
</div>
@endsection
