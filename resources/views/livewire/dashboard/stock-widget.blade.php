<div wire:poll.10s
     class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">

  {{-- Header minimal --}}
  <div class="px-5 py-4 border-b border-slate-200/60 dark:border-slate-800 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800">
        {{-- ícono caja simple --}}
        <svg viewBox="0 0 24 24" class="w-5 h-5 text-slate-700 dark:text-slate-300" fill="currentColor" aria-hidden="true">
          <path d="M21 8.5l-9-5-9 5V17l9 5 9-5V8.5zM12 5.15L18.74 9 12 12.85 5.26 9 12 5.15zM6 10.73l5 2.89v5.65l-5-2.78v-5.76zm12 0v5.76l-5 2.78v-5.65l5-2.89z"/>
        </svg>
      </span>
      <div>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Stock</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">
          {{ $hasMin ? 'Comparado con mínimo' : 'Sin mínimo definido' }}
        </p>
      </div>
    </div>

    {{-- En md/lg, mini KPIs laterales --}}
    @if(!$this->isSmall())
      <div class="hidden sm:flex items-center gap-5">
        <div class="text-right">
          <div class="text-[11px] text-slate-500 dark:text-slate-400">Productos</div>
          <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">
            {{ number_format($totals->total_products ?? 0) }}
          </div>
        </div>
        <div class="text-right">
          <div class="text-[11px] text-slate-500 dark:text-slate-400">Bajo stock</div>
          <div class="text-sm font-semibold {{ ($totals->low_count ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
            {{ number_format($totals->low_count ?? 0) }}
          </div>
        </div>
      </div>
    @endif
  </div>

  {{-- Body --}}
  <div class="px-5 py-5">

    {{-- Modo compacto (sm): número grande del stock total --}}
    @if($this->isSmall())
      <div class="flex items-baseline justify-center">
        <div class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white tabular-nums">
          {{ number_format($totals->total_units ?? 0) }}
        </div>
        <div class="ml-2 text-sm text-slate-500 dark:text-slate-400">unidades</div>
      </div>
    @endif

    {{-- Modo md: total grande + KPIs simples en fila (sin lista) --}}
    @if($this->isMedium())
      <div class="grid grid-cols-3 gap-4">
        <div class="col-span-3">
          <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-1">Unidades totales</div>
          <div class="text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
            {{ number_format($totals->total_units ?? 0) }}
          </div>
        </div>
        <div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-1">Productos</div>
          <div class="text-lg font-semibold text-slate-900 dark:text-white">
            {{ number_format($totals->total_products ?? 0) }}
          </div>
        </div>
        <div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-1">Bajo stock</div>
          <div class="text-lg font-semibold {{ ($totals->low_count ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
            {{ number_format($totals->low_count ?? 0) }}
          </div>
        </div>
      </div>
    @endif

    {{-- Modo lg: total + listado por producto (top bajos) --}}
    @if($this->isLarge())
      <div class="mb-4">
        <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-1">Unidades totales</div>
        <div class="text-3xl font-bold text-slate-900 dark:text-white tabular-nums">
          {{ number_format($totals->total_units ?? 0) }}
        </div>
      </div>

      <div class="space-y-2">
        @forelse($items as $p)
          @php
            $min   = $hasMin ? (int)($p->min_stock ?? 0) : 0;
            $isLow = $hasMin ? ((int)$p->stock <= $min) : ((int)$p->stock <= 0);
            $ratio = $hasMin
                ? max(0, min(100, $min > 0 ? round(($p->stock / $min) * 100) : 100))
                : ((int)$p->stock > 0 ? 100 : 0);
          @endphp

          <div class="flex items-center gap-3">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <span class="truncate font-medium text-slate-800 dark:text-slate-100">{{ $p->name }}</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 truncate hidden sm:inline">SKU: {{ $p->sku }}</span>
              </div>
              <div class="mt-1 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $isLow ? 'bg-rose-500 dark:bg-rose-400' : 'bg-emerald-500 dark:bg-emerald-400' }}"
                     style="width: {{ $ratio }}%"></div>
              </div>
            </div>
            <div class="text-right w-20">
              <div class="text-sm font-semibold {{ $isLow ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-100' }}">
                {{ (int)$p->stock }}
              </div>
              @if($hasMin)
                <div class="text-[11px] text-slate-500 dark:text-slate-400">min {{ (int)$p->min_stock }}</div>
              @endif
            </div>
          </div>
        @empty
          <div class="text-sm text-slate-500 dark:text-slate-400">Sin productos.</div>
        @endforelse
      </div>
    @endif
  </div>
</div>
