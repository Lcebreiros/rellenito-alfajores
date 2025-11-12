@php
    use Illuminate\Support\Facades\Route;

    $ordersUrl = Route::has('orders.index') ? route('orders.index') : (Route::has('orders.create') ? route('orders.create') : '#');
@endphp

<div
  id="mobile-fab-menu"
  class="md:hidden"
  aria-label="Menú rápido móvil"
>
  <!-- Botón flotante -->
  <button
    type="button"
    id="fab-menu-button"
    aria-expanded="false"
    style="bottom: 24px; right: 16px;"
    class="fixed z-[60] inline-flex items-center justify-center w-12 h-12 rounded-full shadow-lg border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 active:scale-95 transition-transform"
  >
    <!-- Ícono de apps/menú (grid 3x3) -->
    <svg id="fab-icon-menu" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
      <path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
    </svg>
    <!-- Ícono de X para cerrar -->
    <svg id="fab-icon-close" style="display: none;" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
    </svg>
  </button>

  <!-- Menú emergente hacia arriba -->
  <div
    id="fab-menu-panel"
    style="display: none; opacity: 0; transform: translateY(0.5rem); bottom: 96px; right: 16px; max-height: calc(100vh - 130px);"
    class="fixed z-[60] w-[min(92vw,22rem)] rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xl overflow-hidden transition-all duration-150 ease-out"
    role="menu"
  >
    <div class="overflow-auto p-2 space-y-1" style="max-height: calc(100vh - 130px);">
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

      <a href="{{ route('expenses.index') }}" wire:navigate data-turbo="false"
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

      <form method="POST" action="{{ route('logout') }}" class="m-0" role="none">
        @csrf
        <button type="submit"
                class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400"
                role="menuitem">Salir</button>
      </form>
    </div>
  </div>

  <!-- Overlay -->
  <div id="fab-menu-overlay" style="display: none; opacity: 0;" class="fixed inset-0 z-50 bg-black/20 transition-opacity duration-150"></div>
</div>

<script>
(function() {
  // Esperar a que el DOM esté listo
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFabMenu);
  } else {
    initFabMenu();
  }

  function initFabMenu() {
    const button = document.getElementById('fab-menu-button');
    const panel = document.getElementById('fab-menu-panel');
    const overlay = document.getElementById('fab-menu-overlay');
    const iconMenu = document.getElementById('fab-icon-menu');
    const iconClose = document.getElementById('fab-icon-close');

    if (!button || !panel || !overlay || !iconMenu || !iconClose) {
      console.error('FAB menu: elementos no encontrados');
      return;
    }

    let isOpen = false;

    function openMenu() {
      isOpen = true;
      button.setAttribute('aria-expanded', 'true');

      // Mostrar overlay
      overlay.style.display = 'block';
      requestAnimationFrame(() => {
        overlay.style.opacity = '1';
      });

      // Mostrar panel con animación
      panel.style.display = 'block';
      requestAnimationFrame(() => {
        panel.style.opacity = '1';
        panel.style.transform = 'translateY(0)';
      });

      // Cambiar iconos
      iconMenu.style.display = 'none';
      iconClose.style.display = 'block';
    }

    function closeMenu() {
      isOpen = false;
      button.setAttribute('aria-expanded', 'false');

      // Ocultar overlay
      overlay.style.opacity = '0';
      setTimeout(() => {
        overlay.style.display = 'none';
      }, 150);

      // Ocultar panel con animación
      panel.style.opacity = '0';
      panel.style.transform = 'translateY(0.5rem)';
      setTimeout(() => {
        panel.style.display = 'none';
      }, 150);

      // Cambiar iconos
      iconMenu.style.display = 'block';
      iconClose.style.display = 'none';
    }

    function toggleMenu() {
      if (isOpen) {
        closeMenu();
      } else {
        openMenu();
      }
    }

    // Event listeners
    button.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', closeMenu);

    // Cerrar al hacer clic fuera del panel (pero dentro del overlay ya está cubierto)
    document.addEventListener('click', function(e) {
      if (isOpen && !panel.contains(e.target) && !button.contains(e.target)) {
        closeMenu();
      }
    });

    // Cerrar con tecla Escape
    document.addEventListener('keydown', function(e) {
      if (isOpen && e.key === 'Escape') {
        closeMenu();
      }
    });
  }
})();
</script>
