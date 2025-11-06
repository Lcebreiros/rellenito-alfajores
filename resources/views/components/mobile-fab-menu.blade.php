@php
    use Illuminate\Support\Facades\Route;

    $ordersUrl = Route::has('orders.index') ? route('orders.index') : (Route::has('orders.create') ? route('orders.create') : '#');
@endphp

<div
  x-data="{ open:false }"
  class="md:hidden"
  aria-label="Menú rápido móvil"
>
  <!-- Overlay (debe ir primero en el DOM) -->
  <div x-cloak x-show="open" class="fixed inset-0 z-40 bg-black/20" @click="open=false"></div>

  <!-- Botón flotante -->
  <button
    type="button"
    @click="open = !open"
    :aria-expanded="open ? 'true' : 'false'"
    class="fixed right-4 bottom-6 z-50 inline-flex items-center justify-center w-12 h-12 rounded-full shadow-lg border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 active:scale-95 transition-transform"
  >
    <!-- Ícono de apps/menú (grid 3x3) -->
    <svg x-show="!open" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
      <path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
    </svg>
    <!-- Ícono de X para cerrar -->
    <svg x-show="open" x-cloak class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
    </svg>
  </button>

  <!-- Menú emergente hacia arriba -->
  <div
    x-cloak
    x-show="open"
    @click.outside="open = false"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed right-4 bottom-24 z-50 w-[min(92vw,22rem)] rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xl overflow-hidden"
    role="menu"
  >
    <div class="max-h-[70vh] overflow-auto p-2 space-y-1">
      <a href="{{ route('dashboard') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('dashboard') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">Dashboard</a>

      <a href="{{ route('orders.create') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('orders.create') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">Crear pedido</a>

      <a href="{{ $ordersUrl }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('orders.index') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">Lista de pedidos</a>

      <a href="{{ route('products.index') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('products.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">Productos</a>

      <a href="{{ route('services.index') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('services.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">Servicios</a>

      <a href="{{ route('clients.index') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('clients.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">Clientes</a>

      <a href="{{ route('stock.index') }}#stock" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->fullUrlIs(route('stock.index').'#stock') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">Stock</a>

      <a href="{{ route('costing.calculator') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('costs.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">Calcular costos</a>

      @auth
        @if(auth()->user()->isMaster() || auth()->user()->isCompany())
          <a href="{{ route('company.branches.index') }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('company.branches.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">Sucursales</a>
        @endif
      @endauth

      @auth
        @if(auth()->user()->isMaster() || auth()->user()->isCompany())
          <a href="{{ route('company.employees.index') }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('company.employees.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">Personal</a>
        @endif
      @endauth

      @auth
        @if(auth()->user()->isMaster())
          <a href="{{ route('master.invitations.index') }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('master.invitations.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">Generar usuarios</a>
        @endif
      @endauth

      @auth
        @if(auth()->user()->isMaster())
          <a href="{{ route('master.users.index') }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('master.users.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">Gestionar usuarios</a>
        @endif
      @endauth

      <a href="{{ route('settings') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('settings') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">Configuración</a>

      <a href="{{ route('support.index') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('support.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">Soporte</a>

      <div class="h-px bg-neutral-200 dark:bg-neutral-800 my-2"></div>

      <a href="{{ route('profile.show') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('profile.show') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">Perfil</a>

      <form method="POST" action="{{ route('logout') }}" class="m-0" role="none">
        @csrf
        <button type="submit"
                class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400"
                role="menuitem">Salir</button>
      </form>
    </div>
  </div>
</div>

