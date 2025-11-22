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
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">
  <style>
    /* Animación de entrada sutil para tiles e íconos */
    @keyframes tileIn { from { opacity: 0; transform: translateY(8px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
    @keyframes iconIn { from { opacity: 0; transform: translateY(6px) scale(.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
    .tile-enter { animation: tileIn .45s cubic-bezier(.16,1,.3,1) both; }
    .icon-enter { animation: iconIn .5s cubic-bezier(.16,1,.3,1) .06s both; }
  </style>
  @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('success') }}
    </div>
  @endif

  @php
    // Contenedor centrado; hover sobre caja blanca con animación estándar (sin clases arbitrarias)
    $tile     = 'group block p-1';
    $box      = 'relative rounded-2xl px-6 py-7 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-center flex flex-col items-center '
              . 'transition-all duration-200 ease-out transform tile-enter shadow-sm '
              . 'hover:shadow-lg hover:ring-2 hover:ring-indigo-200/60 dark:hover:ring-indigo-700/40 hover:-translate-y-0.5 hover:scale-105';
    $imgCls   = 'icon-enter w-16 h-16 md:w-20 md:h-20 object-contain dark:invert '
              . 'transition-transform duration-200 ease-out group-hover:scale-110 mx-auto';
    $titleCls = 'mt-2 text-base md:text-lg font-semibold text-neutral-900 dark:text-neutral-100 '
              . 'transition-colors group-hover:text-indigo-600 dark:group-hover:text-indigo-400';
    $descCls  = 'text-sm text-neutral-600 dark:text-neutral-300';
  @endphp

  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 place-items-center">

    {{-- Dashboard --}}
    <a href="{{ route('dashboard') }}" class="{{ $tile }}">
      <div class="{{ $box }}">
        <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard" class="{{ $imgCls }}">
        <div class="{{ $titleCls }}">Dashboard</div>
        <div class="{{ $descCls }}">Resumen y métricas</div>
      </div>
    </a>

    {{-- Crear pedido --}}
    <a href="{{ route('orders.create') }}" class="{{ $tile }}">
      <div class="{{ $box }}">
        <img src="{{ asset('images/crear-pedido.png') }}" alt="Crear pedido" class="{{ $imgCls }}">
        <div class="{{ $titleCls }}">Crear pedido</div>
        <div class="{{ $descCls }}">Carga rápida</div>
      </div>
    </a>

    {{-- Lista de pedidos --}}
    <a href="{{ route('orders.index') }}" class="{{ $tile }}">
      <div class="{{ $box }}">
        <img src="{{ asset('images/pedidos.png') }}" alt="Pedidos" class="{{ $imgCls }}">
        <div class="{{ $titleCls }}">Pedidos</div>
        <div class="{{ $descCls }}">Historial y gestión</div>
      </div>
    </a>

    {{-- Productos --}}
    <a href="{{ route('products.index') }}" class="{{ $tile }}">
      <div class="{{ $box }}">
        <img src="{{ asset('images/productos.png') }}" alt="Productos" class="{{ $imgCls }}">
        <div class="{{ $titleCls }}">Productos</div>
        <div class="{{ $descCls }}">Catálogo y precios</div>
      </div>
    </a>

    {{-- Servicios --}}
    <a href="{{ route('services.index') }}" class="{{ $tile }}">
      <div class="{{ $box }}">
        <img src="{{ asset('images/servicios.png') }}" alt="Servicios" class="{{ $imgCls }}">
        <div class="{{ $titleCls }}">Servicios</div>
        <div class="{{ $descCls }}">Catálogo y precios</div>
      </div>
    </a>

    {{-- Clientes --}}
    <a href="{{ route('clients.index') }}" class="{{ $tile }}">
      <div class="{{ $box }}">
        <img src="{{ asset('images/empleados.png') }}" alt="Clientes" class="{{ $imgCls }}">
        <div class="{{ $titleCls }}">Clientes</div>
        <div class="{{ $descCls }}">CRM básico</div>
      </div>
    </a>

    {{-- Métodos de Pago --}}
    <a href="{{ route('payment-methods.index') }}" class="{{ $tile }}">
      <div class="{{ $box }}">
        <img src="{{ asset('images/payment.png') }}" alt="Métodos de Pago" class="{{ $imgCls }}">
        <div class="{{ $titleCls }}">Métodos de Pago</div>
        <div class="{{ $descCls }}">Configurar pagos</div>
      </div>
    </a>

    {{-- Stock --}}
    <a href="{{ route('stock.index') }}#stock" class="{{ $tile }}">
      <div class="{{ $box }}">
        <img src="{{ asset('images/stock.png') }}" alt="Stock" class="{{ $imgCls }}">
        <div class="{{ $titleCls }}">Stock</div>
        <div class="{{ $descCls }}">Inventario</div>
      </div>
    </a>

    {{-- Gastos --}}
    <a href="{{ route('expenses.index') }}" class="{{ $tile }}">
      <div class="{{ $box }}">
        <img src="{{ asset('images/calcular-costos.png') }}" alt="Gastos" class="{{ $imgCls }}">
        <div class="{{ $titleCls }}">Gastos</div>
        <div class="{{ $descCls }}">Gestión de gastos</div>
      </div>
    </a>

    {{-- Sucursales (company o master) --}}
    @auth
      @if(auth()->user()->isMaster() || auth()->user()->isCompany())
        <a href="{{ route('company.branches.index') }}" class="{{ $tile }}">
          <div class="{{ $box }}">
            <img src="{{ asset('images/sucursales.png') }}" alt="Sucursales" class="{{ $imgCls }}">
            <div class="{{ $titleCls }}">Sucursales</div>
            <div class="{{ $descCls }}">Gestión de sedes</div>
          </div>
        </a>
      @endif
    @endauth

    {{-- Personal (company o master) --}}
    @auth
      @if(auth()->user()->isMaster() || auth()->user()->isCompany())
        <a href="{{ route('company.employees.index') }}" class="{{ $tile }}">
          <div class="{{ $box }}">
            <img src="{{ asset('images/empleados.png') }}" alt="Personal" class="{{ $imgCls }}">
            <div class="{{ $titleCls }}">Personal</div>
            <div class="{{ $descCls }}">Empleados</div>
          </div>
        </a>
      @endif
    @endauth

    {{-- Master: invitaciones y usuarios --}}
    @auth
      @if(auth()->user()->isMaster())
        <a href="{{ route('master.invitations.index') }}" class="{{ $tile }}">
          <div class="{{ $box }}">
            <img src="{{ asset('images/agregar-user.png') }}" alt="Invitaciones" class="{{ $imgCls }}">
            <div class="{{ $titleCls }}">Generar usuarios</div>
            <div class="{{ $descCls }}">Invitaciones</div>
          </div>
        </a>
        <a href="{{ route('master.users.index') }}" class="{{ $tile }}">
          <div class="{{ $box }}">
            <img src="{{ asset('images/gestionar-user.png') }}" alt="Usuarios" class="{{ $imgCls }}">
            <div class="{{ $titleCls }}">Gestionar usuarios</div>
            <div class="{{ $descCls }}">Administración</div>
          </div>
        </a>
      @endif
    @endauth

    {{-- Configuración --}}
    <a href="{{ route('settings') }}" class="{{ $tile }}">
      <div class="{{ $box }}">
        <img src="{{ asset('images/configuraciones.png') }}" alt="Configuración" class="{{ $imgCls }}">
        <div class="{{ $titleCls }}">Configuración</div>
        <div class="{{ $descCls }}">Preferencias</div>
      </div>
    </a>

        {{-- Soporte --}}
    <a href="{{ route('support.index') }}" class="{{ $tile }}">
      <div class="{{ $box }}">
        <img src="{{ asset('images/soporte.png') }}" alt="Soporte" class="{{ $imgCls }}">
        <div class="{{ $titleCls }}">Soporte</div>
        <div class="{{ $descCls }}">Reclamos y mensajes</div>
      </div>
    </a>

  </div>
</div>
@endsection
