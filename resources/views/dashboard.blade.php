{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('header')
  @php
    $hour = now()->hour;
    $greeting = $hour < 12 ? __('dashboard.greeting_morning') : ($hour < 19 ? __('dashboard.greeting_afternoon') : __('dashboard.greeting_evening'));
  @endphp
  <div>
    <h1 class="text-xl font-semibold text-gray-800 dark:text-neutral-100 leading-tight">
      {{ $greeting }}, {{ Auth::user()->name }}
    </h1>
    <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5 capitalize">
      {{ now()->isoFormat('dddd, D [de] MMMM') }}
    </p>
  </div>
@endsection

@section('header_actions')
  <div class="flex items-center gap-1.5 flex-wrap">

    {{-- Nuevo producto --}}
    <a href="{{ route('products.create') }}"
       class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-[13px] font-medium
              transition-all whitespace-nowrap
              bg-violet-50 hover:bg-violet-100 dark:bg-violet-950/40 dark:hover:bg-violet-950/60
              text-violet-700 dark:text-violet-300">
      <span class="w-5 h-5 rounded-md bg-violet-200/70 dark:bg-violet-500/20 flex items-center justify-center shrink-0 transition-colors group-hover:bg-violet-300/60 dark:group-hover:bg-violet-500/30">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3 h-3 text-violet-600 dark:text-violet-400">
          <path d="M7.557 2.066A.75.75 0 0 1 8 2c.18 0 .35.065.484.186l5.25 4.5A.75.75 0 0 1 14 7.5V13a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7.5a.75.75 0 0 1 .249-.564l5.25-4.5-.058.13.116-.5ZM6.5 10v3h3v-3h-3Z"/>
        </svg>
      </span>
      <span class="hidden sm:inline">{{ __('dashboard.new_product_btn') }}</span>
      <span class="sm:hidden">{{ __('dashboard.new_product_btn_short') }}</span>
    </a>

    {{-- Nuevo servicio --}}
    <a href="{{ route('services.create') }}"
       class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-[13px] font-medium
              transition-all whitespace-nowrap
              bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/40 dark:hover:bg-sky-950/60
              text-sky-700 dark:text-sky-300">
      <span class="w-5 h-5 rounded-md bg-sky-200/70 dark:bg-sky-500/20 flex items-center justify-center shrink-0 transition-colors group-hover:bg-sky-300/60 dark:group-hover:bg-sky-500/30">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3 h-3 text-sky-600 dark:text-sky-400">
          <path fill-rule="evenodd" d="M6.955 1.45A.75.75 0 0 1 7.68 1h.642a.75.75 0 0 1 .727.563l.258 1.036c.2.008.388.022.566.04l.802-.802a.75.75 0 0 1 1.007-.05l.464.464a.75.75 0 0 1 .05 1.007l-.802.802c.018.178.032.366.04.566l1.036.258a.75.75 0 0 1 .563.727v.642a.75.75 0 0 1-.563.727l-1.036.258a8.245 8.245 0 0 1-.04.566l.802.802a.75.75 0 0 1-.05 1.007l-.464.464a.75.75 0 0 1-1.007-.05l-.802-.802a8.243 8.243 0 0 1-.566.04l-.258 1.036a.75.75 0 0 1-.727.563H7.68a.75.75 0 0 1-.727-.563l-.258-1.036a8.245 8.245 0 0 1-.566-.04l-.802.802a.75.75 0 0 1-1.007.05l-.464-.464a.75.75 0 0 1-.05-1.007l.802-.802a8.243 8.243 0 0 1-.04-.566L3.532 8.73A.75.75 0 0 1 2.969 8V7.358a.75.75 0 0 1 .563-.727l1.036-.258c.008-.2.022-.388.04-.566l-.802-.802a.75.75 0 0 1 .05-1.007l.464-.464a.75.75 0 0 1 1.007.05l.802.802c.178-.018.366-.032.566-.04L6.955 1.45ZM8 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" clip-rule="evenodd"/>
        </svg>
      </span>
      <span class="hidden sm:inline">{{ __('dashboard.new_service_btn') }}</span>
      <span class="sm:hidden">{{ __('dashboard.new_service_btn_short') }}</span>
    </a>

    {{-- Compra de insumo --}}
    <a href="{{ route('expenses.supplies') }}"
       class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-[13px] font-medium
              transition-all whitespace-nowrap
              bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 dark:hover:bg-amber-950/60
              text-amber-700 dark:text-amber-300">
      <span class="w-5 h-5 rounded-md bg-amber-200/70 dark:bg-amber-500/20 flex items-center justify-center shrink-0 transition-colors group-hover:bg-amber-300/60 dark:group-hover:bg-amber-500/30">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3 h-3 text-amber-600 dark:text-amber-400">
          <path d="M1.75 1.5a.75.75 0 0 0 0 1.5h.64l1.31 6.548a1.5 1.5 0 1 0 1.964.402l-.282-1.41h5.235l-.283 1.41a1.5 1.5 0 1 0 1.965-.402L13.61 3H14.25a.75.75 0 0 0 0-1.5H1.75ZM5.006 9.54 3.913 4h8.174L11 9.54H5.006Z"/>
        </svg>
      </span>
      <span class="hidden sm:inline">{{ __('dashboard.new_supply_btn') }}</span>
      <span class="sm:hidden">{{ __('dashboard.new_supply_btn_short') }}</span>
    </a>

    {{-- Nuevo cliente --}}
    <a href="{{ route('clients.create') }}"
       class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-[13px] font-medium
              transition-all whitespace-nowrap
              bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-950/60
              text-emerald-700 dark:text-emerald-300">
      <span class="w-5 h-5 rounded-md bg-emerald-200/70 dark:bg-emerald-500/20 flex items-center justify-center shrink-0 transition-colors group-hover:bg-emerald-300/60 dark:group-hover:bg-emerald-500/30">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3 h-3 text-emerald-600 dark:text-emerald-400">
          <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM12.735 14c.618 0 1.093-.561.872-1.139a6.002 6.002 0 0 0-11.215 0c-.22.578.254 1.139.872 1.139h9.47Z"/>
        </svg>
      </span>
      <span class="hidden sm:inline">{{ __('dashboard.new_client_btn') }}</span>
      <span class="sm:hidden">{{ __('dashboard.new_client_btn_short') }}</span>
    </a>

    {{-- Separador --}}
    <div class="w-px h-5 bg-neutral-200 dark:bg-neutral-700 mx-0.5"></div>

    {{-- Calculadora --}}
    <a href="{{ route('calculator.show') }}"
       class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-[13px] font-medium
              transition-all whitespace-nowrap
              bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700
              text-neutral-600 dark:text-neutral-400">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3.5 h-3.5 text-neutral-400 dark:text-neutral-500 shrink-0">
        <path fill-rule="evenodd" d="M4 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H4Zm1 3.75A.75.75 0 0 1 5.75 5h4.5a.75.75 0 0 1 0 1.5h-4.5A.75.75 0 0 1 5 5.75ZM5.75 9a.75.75 0 0 0 0 1.5h.5a.75.75 0 0 0 0-1.5h-.5Zm2.25.75A.75.75 0 0 1 8.75 9h.5a.75.75 0 0 1 0 1.5h-.5A.75.75 0 0 1 8 9.75Zm2.25-2.25a.75.75 0 0 0 0 1.5h.5a.75.75 0 0 0 0-1.5h-.5Zm.75 3A.75.75 0 0 1 11.75 9h.5a.75.75 0 0 1 0 1.5h-.5A.75.75 0 0 1 11 10.5ZM5.75 7.5a.75.75 0 0 0 0 1.5h.5a.75.75 0 0 0 0-1.5h-.5Zm2.25.75A.75.75 0 0 1 8.75 7.5h.5a.75.75 0 0 1 0 1.5h-.5A.75.75 0 0 1 8 8.25Z" clip-rule="evenodd"/>
      </svg>
      <span class="hidden sm:inline">{{ __('dashboard.calculator_btn') }}</span>
    </a>

  </div>
@endsection

@section('content')
    <livewire:dashboard />
@endsection
