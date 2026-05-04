@extends('layouts.app')

@section('header')
<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
  <h1 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">{{ __('clients.title') }}</h1>

  <div class="flex items-center gap-2">
    {{-- Búsqueda compacta --}}
    <form method="GET" class="relative flex items-center gap-1.5">
      <div class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center">
        <svg class="w-3.5 h-3.5 text-neutral-400" viewBox="0 0 24 24" fill="none">
          <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
          <path d="M21 21l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <input name="q" value="{{ $q }}"
             placeholder="{{ __('clients.search_placeholder') }}"
             autocomplete="off"
             class="w-44 pl-8 pr-3 py-1.5 text-sm rounded-lg
                    border border-neutral-200 dark:border-neutral-700
                    bg-white dark:bg-neutral-900
                    text-neutral-700 dark:text-neutral-200
                    placeholder-neutral-400 dark:placeholder-neutral-500
                    focus:outline-none focus:ring-2 focus:ring-indigo-400/40 focus:border-indigo-400
                    focus:w-56 transition-all duration-200">
    </form>

    @if($q !== '')
      <a href="{{ route('clients.index') }}"
         title="{{ __('clients.clear') }}"
         class="p-1.5 rounded-lg text-neutral-400 transition-colors
                hover:text-neutral-600 dark:hover:text-neutral-300
                hover:bg-neutral-100 dark:hover:bg-neutral-800">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </a>
    @endif

    <a href="{{ route('clients.create') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium
              transition-all duration-150 active:scale-[0.98]
              bg-indigo-600 hover:bg-indigo-700 text-white">
      <x-svg-icon name="user-plus" size="4" />
      <span class="hidden sm:inline">{{ __('clients.new') }}</span>
    </a>
  </div>
</div>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">

  @if(session('ok'))
    <div class="mb-4 flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm
                bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/60
                text-emerald-800 dark:text-emerald-200">
      <x-svg-icon name="check" size="4" class="shrink-0 text-emerald-500" />
      {{ session('ok') }}
    </div>
  @endif

  @if($clients->count())

    {{-- Meta: resultados de búsqueda --}}
    @if($q !== '')
      <div class="mb-2.5 px-0.5">
        <span class="text-xs text-neutral-400 dark:text-neutral-500">
          {{ $clients->total() }} resultado{{ $clients->total() !== 1 ? 's' : '' }}
          para "<span class="font-medium text-neutral-600 dark:text-neutral-300">{{ $q }}</span>"
        </span>
      </div>
    @endif

    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">

          <thead>
            <tr class="border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/80 dark:bg-neutral-800/50">
              <th class="px-4 py-3 text-left text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('clients.col_name') }}</th>
              <th class="px-3 py-3 text-left text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('clients.col_email') }}</th>
              <th class="px-3 py-3 text-left text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('clients.col_phone') }}</th>
              <th class="px-3 py-3 text-left text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('clients.col_dni') }}</th>
              <th class="px-3 py-3 text-right text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('clients.col_balance') }}</th>
              <th class="px-4 py-3 w-20"></th>
            </tr>
          </thead>

          <tbody class="divide-y divide-neutral-50 dark:divide-neutral-800/60">
            @foreach($clients as $c)
              @php
                $words    = preg_split('/\s+/', trim($c->name));
                $initials = collect($words)->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                $balance  = (float) ($c->balance ?? 0);
                $balanceCls = $balance < 0
                  ? 'text-rose-600 dark:text-rose-400'
                  : ($balance > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-500 dark:text-neutral-400');
              @endphp
              <tr class="group transition-colors duration-100 hover:bg-neutral-50/60 dark:hover:bg-neutral-800/30">

                {{-- Nombre + avatar --}}
                <td class="px-4 py-3.5">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40
                                flex items-center justify-center shrink-0">
                      <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-300">{{ $initials }}</span>
                    </div>
                    <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $c->name }}</span>
                  </div>
                </td>

                <td class="px-3 py-3.5 text-neutral-600 dark:text-neutral-300 max-w-[200px] truncate">
                  {{ $c->email ?: '—' }}
                </td>

                <td class="px-3 py-3.5 text-neutral-600 dark:text-neutral-300 tabular-nums whitespace-nowrap">
                  {{ $c->phone ?: '—' }}
                </td>

                <td class="px-3 py-3.5 text-neutral-600 dark:text-neutral-300 tabular-nums">
                  {{ $c->document_number ?: '—' }}
                </td>

                <td class="px-3 py-3.5 text-right">
                  <span class="font-semibold tabular-nums {{ $balanceCls }}">
                    $ {{ number_format($balance, 2, ',', '.') }}
                  </span>
                </td>

                <td class="px-4 py-3.5">
                  <div class="flex items-center justify-end gap-0.5">
                    <a href="{{ route('clients.show', $c) }}" title="{{ __('clients.view') }}"
                       class="p-1.5 rounded-lg text-neutral-400 transition-colors
                              hover:text-indigo-600 hover:bg-indigo-50
                              dark:hover:text-indigo-400 dark:hover:bg-indigo-900/30">
                      <x-svg-icon name="eye" size="4" />
                    </a>
                    <a href="{{ route('clients.edit', $c) }}" title="{{ __('clients.edit') }}"
                       class="p-1.5 rounded-lg text-neutral-400 transition-colors
                              hover:text-neutral-700 hover:bg-neutral-100
                              dark:hover:text-neutral-200 dark:hover:bg-neutral-800">
                      <x-svg-icon name="edit" size="4" />
                    </a>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($clients->hasPages())
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-neutral-800">
          {{ $clients->withQueryString()->links() }}
        </div>
      @endif
    </div>

  @else
    <x-empty-state
      icon="user"
      :title="__('clients.empty_title')"
      :description="__('clients.empty_description')"
      :action-url="route('clients.create')"
      :action-text="__('clients.empty_action')"
      action-icon="user-plus"
    />
  @endif

</div>
@endsection
