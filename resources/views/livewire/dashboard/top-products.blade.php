<div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl shadow-sm dark:shadow-black/40 dark:ring-1 dark:ring-white/5 h-auto transition-colors max-w-full overflow-hidden">
  {{-- Encabezado --}}
  <div class="flex items-center justify-between px-3 sm:px-4 py-3">
    <div class="min-w-0">
      <h4 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 truncate">Más vendidos</h4>
      <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">Últimos 30 días</p>
    </div>
    <a href="{{ route('products.index') }}"
       class="text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:underline
              focus:outline-none focus:ring-2 focus:ring-neutral-500 rounded-md
              focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900">
      Ver productos
    </a>
  </div>

  @php
    $rows = collect($rows ?? []);
    $maxQty = max(1, (int) $rows->max('qty'));
  @endphp

  <div class="px-3 sm:px-4 pb-4 space-y-3">
    @forelse($rows as $i => $r)
      @php
        $qty  = (int) ($r->qty ?? 0);
        $pct  = min(100, (int) round(($qty / $maxQty) * 100));
        $rank = $i + 1;
      @endphp

      <div class="group rounded-xl border border-transparent hover:border-neutral-200 dark:hover:border-neutral-700 hover:bg-neutral-50/60 dark:hover:bg-neutral-800/40 transition-colors">
        <div class="flex items-center gap-3 px-3 pt-3">
          {{-- Rank --}}
          <span class="shrink-0 inline-flex items-center justify-center w-7 h-7 rounded-lg
                       bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200 text-xs font-semibold">
            #{{ $rank }}
          </span>

          {{-- (Opcional) imagen si la hay --}}
          @if(!empty($r->image_url ?? null))
            <img src="{{ $r->image_url }}" alt="" class="w-9 h-9 rounded-lg object-cover border border-neutral-200 dark:border-neutral-700">
          @else
            <div class="w-9 h-9 rounded-lg grid place-items-center bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700">
              <svg class="w-4 h-4 text-neutral-500 dark:text-neutral-300" viewBox="0 0 24 24" fill="none">
                <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
              </svg>
            </div>
          @endif

          {{-- Nombre + cantidad --}}
          <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-2">
              <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate" title="{{ $r->name }}">
                {{ $r->name }}
              </p>
              <p class="shrink-0 text-sm font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums whitespace-nowrap">
                {{ number_format($qty, 0, ',', '.') }}
              </p>
            </div>

            {{-- Barra de progreso proporcional (monocromo) --}}
            <div class="mt-2 h-2 rounded-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
              <div class="h-2 rounded-full bg-neutral-600 dark:bg-neutral-300 transition-[width] duration-500"
                   style="width: {{ $pct }}%"></div>
            </div>
          </div>
        </div>

        {{-- Separador suave --}}
        <div class="px-3 pb-3 pt-1">
          <div class="h-px bg-neutral-100 dark:bg-neutral-800/70"></div>
        </div>
      </div>
    @empty
      <div class="px-6 py-10 text-center">
        <svg class="w-10 h-10 mx-auto text-neutral-300 dark:text-neutral-700" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
          <path d="M8 12h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-400">Sin ventas en los últimos 30 días.</p>
      </div>
    @endforelse
  </div>
</div>
