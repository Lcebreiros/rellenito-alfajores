<div class="card bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm dark:shadow-slate-900/40 dark:ring-1 dark:ring-white/5 transition-colors">
  {{-- Encabezado --}}
  <div class="flex items-center justify-between px-4 py-3">
    <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Últimos pedidos</h4>
    <a href="{{ route('orders.index') }}"
       class="text-xs font-medium text-indigo-600 dark:text-slate-200 hover:underline
              focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-neutral-500 rounded-md
              focus:ring-offset-2 dark:focus:ring-offset-slate-800">
      Ver todos
    </a>
  </div>

  @php
    $statusMap = [
      'completed' => ['label'=>'Completado','classes'=>'bg-emerald-100 text-emerald-700 border-emerald-200/70 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800/50'],
      'paid'      => ['label'=>'Pagado','classes'=>'bg-teal-100 text-teal-700 border-teal-200/70 dark:bg-teal-900/30 dark:text-teal-300 dark:border-teal-800/50'],
      'pending'   => ['label'=>'Pendiente','classes'=>'bg-amber-100 text-amber-800 border-amber-200/70 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800/50'],
      'draft'     => ['label'=>'Borrador','classes'=>'bg-slate-100 text-slate-700 border-slate-200/70 dark:bg-slate-700/50 dark:text-slate-300 dark:border-slate-600/70'],
      'canceled'  => ['label'=>'Cancelado','classes'=>'bg-rose-100 text-rose-700 border-rose-200/70 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800/50'],
      'fulfilled' => ['label'=>'Entregado','classes'=>'bg-indigo-100 text-indigo-700 border-indigo-200/70 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800/50'],
    ];
  @endphp

  {{-- Lista --}}
  <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
    @forelse($orders as $o)
      @php
        $st = $statusMap[$o->status] ?? ['label'=>ucfirst($o->status ?? '—'),'classes'=>'bg-slate-100 text-slate-700 border-slate-200/70 dark:bg-slate-700/50 dark:text-slate-300 dark:border-slate-600/70'];
      @endphp

      <a href="{{ route('orders.show', $o) }}"
         class="block group focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-neutral-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 rounded-md">
        <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
          <div class="flex items-start gap-3">
            {{-- Icono/placeholder --}}
            <div class="mt-0.5 shrink-0 w-10 h-10 grid place-items-center rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-700">
              <svg class="w-5 h-5 text-slate-500 dark:text-slate-300" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="4" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M7 8h10M7 12h10M7 16h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>

            {{-- Centro: ID, fecha y cliente --}}
            <div class="min-w-0 flex-1">
              <div class="flex items-center justify-between gap-2">
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">
                  Pedido #{{ $o->id }}
                </p>
                {{-- Monto (tabular para alinear) --}}
                <p class="text-sm font-bold text-slate-900 dark:text-slate-100 tabular-nums">
                  $ {{ number_format($o->total ?? 0, 2, ',', '.') }}
                </p>
              </div>

              <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                <span class="text-slate-500 dark:text-slate-400">{{ $o->created_at?->diffForHumans() }}</span>
                <span class="hidden sm:inline text-slate-300 dark:text-slate-600">•</span>
                @if($o->customer_name ?? false)
                  <span class="text-slate-600 dark:text-slate-300 truncate">{{ $o->customer_name }}</span>
                @endif
              </div>
            </div>

            {{-- Estado como pill --}}
            <span class="shrink-0 inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-medium {{ $st['classes'] }}">
              {{ $st['label'] }}
            </span>
          </div>
        </div>
      </a>
    @empty
      {{-- Estado vacío elegante --}}
      <div class="px-6 py-10 text-center">
        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
          <path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">Sin pedidos aún.</p>
        <a href="{{ route('orders.create') }}"
           class="mt-3 inline-flex items-center gap-2 text-xs font-medium text-indigo-600 dark:text-slate-200 hover:underline
                  focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-neutral-500 rounded-md
                  focus:ring-offset-2 dark:focus:ring-offset-slate-800">
          Crear pedido
        </a>
      </div>
    @endforelse
  </div>
</div>
