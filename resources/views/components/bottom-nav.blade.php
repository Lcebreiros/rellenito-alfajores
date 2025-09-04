<nav
  x-data="bottomBar()"
  x-init="init({{ $activeIndex }})"
  class="{{ $bar }}"
  aria-label="Navegación inferior"
>
  {{-- PASTILLA --}}
  <div class="absolute inset-x-0 top-0 h-16 px-2 sm:px-3 pointer-events-none">
    <div class="mx-auto max-w-3xl h-full relative" x-ref="track">
      <div x-ref="indicator"
           class="indicator absolute top-2 h-12 rounded-2xl
                  bg-gradient-to-br from-white to-neutral-50 
                  dark:from-neutral-100 dark:to-neutral-200
                  shadow-lg shadow-neutral-900/10 dark:shadow-black/20
                  ring-1 ring-black/[0.08] dark:ring-white/[0.1]
                  will-change: transform, width
                  pointer-events-none">
        <div class="absolute inset-x-2 top-0.5 h-px bg-white/60 rounded-full"></div>
      </div>
    </div>
  </div>

  <div class="{{ $wrap }}">
    <div class="{{ $grid }}" x-ref="tabsWrap">
      {{-- Dashboard --}}
      <a href="{{ route('dashboard') }}" wire:navigate data-turbo="false"
         @click="setActive(0)"
         data-tab
         class="group flex justify-center touch-manipulation transition-all duration-200 hover:scale-105 active:scale-95 flex-1">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px] transition-all duration-300">
          <div class="relative">
            <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard"
                 class="w-6 h-6 object-contain transition-transform duration-300 {{ $isActive('dashboard') ? 'scale-110 drop-shadow-sm' : 'opacity-80 group-hover:opacity-100' }}">
          </div>
          <span class="{{ $pillLabel }} transition-opacity duration-300 {{ $isActive('dashboard') ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300' }}">
            Dashboard
          </span>
        </div>
      </a>

      {{-- Crear pedido --}}
      <a href="{{ route('orders.create') }}" wire:navigate data-turbo="false"
         @click="setActive(1)"
         data-tab
         class="group flex justify-center touch-manipulation transition-all duration-200 hover:scale-105 active:scale-95 flex-1">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px] transition-all duration-300">
          <div class="relative">
            <img src="{{ asset('images/crear-pedido.png') }}" alt="Crear pedido"
                 class="w-6 h-6 object-contain transition-transform duration-300 {{ $isActive('orders.create') ? 'scale-110 drop-shadow-sm' : 'opacity-80 group-hover:opacity-100' }}">
          </div>
          <span class="{{ $pillLabel }} {{ $isActive('orders.create') ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300' }}">
            Crear
          </span>
        </div>
      </a>

      {{-- Lista de pedidos --}}
      <a href="{{ $ordersUrl }}" wire:navigate data-turbo="false"
         @click="setActive(2)"
         data-tab
         class="group flex justify-center touch-manipulation transition-all duration-200 hover:scale-105 active:scale-95 flex-1">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px]">
          <div class="relative">
            <img src="{{ asset('images/pedidos.png') }}" alt="Pedidos"
                 class="w-6 h-6 object-contain transition-transform duration-300 {{ $isActive('orders.index') ? 'scale-110 drop-shadow-sm' : 'opacity-80 group-hover:opacity-100' }}">
          </div>
          <span class="{{ $pillLabel }} {{ $isActive('orders.index') ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300' }}">
            Pedidos
          </span>
        </div>
      </a>

      {{-- Productos --}}
      <a href="{{ route('products.index') }}" wire:navigate data-turbo="false"
         @click="setActive(3)"
         data-tab
         class="group flex justify-center touch-manipulation transition-all duration-200 hover:scale-105 active:scale-95 flex-1">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px]">
          <div class="relative">
            <img src="{{ asset('images/productos.png') }}" alt="Productos"
                 class="w-6 h-6 object-contain transition-transform duration-300 {{ $isActive('products.*') ? 'scale-110 drop-shadow-sm' : 'opacity-80 group-hover:opacity-100' }}">
          </div>
          <span class="{{ $pillLabel }} {{ $isActive('products.*') ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300' }}">
            Productos
          </span>
        </div>
      </a>

      {{-- Stock --}}
      <a href="{{ route('stock.index') }}#stock" wire:navigate data-turbo="false"
         @click="setActive(4)"
         data-tab
         class="group flex justify-center touch-manipulation transition-all duration-200 hover:scale-105 active:scale-95 flex-1">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px]">
          <div class="relative">
            <img src="{{ asset('images/stock.png') }}" alt="Stock"
                 class="w-6 h-6 object-contain transition-transform duration-300 {{ $isActive('stock.index') ? 'scale-110 drop-shadow-sm' : 'opacity-80 group-hover:opacity-100' }}">
          </div>
          <span class="{{ $pillLabel }} {{ $isActive('stock.index') ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300' }}">
            Stock
          </span>
        </div>
      </a>

      {{-- Más --}}
      <div class="relative flex justify-center flex-1">
        <button type="button"
                @click.stop="toggleMore(5)"
                @keydown.escape.window="moreOpen = false"
                @click.outside="moreOpen = false"
                data-tab
                class="group touch-manipulation transition-all duration-200 hover:scale-105 active:scale-95 w-full">
          <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px]"
               :class="moreOpen ? 'scale-105' : ''">
            <div class="relative">
              @auth
                <img class="w-6 h-6 rounded-full object-cover ring-2 transition-all duration-300"
                     :class="moreOpen ? 'ring-white dark:ring-neutral-200 scale-110 drop-shadow-sm' : 'ring-transparent opacity-80 group-hover:opacity-100'"
                     src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
              @else
                <div class="w-6 h-6 rounded-full bg-neutral-200 dark:bg-neutral-700"></div>
              @endauth
            </div>
            <span class="{{ $pillLabel }}"
                  :class="moreOpen ? 'text-neutral-900 dark:text-neutral-900 font-bold' : 'text-neutral-600 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-neutral-300'">
              Más
            </span>
          </div>
        </button>

        {{-- (tu popover queda igual) --}}
        <!-- … -->
      </div>
      {{-- /Más --}}
    </div>
  </div>

  <div class="h-[env(safe-area-inset-bottom)] min-h-[8px] bg-gradient-to-t from-neutral-50/50 to-transparent dark:from-neutral-900/50"></div>
</nav>

<style>
  .indicator{
    /* Valores por defecto */
    --x: 0px;
    --w: 64px;
    width: var(--w);
    transform: translate3d(var(--x), 0, 0);
    transition:
      transform 280ms cubic-bezier(.22,1,.36,1),
      width     280ms cubic-bezier(.22,1,.36,1);
    backface-visibility: hidden;
    contain: paint;
  }
  @media (prefers-reduced-motion: reduce){
    .indicator{ transition: none !important; }
  }
</style>

{{-- Script Alpine (puede ir al pie del archivo) --}}
<script>
  function bottomBar(){
    let rafId = null;

    const measure = (el) => el.getBoundingClientRect();

    const round = (v) => Math.round(v); // evita subpíxeles (parpadeo)

    return {
      moreOpen: false,
      activeIndex: 0,
      init(startIndex){
        this.activeIndex = Number.isFinite(startIndex) ? startIndex : 0;

        // 1er ajuste tras el render
        this.$nextTick(() => this.updateIndicator());

        // Observadores agrupados en rAF
        const update = () => this.updateIndicator();

        // Livewire v3
        window.addEventListener('livewire:navigated', update, { passive: true });

        // Turbo (si lo usás)
        window.addEventListener('turbo:load', update, { passive: true });

        // Resize / rotación
        window.addEventListener('resize', update, { passive: true });
        window.addEventListener('orientationchange', update, { passive: true });

        // ResizeObserver del track
        const ro = new ResizeObserver(update);
        if (this.$refs?.track) ro.observe(this.$refs.track);
      },
      setActive(i){
        this.activeIndex = i;
        this.updateIndicator();
      },
      toggleMore(i){
        this.activeIndex = i;
        this.moreOpen = !this.moreOpen;
        this.updateIndicator();
      },
      updateIndicator(){
        if (rafId) cancelAnimationFrame(rafId);
        rafId = requestAnimationFrame(() => {
          const tabsWrap = this.$refs?.tabsWrap;
          const ind = this.$refs?.indicator;
          const track = this.$refs?.track;
          if (!tabsWrap || !ind || !track) return;

          const tabs = tabsWrap.querySelectorAll('[data-tab]');
          if (!tabs.length) return;

          const i = Math.max(0, Math.min(this.activeIndex, tabs.length - 1));

          const tabRect = measure(tabs[i]);
          const parentRect = measure(track);

          const x = round(tabRect.left - parentRect.left);
          const w = round(tabRect.width);

          ind.style.setProperty('--x', x + 'px');
          ind.style.setProperty('--w', w + 'px');
        });
      }
    }
  }
</script>
