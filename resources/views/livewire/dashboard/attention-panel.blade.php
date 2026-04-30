{{-- resources/views/livewire/dashboard/attention-panel.blade.php --}}
<div wire:poll.60s.visible>
@if(count($items) > 0)
<div class="mx-4 sm:mx-5 lg:mx-6 mb-3">
  <div class="rounded-xl overflow-hidden
              bg-violet-50/50 dark:bg-neutral-900/65 backdrop-blur-sm
              shadow-[0_4px_20px_-2px_rgba(109,40,217,0.07),0_1px_4px_-1px_rgba(109,40,217,0.03)]
              dark:shadow-[0_4px_20px_-2px_rgba(0,0,0,0.4),0_1px_4px_-1px_rgba(0,0,0,0.2)]">

    {{-- Encabezado --}}
    <div class="px-4 py-2.5 border-b border-neutral-100 dark:border-neutral-800 flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
           class="w-3.5 h-3.5 text-neutral-400 dark:text-neutral-500">
        <path fill-rule="evenodd" d="M6.701 2.25c.577-1 2.02-1 2.598 0l5.196 9a1.5 1.5 0 0 1-1.299 2.25H2.804a1.5 1.5 0 0 1-1.3-2.25l5.197-9ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
      </svg>
      <span class="text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
        {{ __('dashboard.attention_label') }}
      </span>
      <span class="ml-auto text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 tabular-nums">
        {{ count($items) }}
      </span>
    </div>

    {{-- Items --}}
    <div class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
      @foreach($items as $item)
        <a href="{{ $item['route'] }}"
           class="flex items-center gap-3 px-4 py-3
                  hover:bg-neutral-50 dark:hover:bg-neutral-800/40
                  transition-colors group">

          {{-- Ícono con color según nivel --}}
          <div @class([
                'shrink-0 w-7 h-7 rounded-full flex items-center justify-center',
                'bg-rose-100 dark:bg-rose-900/25'   => $item['level'] === 'danger',
                'bg-amber-100 dark:bg-amber-900/25' => $item['level'] === 'warning',
                'bg-sky-100 dark:bg-sky-900/25'     => $item['level'] === 'info',
               ])>
            @if($item['icon'] === 'orders')
              {{-- Documento / pedido --}}
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                   @class(['w-3.5 h-3.5',
                     'text-rose-500 dark:text-rose-400'   => $item['level'] === 'danger',
                     'text-amber-500 dark:text-amber-400' => $item['level'] === 'warning',
                     'text-sky-500 dark:text-sky-400'     => $item['level'] === 'info',
                   ])>
                <path d="M5 3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5Zm1 5a.75.75 0 0 1 0-1.5h4a.75.75 0 0 1 0 1.5H6Zm0 2.5a.75.75 0 0 1 0-1.5h4a.75.75 0 0 1 0 1.5H6Z"/>
              </svg>

            @elseif($item['icon'] === 'calendar')
              {{-- Calendario / agendado --}}
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                   @class(['w-3.5 h-3.5',
                     'text-rose-500 dark:text-rose-400'   => $item['level'] === 'danger',
                     'text-amber-500 dark:text-amber-400' => $item['level'] === 'warning',
                     'text-sky-500 dark:text-sky-400'     => $item['level'] === 'info',
                   ])>
                <path d="M5.75 7.5a.75.75 0 1 0 0 1.5.75.75 0 0 0 0-1.5ZM5 10.25a.75.75 0 1 1 1.5 0 .75.75 0 0 1-1.5 0Zm5.75-2.75a.75.75 0 1 0 0 1.5.75.75 0 0 0 0-1.5ZM10 10.25a.75.75 0 1 1 1.5 0 .75.75 0 0 1-1.5 0ZM8 7.5a.75.75 0 1 0 0 1.5.75.75 0 0 0 0-1.5ZM7.25 10.25a.75.75 0 1 1 1.5 0 .75.75 0 0 1-1.5 0Z"/>
                <path fill-rule="evenodd" d="M4.75 1a.75.75 0 0 1 .75.75V3h5V1.75a.75.75 0 0 1 1.5 0V3h.25A2.25 2.25 0 0 1 14.5 5.25v7.5A2.25 2.25 0 0 1 12.25 15H3.75A2.25 2.25 0 0 1 1.5 12.75v-7.5A2.25 2.25 0 0 1 3.75 3H4V1.75A.75.75 0 0 1 4.75 1ZM3 6.5v6.25c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75V6.5H3Z" clip-rule="evenodd"/>
              </svg>

            @elseif($item['icon'] === 'stock')
              {{-- Caja / stock --}}
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                   @class(['w-3.5 h-3.5',
                     'text-rose-500 dark:text-rose-400'   => $item['level'] === 'danger',
                     'text-amber-500 dark:text-amber-400' => $item['level'] === 'warning',
                     'text-sky-500 dark:text-sky-400'     => $item['level'] === 'info',
                   ])>
                <path d="M7.557 2.066A.75.75 0 0 1 8 2c.18 0 .35.065.484.186l5.25 4.5A.75.75 0 0 1 14 7.5V13a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7.5a.75.75 0 0 1 .249-.564l5.25-4.5-.058.13.116-.5ZM6.5 10v3h3v-3h-3Z"/>
              </svg>

            @elseif($item['icon'] === 'payment')
              {{-- Billete / pago --}}
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                   @class(['w-3.5 h-3.5',
                     'text-rose-500 dark:text-rose-400'   => $item['level'] === 'danger',
                     'text-amber-500 dark:text-amber-400' => $item['level'] === 'warning',
                     'text-sky-500 dark:text-sky-400'     => $item['level'] === 'info',
                   ])>
                <path d="M2.5 3A1.5 1.5 0 0 0 1 4.5v.793c.026.009.051.02.076.032L7.674 8.51c.206.1.446.1.652 0l6.598-3.185A.755.755 0 0 1 15 5.293V4.5A1.5 1.5 0 0 0 13.5 3h-11Z"/>
                <path d="M15 6.954 8.978 9.86a2.25 2.25 0 0 1-1.956 0L1 6.954V11.5A1.5 1.5 0 0 0 2.5 13h11a1.5 1.5 0 0 0 1.5-1.5V6.954Z"/>
              </svg>

            @elseif($item['icon'] === 'booking')
              {{-- Llave / reserva --}}
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                   @class(['w-3.5 h-3.5',
                     'text-rose-500 dark:text-rose-400'   => $item['level'] === 'danger',
                     'text-amber-500 dark:text-amber-400' => $item['level'] === 'warning',
                     'text-sky-500 dark:text-sky-400'     => $item['level'] === 'info',
                   ])>
                <path fill-rule="evenodd" d="M11 5a3 3 0 1 0-2.977 3.354L.5 14.854l.646.646L2 14.646V13h2v-2h2V9h2l.646-.646A3 3 0 0 0 11 5ZM9.5 5a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Z" clip-rule="evenodd"/>
              </svg>
            @endif
          </div>

          {{-- Texto --}}
          <span class="text-sm text-neutral-700 dark:text-neutral-300 flex-1 min-w-0 truncate">
            {{ $item['label'] }}
          </span>

          {{-- Flecha --}}
          <span class="shrink-0 text-xs text-neutral-400 dark:text-neutral-600
                       group-hover:text-neutral-600 dark:group-hover:text-neutral-400
                       transition-colors">
            {{ __('dashboard.see_link') }}
          </span>
        </a>
      @endforeach
    </div>

  </div>
</div>
@endif
</div>
