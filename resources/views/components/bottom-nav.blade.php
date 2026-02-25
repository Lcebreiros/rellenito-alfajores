@php
use Illuminate\Support\Facades\Route;

/** Helper: primera ruta disponible o fallback */
$safeRoute = function (array $names, string $fallback = '#') {
    foreach ($names as $n) {
        if (Route::has($n)) return route($n);
    }
    return $fallback;
};

/** Helper: patrón activo */
$isActive = fn (string $pattern) => request()->routeIs($pattern);

/** URLs seguras */
$ordersUrl   = Route::has('orders.index') ? route('orders.index') : route('orders.create');
$settingsUrl = $safeRoute(['settings', 'settings.index', 'profile.show'], '#');
$profileUrl  = $safeRoute(['profile.show'], '#');

/** Clases utilitarias */
$bar = 'fixed bottom-0 inset-x-0 z-50 md:hidden
        border-t border-neutral-200/40 dark:border-neutral-800/40
        bg-white/90 dark:bg-neutral-950/90 backdrop-blur-xl
        supports-[backdrop-filter]:bg-white/80 supports-[backdrop-filter]:dark:bg-neutral-950/80';
$wrap = 'mx-auto max-w-3xl px-2 sm:px-3 relative';
$grid = 'grid grid-cols-6 items-center h-16 gap-1 relative z-10';
$pillLabel = 'text-[10px] sm:text-[11px] font-semibold leading-tight tracking-tight text-center whitespace-nowrap min-w-0';

/** Inicializar $activeIndex de forma segura */
$activeIndex = 0; // valor por defecto
if ($isActive('orders.create'))         $activeIndex = 1;
elseif ($isActive('orders.index'))      $activeIndex = 2;
elseif ($isActive('products.*'))        $activeIndex = 3;
elseif ($isActive('stock.index'))       $activeIndex = 4;
elseif ($isActive('costing.calculator') || $isActive('costs.*')) $activeIndex = 5;
@endphp


<style>
/* ---------- Reset / performance ---------- */
.nav-icon,
.nav-tab,
.nav-underline {
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  will-change: transform, opacity;
}

/* ---------- Contenedor inferior (mantengo tu paleta) ---------- */
.nav-bar-bg {
  border-top: 1px solid rgba(0,0,0,0.06);
  background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(255,255,255,0.90));
  backdrop-filter: blur(6px);
}
.dark .nav-bar-bg {
  border-top-color: rgba(255,255,255,0.04);
  background: linear-gradient(180deg, rgba(15,15,17,0.92), rgba(10,10,12,0.94));
}

/* ---------- Each tab ---------- */
.nav-tab {
  -webkit-tap-highlight-color: transparent;
  transition: transform 150ms cubic-bezier(.2,.9,.3,1), opacity 140ms;
  touch-action: manipulation;
}

/* reduce hover/scale on small screens */
@media (hover: hover) and (pointer: fine) {
  .nav-tab:hover { transform: translateY(-3px) scale(1.03); }
}

/* Icon look */
.nav-icon {
  transition: transform 190ms cubic-bezier(.2,.9,.3,1), filter 180ms, opacity 180ms;
  display: block;
}

/* Active text */
.nav-text {
  transition: color 160ms;
  color: var(--nav-text, #6b7280); /* neutral-500 */
  font-weight: 600;
}
.dark .nav-text { color: var(--nav-text-dark, #9ca3af); }

/* cuando está activo */
.nav-active .nav-icon { transform: scale(1.12); filter: drop-shadow(0 6px 10px rgba(0,0,0,0.08)); opacity: 1; }
.nav-active .nav-text { color: var(--nav-text-active, #111827); }
.dark .nav-active .nav-text { color: #e6edf3; }

/* ---------- Underline (simple + sin parpadeo) ---------- */
/* Es un elemento dentro de cada tab, se escala en X para aparecer/desaparecer */
.nav-underline {
  display: block;
  margin: 6px auto 0;
  height: 3px;
  width: 42%;
  border-radius: 999px;
  background: linear-gradient(90deg, #06b6d4, #7c3aed); /* cyan -> purple */
  transform-origin: center;
  transform: scaleX(0);
  opacity: 0;
  transition: transform 320ms cubic-bezier(.22,1,.36,1), opacity 220ms;
}

/* colors para modo oscuro */
.dark .nav-underline { background: linear-gradient(90deg, #22d3ee, #a78bfa); }

/* cuando está activo -> scaleX(1) */
.nav-active .nav-underline {
  transform: scaleX(1);
  opacity: 1;
}

/* Touch / active feedback */
.nav-tab:active { transform: scale(0.96); }

/* Safe area (iOS) */
.safe-area-gradient {
  background: linear-gradient(to top, rgba(250,250,250,0.92), transparent);
}
.dark .safe-area-gradient {
  background: linear-gradient(to top, rgba(10,10,12,0.92), transparent);
}

/* Small tweak: reduce underline width on very small devices */
@media (max-width: 380px) {
  .nav-underline { width: 34%; height: 2.5px; }
}
</style>

<nav
  x-data="() => ({
    moreOpen: false,
    activeIndex: {{ $activeIndex }},
    init() {
      // nos apoyamos únicamente en el índice y en clases; no movemos DOM globalmente
      // Escucha cambios de rutas SPA (Livewire/Turbo)
      window.addEventListener('livewire:navigated', () => {
        // recalcula por si activeIndex fue cambiado por servidor
        setTimeout(() => {
          // si tu backend actualiza la variable $activeIndex en siguiente render,
          // Alpine la recibirá en el próximo tick; no forzamos DOM difícilmente.
        }, 60);
      });

      // Resize: solo necesitamos forzar repaint si hay cambio de ancho (éste es muy ligero)
      let rt;
      const onResize = () => {
        clearTimeout(rt);
        rt = setTimeout(() => {
          // Forzamos un pequeño reflow para evitar estados visuales incorrectos en algunos navegadores
          document.querySelectorAll('.nav-tab').forEach(el => el.getBoundingClientRect());
        }, 120);
      };
      try {
        const ro = new ResizeObserver(onResize);
        ro.observe(document.body);
      } catch (e) {
        window.addEventListener('resize', onResize);
      }
    },
    setActive(i) {
      this.activeIndex = i;
      // small visual confirmation on click for accesibilidad (no bloquea navegaciones)
      const el = document.querySelector(`[data-tab-index='${i}']`);
      if (el) {
        el.classList.add('nav-clicked');
        setTimeout(() => el.classList.remove('nav-clicked'), 220);
      }
    }
  })"
  class="{{ $bar }} nav-bar-bg"
  aria-label="Navegación inferior"
>
  <div class="{{ $wrap }}">
    <div class="{{ $grid }} px-1" role="tablist" aria-orientation="horizontal">
      {{-- Dashboard --}}
      <a href="{{ route('dashboard') }}" wire:navigate data-turbo="false"
         @click.prevent="setActive(0); $nextTick(()=> $el.contains(event.target) ? (window.location = '{{ route('dashboard') }}') : null)"
         data-tab-index="0"
         :class="activeIndex === 0 ? 'nav-active' : ''"
         class="nav-tab group flex justify-center touch-manipulation"
         role="tab"
         aria-current="{{ $isActive('dashboard') ? 'page' : 'false' }}">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px]">
          <div class="relative w-6 h-6">
            <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard"
                 class="nav-icon w-6 h-6 object-contain {{ $isActive('dashboard') ? '' : 'opacity-70 group-hover:opacity-95' }}">
            @if($isActive('dashboard'))
              <div class="absolute inset-0 rounded-full pointer-events-none"></div>
            @endif
          </div>
          <span class="nav-text text-[10px] sm:text-[11px] font-semibold leading-tight tracking-tight text-center whitespace-nowrap min-w-0">
            Dashboard
          </span>
          <span class="nav-underline" aria-hidden="true"></span>
        </div>
      </a>

      {{-- Crear venta --}}
      <a href="{{ route('orders.create') }}" wire:navigate data-turbo="false"
         @click.prevent="setActive(1); $nextTick(()=> window.location='{{ route('orders.create') }}')"
         data-tab-index="1"
         :class="activeIndex === 1 ? 'nav-active' : ''"
         class="nav-tab group flex justify-center touch-manipulation"
         role="tab">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px]">
          <div class="relative w-6 h-6">
            <img src="{{ asset('images/crear-venta.png') }}" alt="Crear venta"
                 class="nav-icon w-6 h-6 object-contain {{ $isActive('orders.create') ? '' : 'opacity-70 group-hover:opacity-95' }}">
          </div>
          <span class="nav-text text-[10px] sm:text-[11px] font-semibold leading-tight tracking-tight text-center whitespace-nowrap min-w-0">Crear</span>
          <span class="nav-underline" aria-hidden="true"></span>
        </div>
      </a>

      {{-- Ventas --}}
      <a href="{{ $ordersUrl }}" wire:navigate data-turbo="false"
         @click.prevent="setActive(2); $nextTick(()=> window.location='{{ $ordersUrl }}')"
         data-tab-index="2"
         :class="activeIndex === 2 ? 'nav-active' : ''"
         class="nav-tab group flex justify-center touch-manipulation"
         role="tab">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px]">
          <div class="relative w-6 h-6">
            <img src="{{ asset('images/ventas.png') }}" alt="Ventas"
                 class="nav-icon w-6 h-6 object-contain {{ $isActive('orders.index') ? '' : 'opacity-70 group-hover:opacity-95' }}">
          </div>
          <span class="nav-text text-[10px] sm:text-[11px] font-semibold leading-tight tracking-tight text-center whitespace-nowrap min-w-0">Ventas</span>
          <span class="nav-underline" aria-hidden="true"></span>
        </div>
      </a>

      {{-- Productos --}}
      <a href="{{ route('products.index') }}" wire:navigate data-turbo="false"
         @click.prevent="setActive(3); $nextTick(()=> window.location='{{ route('products.index') }}')"
         data-tab-index="3"
         :class="activeIndex === 3 ? 'nav-active' : ''"
         class="nav-tab group flex justify-center touch-manipulation"
         role="tab">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px]">
          <div class="relative w-6 h-6">
            <img src="{{ asset('images/productos.png') }}" alt="Productos"
                 class="nav-icon w-6 h-6 object-contain {{ $isActive('products.*') ? '' : 'opacity-70 group-hover:opacity-95' }}">
          </div>
          <span class="nav-text text-[10px] sm:text-[11px] font-semibold leading-tight tracking-tight text-center whitespace-nowrap min-w-0">Productos</span>
          <span class="nav-underline" aria-hidden="true"></span>
        </div>
      </a>

      {{-- Stock --}}
      <a href="{{ route('stock.index') }}#stock" wire:navigate data-turbo="false"
         @click.prevent="setActive(4); $nextTick(()=> window.location='{{ route('stock.index') }}#stock')"
         data-tab-index="4"
         :class="activeIndex === 4 ? 'nav-active' : ''"
         class="nav-tab group flex justify-center touch-manipulation"
         role="tab">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px]">
          <div class="relative w-6 h-6">
            <img src="{{ asset('images/stock.png') }}" alt="Stock"
                 class="nav-icon w-6 h-6 object-contain {{ $isActive('stock.index') ? '' : 'opacity-70 group-hover:opacity-95' }}">
          </div>
          <span class="nav-text text-[10px] sm:text-[11px] font-semibold leading-tight tracking-tight text-center whitespace-nowrap min-w-0">Stock</span>
          <span class="nav-underline" aria-hidden="true"></span>
        </div>
      </a>

      {{-- Costos --}}
      <a href="{{ route('costing.calculator') }}" wire:navigate data-turbo="false"
         @click.prevent="setActive(5); $nextTick(()=> window.location='{{ route('costing.calculator') }}')"
         data-tab-index="5"
         :class="activeIndex === 5 ? 'nav-active' : ''"
         class="nav-tab group flex justify-center touch-manipulation"
         role="tab">
        <div class="inline-flex flex-col items-center justify-center gap-1.5 px-2 py-2 rounded-2xl min-w-0 min-h-[52px]">
          <div class="relative w-6 h-6">
            <img src="{{ asset('images/calcular-costos.png') }}" alt="Calcular costos"
                 class="nav-icon w-6 h-6 object-contain {{ ($isActive('costing.calculator')||$isActive('costs.*')) ? '' : 'opacity-70 group-hover:opacity-95' }}">
          </div>
          <span class="nav-text text-[10px] sm:text-[11px] font-semibold leading-tight tracking-tight text-center whitespace-nowrap min-w-0">Costos</span>
          <span class="nav-underline" aria-hidden="true"></span>
        </div>
      </a>
    </div>
  </div>

  

  {{-- Safe area iOS --}}
  <div class="h-[env(safe-area-inset-bottom)] min-h-[8px] safe-area-gradient"></div>
</nav>
