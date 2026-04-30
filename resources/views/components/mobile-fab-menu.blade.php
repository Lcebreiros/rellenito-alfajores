@php
    use Illuminate\Support\Facades\Route;

    $ordersUrl = Route::has('orders.index') ? route('orders.index') : (Route::has('orders.create') ? route('orders.create') : '#');
@endphp

<div
  id="mobile-fab-menu"
  class="md:hidden"
  aria-label="{{ __('nav.settings') }}"
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
         role="menuitem">{{ __('nav.dashboard') }}</a>

      <a href="{{ route('nexum') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('nexum') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">
        <span class="text-violet-500">✦</span> {{ __('nav.nexum') }}
      </a>

      <a href="{{ route('orders.create') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('orders.create') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">{{ __('nav.create_sale') }}</a>

      <a href="{{ $ordersUrl }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('orders.index') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">{{ __('nav.sales_list') }}</a>

      <a href="{{ route('products.index') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('products.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">{{ __('nav.products') }}</a>

      <a href="{{ route('services.index') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('services.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">{{ __('nav.services') }}</a>

      <a href="{{ route('clients.index') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('clients.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">{{ __('nav.clients') }}</a>

      <a href="{{ route('stock.index') }}#stock" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->fullUrlIs(route('stock.index').'#stock') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">{{ __('nav.stock') }}</a>

      <a href="{{ route('expenses.index') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('costs.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">{{ __('nav.costing') }}</a>

      @auth
        @if(auth()->user()->isMaster() || auth()->user()->hasModule('alquileres'))
          <div class="h-px bg-neutral-200 dark:bg-neutral-800 my-1"></div>
          <p class="px-3 pt-1 pb-0.5 text-[10px] font-semibold uppercase tracking-widest text-neutral-400 dark:text-neutral-500">{{ __('nav.rentals') }}</p>
          <a href="{{ Route::has('rentals.calendar') ? route('rentals.calendar') : '#' }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('rentals.calendar') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">{{ __('nav.bookings') }}</a>
          <a href="{{ Route::has('rentals.bookings.index') ? route('rentals.bookings.index') : '#' }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('rentals.bookings.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">{{ __('nav.bookings') }}</a>
          <a href="{{ Route::has('rentals.spaces.index') ? route('rentals.spaces.index') : '#' }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('rentals.spaces.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">{{ __('nav.spaces') }}</a>
          <div class="h-px bg-neutral-200 dark:bg-neutral-800 my-1"></div>
        @endif
      @endauth

      @auth
        @if(auth()->user()->isMaster() || auth()->user()->isCompany())
          <a href="{{ route('company.branches.index') }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('company.branches.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">{{ __('nav.branches') }}</a>
        @endif
      @endauth

      @auth
        @if(auth()->user()->isMaster() || auth()->user()->isCompany())
          <a href="{{ route('company.employees.index') }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('company.employees.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">{{ __('nav.employees') }}</a>
        @endif
      @endauth

      @auth
        @if(auth()->user()->isMaster())
          <a href="{{ route('master.invitations.index') }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('master.invitations.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">{{ __('nav.generate_users') }}</a>
        @endif
      @endauth

      @auth
        @if(auth()->user()->isMaster())
          <a href="{{ route('master.users.index') }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('master.users.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">{{ __('nav.manage_users') }}</a>
        @endif
      @endauth

      @auth
        @if(auth()->user()->isMaster())
          <a href="{{ route('trial-requests') }}" wire:navigate data-turbo="false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('trial-requests') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
             role="menuitem">{{ __('nav.requests') }}</a>
        @endif
      @endauth

      <a href="{{ route('settings') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('settings') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">{{ __('nav.settings') }}</a>

      <a href="{{ route('support.index') }}" wire:navigate data-turbo="false"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ request()->routeIs('support.*') ? 'bg-neutral-100 dark:bg-neutral-800' : '' }}"
         role="menuitem">{{ __('nav.support') }}</a>

      <div class="h-px bg-neutral-200 dark:bg-neutral-800 my-2"></div>

      <form method="POST" action="{{ route('logout') }}" class="m-0" role="none">
        @csrf
        <button type="submit"
                class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400"
                role="menuitem">{{ __('nav.logout') }}</button>
      </form>
    </div>
  </div>

  <!-- Overlay -->
  <div id="fab-menu-overlay" style="display: none; opacity: 0;" class="fixed inset-0 z-50 bg-black/20 transition-opacity duration-150"></div>
</div>

<script>
(function() {
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

      overlay.style.display = 'block';
      requestAnimationFrame(() => {
        overlay.style.opacity = '1';
      });

      panel.style.display = 'block';
      requestAnimationFrame(() => {
        panel.style.opacity = '1';
        panel.style.transform = 'translateY(0)';
      });

      iconMenu.style.display = 'none';
      iconClose.style.display = 'block';
    }

    function closeMenu() {
      isOpen = false;
      button.setAttribute('aria-expanded', 'false');

      overlay.style.opacity = '0';
      setTimeout(() => {
        overlay.style.display = 'none';
      }, 150);

      panel.style.opacity = '0';
      panel.style.transform = 'translateY(0.5rem)';
      setTimeout(() => {
        panel.style.display = 'none';
      }, 150);

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

    button.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', closeMenu);

    document.addEventListener('click', function(e) {
      if (isOpen && !panel.contains(e.target) && !button.contains(e.target)) {
        closeMenu();
      }
    });

    document.addEventListener('keydown', function(e) {
      if (isOpen && e.key === 'Escape') {
        closeMenu();
      }
    });
  }
})();
</script>
