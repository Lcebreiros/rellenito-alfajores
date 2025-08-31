@php
    $ordersUrl = \Illuminate\Support\Facades\Route::has('orders.index')
        ? route('orders.index')
        : route('orders.create');

    // activo en negro con semibold
    $active = 'text-slate-900 font-semibold bg-slate-100';
    // inactivo en gris
    $idle   = 'text-slate-600 hover:text-slate-900 hover:bg-slate-50';
@endphp

{{-- CSS directo para asegurar el hover, sin depender del build --}}
<style>
  .nav-link{
    transform: translateZ(0);
    transition: transform .28s cubic-bezier(.34,1.56,.64,1);
    will-change: transform;
    border-radius: 0.5rem;
  }
  .nav-link:hover{ transform: scale(1.02); }
  .nav-icon{
    width: 1.5rem; height: 1.5rem;
    transform: translateZ(0);
    transition: transform .28s cubic-bezier(.34,1.56,.64,1);
    transform-origin: left center;
    will-change: transform;
  }
  .nav-link:hover .nav-icon{ transform: scale(1.4); }
  aside[data-collapsed="true"] .nav-icon{ transform-origin: center center; }
  .toggle-btn { position: absolute; right: 1rem; top: 1rem; z-index: 10; }
</style>

<aside
  x-data="{
      collapsed: false,
      animating: false,
      init() {
        const saved = localStorage.getItem('sidebar:collapsed');
        this.collapsed = saved === '1';
      },
      toggle() {
        if (this.animating) return;
        this.animating = true;
        this.collapsed = !this.collapsed;
        localStorage.setItem('sidebar:collapsed', this.collapsed ? '1' : '0');
        // Notificar al layout para que ajuste el margen
        window.dispatchEvent(new CustomEvent('sidebar:toggle', { detail: this.collapsed }));
        setTimeout(() => this.animating = false, 520);
      }
  }"
  x-bind:data-collapsed="collapsed ? 'true' : 'false'"
  :class="collapsed ? 'w-20' : 'w-72'"
  class="fixed inset-y-0 left-0 z-40  {{-- ocupa todo el alto: top-0 y bottom-0 --}}
         bg-white border-r border-slate-200
         overflow-hidden transition-[width] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]">
  <div class="h-full flex flex-col">
    {{-- Header --}}
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-200 relative">
      <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-3">
        <x-application-mark x-bind:class="collapsed ? 'h-8 w-auto' : 'h-9 w-auto'" class="transition-all duration-300" />
        <span x-show="!collapsed" x-transition class="font-bold text-lg text-slate-800">Panel</span>
      </a>

      {{-- Botón de toggle reposicionado --}}
      <button
        @click="toggle()" :disabled="animating"
        class="toggle-btn inline-flex items-center justify-center size-9 rounded-lg
               border border-slate-200 bg-white
               text-slate-600 hover:text-slate-700 hover:bg-slate-50
               transition-colors shadow-sm"
        :title="collapsed ? 'Expandir sidebar' : 'Contraer sidebar'">
        <svg x-show="!collapsed" x-transition.opacity class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg x-show="collapsed" x-transition.opacity class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    {{-- NAV (scroll propio dentro del alto total) --}}
    <nav class="flex-1 min-h-0 overflow-y-auto px-4 pt-4 pb-2 space-y-1"
         :class="animating ? 'pointer-events-none select-none' : ''">

      {{-- Dashboard --}}
      <a href="{{ route('dashboard') }}"
         class="nav-link {{ request()->routeIs('dashboard') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Dashboard' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate">Dashboard</span>
      </a>

      {{-- Crear pedido --}}
      <a href="{{ route('orders.create') }}"
         class="nav-link {{ request()->routeIs('orders.create') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Crear pedido' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/crear-pedido.png') }}" alt="Crear pedido" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate">Crear pedido</span>
      </a>

      {{-- Lista de pedidos --}}
      <a href="{{ $ordersUrl }}"
         class="nav-link {{ request()->routeIs('orders.index') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Lista de pedidos' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/pedidos.png') }}" alt="Pedidos" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate">Lista de pedidos</span>
      </a>

      {{-- Productos --}}
      <a href="{{ route('products.index') }}"
         class="nav-link {{ request()->routeIs('products.*') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Productos' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/productos.png') }}" alt="Productos" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate">Productos</span>
      </a>

      {{-- Stock --}}
      <a href="{{ route('stock.index') }}#stock"
         class="nav-link {{ request()->fullUrlIs(route('products.index').'#stock') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Stock' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/stock.png') }}" alt="Stock" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate">Stock</span>
      </a>

      {{-- Calcular costos --}}
      <a href="{{ route('costing.calculator') }}"
         class="nav-link {{ request()->routeIs('costs.*') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3 p-3' : 'flex items-center gap-3 p-3'"
         :title="collapsed ? 'Calcular costos' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <img src="{{ asset('images/calcular-costos.png') }}" alt="Calcular costos" class="nav-icon">
        </span>
        <span x-show="!collapsed" x-transition class="text-sm font-semibold truncate">Calcular costos</span>
      </a>

    </nav>

    {{-- Footer --}}
    <div class="border-t border-slate-200 p-4">
      <div class="flex items-center gap-3 mb-3" :class="collapsed ? 'justify-center' : ''">
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
          <img class="w-9 h-9 rounded-full object-cover ring-2 ring-slate-200"
               src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
        @endif
        <div class="min-w-0" x-show="!collapsed" x-transition>
          <div class="text-sm font-medium text-slate-800 truncate">{{ Auth::user()->name }}</div>
          <div class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</div>
        </div>
      </div>

      <div class="grid" :class="collapsed ? 'grid-cols-1 gap-2' : 'grid-cols-2 gap-2'">
        <a href="{{ route('profile.show') }}"
           class="flex items-center justify-center gap-2 text-xs py-2 px-3 rounded-lg 
                  border border-slate-200 hover:bg-slate-50 transition-colors"
           :title="collapsed ? 'Ver perfil' : null">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
            <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm7 9a7 7 0 0 0-14 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <span x-show="!collapsed" x-transition.opacity class="font-medium">Perfil</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button type="submit"
                  class="w-full flex items-center justify-center gap-2 text-xs py-2 px-3 rounded-lg 
                         border border-slate-200 hover:bg-red-50 transition-colors"
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
</aside>
