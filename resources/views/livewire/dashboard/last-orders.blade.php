<div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl shadow-sm shadow-black/5 dark:shadow-black/40 dark:ring-1 dark:ring-white/5 transition-colors max-w-full overflow-hidden">

  {{-- Encabezado --}}
  <div class="flex items-center justify-between px-3 sm:px-4 py-3">
    <h4 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Últimos pedidos</h4>

    <a href="{{ route('orders.index') }}"
       class="text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:underline
              focus:outline-none focus:ring-2 focus:ring-neutral-500 dark:focus:ring-neutral-500 rounded-md
              focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900">
      Ver todos
    </a>
  </div>

  @php
    // Paleta dark neutra (gris/negro). Mantenemos color en estados de negocio,
    // pero podés pasarlos también a grises si querés full monocromo.
    $statusMap = [
      'completed' => ['label'=>'Completado','classes'=>'bg-emerald-100 text-emerald-700 border-emerald-200/70 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800/50'],
      'paid'      => ['label'=>'Pagado','classes'=>'bg-teal-100 text-teal-700 border-teal-200/70 dark:bg-teal-900/30 dark:text-teal-300 dark:border-teal-800/50'],
      'pending'   => ['label'=>'Pendiente','classes'=>'bg-amber-100 text-amber-800 border-amber-200/70 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800/50'],
      'draft'     => ['label'=>'Borrador','classes'=>'bg-neutral-100 text-neutral-700 border-neutral-200/70 dark:bg-neutral-700/50 dark:text-neutral-300 dark:border-neutral-600/70'],
      'canceled'  => ['label'=>'Cancelado','classes'=>'bg-rose-100 text-rose-700 border-rose-200/70 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800/50'],
      'fulfilled' => ['label'=>'Entregado','classes'=>'bg-neutral-100 text-neutral-700 border-neutral-200/70 dark:bg-neutral-700/50 dark:text-neutral-300 dark:border-neutral-600/70'],
    ];
  @endphp

  {{-- Lista --}}
  <div class="divide-y divide-neutral-100 dark:divide-neutral-800/70">
    @forelse($orders as $o)
      @php
        $st = $statusMap[$o->status] ?? ['label'=>ucfirst($o->status ?? '—'),'classes'=>'bg-neutral-100 text-neutral-700 border-neutral-200/70 dark:bg-neutral-700/50 dark:text-neutral-300 dark:border-neutral-600/70'];
      @endphp

      <a href="{{ route('orders.show', $o) }}"
         class="block group focus:outline-none focus:ring-2 focus:ring-neutral-500 rounded-md
                focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900">
        <div class="px-3 sm:px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
          <div class="flex flex-wrap items-start gap-3">

            {{-- Icono/placeholder --}}
            <div class="mt-0.5 shrink-0 w-10 h-10 grid place-items-center rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-800">
              <svg class="w-5 h-5 text-neutral-500 dark:text-neutral-300" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="4" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M7 8h10M7 12h10M7 16h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>

            {{-- Centro: ID, fecha y cliente --}}
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                  Pedido #{{ $o->id }}
                </p>
                {{-- Monto (no se corta en mobile) --}}
                <p class="ml-auto text-sm font-bold text-neutral-900 dark:text-neutral-100 tabular-nums whitespace-nowrap">
                  $ {{ number_format($o->total ?? 0, 2, ',', '.') }}
                </p>
              </div>

              <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                <span class="text-neutral-500 dark:text-neutral-400">{{ $o->created_at?->diffForHumans() }}</span>
                <span class="hidden sm:inline text-neutral-300 dark:text-neutral-700">•</span>
                @if($o->customer_name ?? false)
                  <span class="text-neutral-600 dark:text-neutral-300 truncate">{{ $o->customer_name }}</span>
                @endif
              </div>
            </div>

            {{-- Estado como pill (salta abajo si no entra) --}}
            <span class="shrink-0 inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-medium {{ $st['classes'] }} 
                         ml-auto sm:ml-0 self-start">
              {{ $st['label'] }}
            </span>

          </div>
        </div>
      </a>
    @empty
      {{-- Estado vacío elegante --}}
      <div class="px-6 py-10 text-center">
        <svg class="w-10 h-10 mx-auto text-neutral-300 dark:text-neutral-700" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
          <path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-400">Sin pedidos aún.</p>
        <a href="{{ route('orders.create') }}"
           class="mt-3 inline-flex items-center gap-2 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:underline
                  focus:outline-none focus:ring-2 focus:ring-neutral-500 rounded-md
                  focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900">
          Crear pedido
        </a>
      </div>
    @endforelse
  </div>
</div>
