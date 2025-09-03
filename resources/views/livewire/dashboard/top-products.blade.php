<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm dark:shadow-slate-900/40 dark:ring-1 dark:ring-white/5 h-auto transition-colors">
  {{-- Encabezado --}}
  <div class="flex items-center justify-between px-4 py-3">
    <div>
      <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Más vendidos</h4>
      <p class="text-xs text-slate-500 dark:text-slate-400">Últimos 30 días</p>
    </div>
    <a href="{{ route('products.index') }}"
       class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline
              focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md
              focus:ring-offset-2 dark:focus:ring-offset-slate-800">
      Ver productos
    </a>
  </div>

  @php
    $rows = collect($rows ?? []);
    $maxQty = max(1, (int) $rows->max('qty'));
  @endphp

  <div class="px-4 pb-4 space-y-3">
    @forelse($rows as $i => $r)
      @php
        $qty  = (int) ($r->qty ?? 0);
        $pct  = min(100, (int) round(($qty / $maxQty) * 100));
        $rank = $i + 1;
      @endphp

      <div class="group rounded-xl border border-transparent hover:border-slate-200 dark:hover:border-slate-600 hover:bg-slate-50/60 dark:hover:bg-slate-700/40 transition-colors">
        <div class="flex items-center gap-3 px-3 pt-3">
          {{-- Rank --}}
          <span class="shrink-0 inline-flex items-center justify-center w-7 h-7 rounded-lg
                       bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 text-xs font-semibold">
            #{{ $rank }}
          </span>

          {{-- (Opcional) imagen si la hay --}}
          @if(!empty($r->image_url ?? null))
            <img src="{{ $r->image_url }}" alt="" class="w-9 h-9 rounded-lg object-cover border border-slate-200 dark:border-slate-600">
          @else
            <div class="w-9 h-9 rounded-lg grid place-items-center bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600">
              <svg class="w-4 h-4 text-slate-500 dark:text-slate-300" viewBox="0 0 24 24" fill="none">
                <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
              </svg>
            </div>
          @endif

          {{-- Nombre + cantidad --}}
          <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-2">
              <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate" title="{{ $r->name }}">
                {{ $r->name }}
              </p>
              <p class="shrink-0 text-sm font-semibold text-slate-900 dark:text-slate-100 tabular-nums">
                {{ number_format($qty, 0, ',', '.') }}
              </p>
            </div>

            {{-- Barra de progreso proporcional --}}
            <div class="mt-2 h-2 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
              <div class="h-2 rounded-full bg-indigo-500 dark:bg-indigo-400 transition-[width] duration-500"
                   style="width: {{ $pct }}%"></div>
            </div>
          </div>
        </div>

        {{-- Separador suave sólo para accesibilidad visual --}}
        <div class="px-3 pb-3 pt-1">
          <div class="h-px bg-slate-100 dark:bg-slate-700/70"></div>
        </div>
      </div>
    @empty
      <div class="px-6 py-10 text-center">
        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
          <path d="M8 12h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">Sin ventas en los últimos 30 días.</p>
      </div>
    @endforelse
  </div>
</div>
