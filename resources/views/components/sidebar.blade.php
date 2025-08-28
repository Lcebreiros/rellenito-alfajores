@php
    $ordersUrl = \Illuminate\Support\Facades\Route::has('orders.index')
        ? route('orders.index')
        : route('orders.create');

    $active = 'text-indigo-700 dark:text-indigo-300 font-semibold';
    $idle   = 'text-slate-800 dark:text-slate-100 hover:text-slate-900 dark:hover:text-white';
@endphp

{{-- CSS directo para asegurar el hover, sin depender del build --}}
<style>
  .nav-link{
    transform: translateZ(0);
    transition: transform .28s cubic-bezier(.34,1.56,.64,1);
    will-change: transform;
  }
  .nav-link:hover{
    transform: scale(1.10);
  }
  .nav-icon{
    width: 1.5rem; height: 1.5rem; /* w-6 h-6 */
    transform: translateZ(0);
    transition: transform .28s cubic-bezier(.34,1.56,.64,1);
    transform-origin: left center;
    will-change: transform;
  }
  .nav-link:hover .nav-icon{
    transform: scale(1.6);
  }
  /* Cuando está colapsado, el origen del icono pasa al centro para crecer parejo */
  aside[data-collapsed="true"] .nav-icon{
    transform-origin: center center;
  }
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
        setTimeout(() => this.animating = false, 520);
      }
  }"
  x-bind:data-collapsed="collapsed ? 'true' : 'false'"
  :class="collapsed ? 'w-20' : 'w-72'"
  class="flex-none bg-white dark:bg-slate-900 border-r border-slate-200/80 dark:border-slate-700/80
         overflow-hidden transition-[width] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]
         will-change-[width]"
>
  <div class="sticky top-0 h-[100svh] flex flex-col">
    {{-- Header --}}
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-200/80 dark:border-slate-700/80">
      <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-3">
        <x-application-mark x-bind:class="collapsed ? 'h-8 w-auto' : 'h-9 w-auto'" class="transition-all duration-300" />
        <span x-show="!collapsed"
              x-transition:enter="transition-all duration-300 ease-out"
              x-transition:enter-start="opacity-0 translate-x-2"
              x-transition:enter-end="opacity-100 translate-x-0"
              x-transition:leave="transition-all duration-200 ease-in"
              x-transition:leave-start="opacity-100 translate-x-0"
              x-transition:leave-end="opacity-0 translate-x-1"
              class="font-bold text-lg text-slate-800 dark:text-slate-100">Panel</span>
      </a>

      <button
        @click="toggle()" :disabled="animating"
        class="inline-flex items-center justify-center size-9 rounded-lg
               border border-slate-200/80 dark:border-slate-600/80
               text-slate-600 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300
               transition-colors"
        :title="collapsed ? 'Expandir sidebar' : 'Contraer sidebar'">
        <svg x-show="!collapsed" x-transition.opacity class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg x-show="collapsed" x-transition.opacity class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    {{-- Search (solo expandido) --}}
    <div class="px-4 pt-4" x-show="!collapsed" x-transition.opacity>
      <div class="relative">
        <input type="text" placeholder="Buscar…"
               class="w-full rounded-xl bg-slate-50/80 dark:bg-slate-800/60
                      border border-slate-200/70 dark:border-slate-700/70
                      pl-10 pr-4 py-2.5 text-sm placeholder:text-slate-400 dark:placeholder:text-slate-500
                      focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-slate-500"
             viewBox="0 0 24 24" fill="none">
          <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5"/>
          <path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </div>
    </div>

    {{-- NAV --}}
    <nav class="flex-1 min-h-0 overflow-y-auto px-4 pt-4 pb-2 space-y-2"
         :class="animating ? 'pointer-events-none select-none' : ''">

      {{-- Dashboard --}}
      <a href="{{ route('dashboard') }}"
         class="nav-link {{ request()->routeIs('dashboard') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3' : 'flex items-center gap-3'"
         :title="collapsed ? 'Dashboard' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
            <path d="M3 12l9-9 9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M9 21V12h6v9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span x-show="!collapsed"
              x-transition:enter="transition-all duration-300 ease-out"
              x-transition:enter-start="opacity-0 translate-x-2"
              x-transition:enter-end="opacity-100 translate-x-0"
              x-transition:leave="transition-all duration-200 ease-in"
              x-transition:leave-start="opacity-100 translate-x-0"
              x-transition:leave-end="opacity-0 translate-x-1"
              class="text-sm font-semibold truncate">Dashboard</span>
      </a>

      {{-- Crear pedido --}}
      <a href="{{ route('orders.create') }}"
         class="nav-link {{ request()->routeIs('orders.create') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3' : 'flex items-center gap-3'"
         :title="collapsed ? 'Crear pedido' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
          </svg>
        </span>
        <span x-show="!collapsed"
              x-transition:enter="transition-all duration-300 ease-out"
              x-transition:enter-start="opacity-0 translate-x-2"
              x-transition:enter-end="opacity-100 translate-x-0"
              x-transition:leave="transition-all duration-200 ease-in"
              x-transition:leave-start="opacity-100 translate-x-0"
              x-transition:leave-end="opacity-0 translate-x-1"
              class="text-sm font-semibold truncate">Crear pedido</span>
      </a>

      {{-- Lista de pedidos --}}
      <a href="{{ $ordersUrl }}"
         class="nav-link {{ request()->routeIs('orders.index') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3' : 'flex items-center gap-3'"
         :title="collapsed ? 'Lista de pedidos' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
            <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </span>
        <span x-show="!collapsed"
              x-transition:enter="transition-all duration-300 ease-out"
              x-transition:enter-start="opacity-0 translate-x-2"
              x-transition:enter-end="opacity-100 translate-x-0"
              x-transition:leave="transition-all duration-200 ease-in"
              x-transition:leave-start="opacity-100 translate-x-0"
              x-transition:leave-end="opacity-0 translate-x-1"
              class="text-sm font-semibold truncate">Lista de pedidos</span>
      </a>

      {{-- Productos --}}
      <a href="{{ route('products.index') }}"
         class="nav-link {{ request()->routeIs('products.*') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3' : 'flex items-center gap-3'"
         :title="collapsed ? 'Productos' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
            <path d="M3.3 7.7 12 12l8.7-4.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </span>
        <span x-show="!collapsed"
              x-transition:enter="transition-all duration-300 ease-out"
              x-transition:enter-start="opacity-0 translate-x-2"
              x-transition:enter-end="opacity-100 translate-x-0"
              x-transition:leave="transition-all duration-200 ease-in"
              x-transition:leave-start="opacity-100 translate-x-0"
              x-transition:leave-end="opacity-0 translate-x-1"
              class="text-sm font-semibold truncate">Productos</span>
      </a>

      {{-- Stock --}}
      <a href="{{ route('stock.index') }}#stock"
         class="nav-link {{ request()->fullUrlIs(route('products.index').'#stock') ? $active : $idle }}"
         :class="collapsed ? 'justify-center flex items-center gap-3' : 'flex items-center gap-3'"
         :title="collapsed ? 'Stock' : null">
        <span class="shrink-0 flex items-center justify-center w-8 h-8">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
            <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
            <path d="M7 15h10M7 11h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <circle cx="17" cy="11" r="1" fill="currentColor"/>
          </svg>
        </span>
        <span x-show="!collapsed"
              x-transition:enter="transition-all duration-300 ease-out"
              x-transition:enter-start="opacity-0 translate-x-2"
              x-transition:enter-end="opacity-100 translate-x-0"
              x-transition:leave="transition-all duration-200 ease-in"
              x-transition:leave-start="opacity-100 translate-x-0"
              x-transition:leave-end="opacity-0 translate-x-1"
              class="text-sm font-semibold truncate">Stock</span>
      </a>
    </nav>

    {{-- Footer --}}
    <div class="border-t border-slate-200/80 dark:border-slate-700/80 p-4">
      <div class="flex items-center gap-3 mb-3" :class="collapsed ? 'justify-center' : ''">
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
          <img class="w-9 h-9 rounded-full object-cover ring-2 ring-slate-200/60 dark:ring-slate-700/60"
               src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
        @endif
        <div class="min-w-0" x-show="!collapsed"
             x-transition:enter="transition-opacity duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
          <div class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate">{{ Auth::user()->name }}</div>
          <div class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</div>
        </div>
      </div>

      <div class="grid" :class="collapsed ? 'grid-cols-1 gap-2' : 'grid-cols-2 gap-2'">
        <a href="{{ route('profile.show') }}"
           class="flex items-center justify-center gap-2 text-xs py-2 px-3 rounded-lg 
                  border border-slate-200/80 dark:border-slate-700/80
                  hover:bg-slate-50 dark:hover:bg-slate-800/50
                  transition-colors"
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
                         border border-slate-200/80 dark:border-slate-700/80
                         hover:bg-red-50 dark:hover:bg-red-900/20
                         transition-colors"
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
