@php
  use Illuminate\Support\Facades\Route;

  // Igual que en tu sidebar:
  $ordersUrl = Route::has('orders.index') ? route('orders.index') : route('orders.create');

  // helpers
  $isActive = function ($pattern) { return request()->routeIs($pattern); };

  $bar = 'fixed bottom-0 inset-x-0 z-50 md:hidden
          border-t border-neutral-200 dark:border-neutral-800
          bg-white/90 dark:bg-neutral-900/80 backdrop-blur';
  $wrap = 'mx-auto max-w-3xl px-3';
  $grid = 'grid grid-cols-6 items-center h-16 gap-1';  // 6 = 5 items + "Más"
  $pillLabel = 'text-[11px] font-medium leading-none';
@endphp

<nav x-data="{ moreOpen:false }" class="{{ $bar }}" aria-label="Navegación inferior">
  <div class="{{ $wrap }}">
    <div class="{{ $grid }}">

      {{-- Dashboard --}}
      <a href="{{ route('dashboard') }}" wire:navigate data-turbo="false"
         class="group flex justify-center"
         aria-current="{{ $isActive('dashboard') ? 'page' : 'false' }}">
        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full transition
                    {{ $isActive('dashboard') ? 'bg-white text-neutral-900 shadow-sm ring-1 ring-black/5 dark:bg-neutral-100 dark:text-neutral-900' : '' }}">
          <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard"
               class="w-6 h-6 object-contain {{ $isActive('dashboard') ? '' : 'opacity-80' }}">
          <span class="{{ $pillLabel }} {{ $isActive('dashboard') ? '' : 'sr-only' }}">Dashboard</span>
        </div>
      </a>

      {{-- Crear pedido --}}
      <a href="{{ route('orders.create') }}" wire:navigate data-turbo="false"
         class="group flex justify-center"
         aria-current="{{ $isActive('orders.create') ? 'page' : 'false' }}">
        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full transition
                    {{ $isActive('orders.create') ? 'bg-white text-neutral-900 shadow-sm ring-1 ring-black/5 dark:bg-neutral-100 dark:text-neutral-900' : '' }}">
          <img src="{{ asset('images/crear-pedido.png') }}" alt="Crear pedido"
               class="w-6 h-6 object-contain {{ $isActive('orders.create') ? '' : 'opacity-80' }}">
          <span class="{{ $pillLabel }} {{ $isActive('orders.create') ? '' : 'sr-only' }}">Crear</span>
        </div>
      </a>

      {{-- Lista de pedidos --}}
      <a href="{{ $ordersUrl }}" wire:navigate data-turbo="false"
         class="group flex justify-center"
         aria-current="{{ $isActive('orders.index') ? 'page' : 'false' }}">
        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full transition
                    {{ $isActive('orders.index') ? 'bg-white text-neutral-900 shadow-sm ring-1 ring-black/5 dark:bg-neutral-100 dark:text-neutral-900' : '' }}">
          <img src="{{ asset('images/pedidos.png') }}" alt="Pedidos"
               class="w-6 h-6 object-contain {{ $isActive('orders.index') ? '' : 'opacity-80' }}">
          <span class="{{ $pillLabel }} {{ $isActive('orders.index') ? '' : 'sr-only' }}">Pedidos</span>
        </div>
      </a>

      {{-- Productos --}}
      <a href="{{ route('products.index') }}" wire:navigate data-turbo="false"
         class="group flex justify-center"
         aria-current="{{ $isActive('products.*') ? 'page' : 'false' }}">
        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full transition
                    {{ $isActive('products.*') ? 'bg-white text-neutral-900 shadow-sm ring-1 ring-black/5 dark:bg-neutral-100 dark:text-neutral-900' : '' }}">
          <img src="{{ asset('images/productos.png') }}" alt="Productos"
               class="w-6 h-6 object-contain {{ $isActive('products.*') ? '' : 'opacity-80' }}">
          <span class="{{ $pillLabel }} {{ $isActive('products.*') ? '' : 'sr-only' }}">Productos</span>
        </div>
      </a>

      {{-- Stock (el hash #stock no viaja al server; usamos la ruta base para activo) --}}
      <a href="{{ route('stock.index') }}#stock" wire:navigate data-turbo="false"
         class="group flex justify-center"
         aria-current="{{ $isActive('stock.index') ? 'page' : 'false' }}">
        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full transition
                    {{ $isActive('stock.index') ? 'bg-white text-neutral-900 shadow-sm ring-1 ring-black/5 dark:bg-neutral-100 dark:text-neutral-900' : '' }}">
          <img src="{{ asset('images/stock.png') }}" alt="Stock"
               class="w-6 h-6 object-contain {{ $isActive('stock.index') ? '' : 'opacity-80' }}">
          <span class="{{ $pillLabel }} {{ $isActive('stock.index') ? '' : 'sr-only' }}">Stock</span>
        </div>
      </a>

{{-- Más: menú con Configuración, Perfil y Salir (usa tus mismas rutas/íconos) --}}
<div class="relative flex justify-center" x-data="{ moreOpen:false }">
  <button type="button"
          @click="moreOpen = !moreOpen"
          @keydown.escape.window="moreOpen=false"
          class="group"
          aria-haspopup="menu" :aria-expanded="moreOpen">
    <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full transition"
         :class="moreOpen ? 'bg-white text-neutral-900 shadow-sm ring-1 ring-black/5 dark:bg-neutral-100 dark:text-neutral-900' : ''">
      {{-- avatar como icono --}}
      @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
        <img class="w-6 h-6 rounded-full object-cover"
             src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
      @else
        <img src="{{ asset('images/configuraciones.png') }}" alt="Más" class="w-6 h-6 object-contain opacity-80">
      @endif
      <span class="text-[11px] font-medium leading-none" :class="moreOpen ? '' : 'sr-only'">Más</span>
    </div>
  </button>

  {{-- Popover --}}
  <div x-show="moreOpen" x-transition
       @click.outside="moreOpen=false"
       class="absolute bottom-[56px] right-0 w-56 rounded-xl border
              border-neutral-200 dark:border-neutral-800
              bg-white dark:bg-neutral-900 shadow-xl overflow-hidden">

    {{-- Configuración (usa route('settings') como en tu sidebar) --}}
    <a href="{{ route('settings') }}" wire:navigate data-turbo="false"
       class="flex items-center gap-3 px-3 py-2 text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800">
      <img src="{{ asset('images/configuraciones.png') }}" alt="Configuración" class="w-5 h-5">
      <span>Configuración</span>
    </a>

    {{-- Perfil --}}
    <a href="{{ route('profile.show') }}" wire:navigate data-turbo="false"
       class="flex items-center gap-3 px-3 py-2 text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800">
      @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
        <img class="w-6 h-6 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
      @else
        <img src="{{ asset('images/productos.png') }}" alt="Perfil" class="w-5 h-5">
      @endif
      <span>Perfil</span>
    </a>

    {{-- Salir --}}
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit"
              class="w-full flex items-center gap-3 px-3 py-2 text-left text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800">
        <img src="{{ asset('images/pedidos.png') }}" alt="Salir" class="w-5 h-5">
        <span>Salir</span>
      </button>
    </form>
  </div>
</div>


    </div>
  </div>

  {{-- safe area iOS --}}
  <div class="pb-[env(safe-area-inset-bottom)]"></div>
</nav>
