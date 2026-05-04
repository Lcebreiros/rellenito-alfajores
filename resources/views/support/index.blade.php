@extends('layouts.app')

@section('header')
<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
  <h1 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">{{ __('support.page_title') }}</h1>

  <button type="button" onclick="window.dispatchEvent(new Event('toggle-ticket-form'))"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium
                 transition-all duration-150 active:scale-[0.98]
                 bg-indigo-600 hover:bg-indigo-700 text-white">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
      <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
    {{ __('support.new_ticket_btn') }}
  </button>
</div>
@endsection

@section('content')
@php
  $statusMap = [
    'nuevo'       => ['label' => __('support.status_nuevo'),       'cls' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',          'bar' => 'bg-amber-400 dark:bg-amber-500'],
    'en_proceso'  => ['label' => __('support.status_en_proceso'),  'cls' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',              'bar' => 'bg-blue-400 dark:bg-blue-500'],
    'solucionado' => ['label' => __('support.status_solucionado'), 'cls' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',  'bar' => 'bg-emerald-400 dark:bg-emerald-500'],
  ];
  $typeMap = [
    'consulta'   => ['label' => __('support.type_consulta'),   'cls' => 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300'],
    'problema'   => ['label' => __('support.type_problema'),   'cls' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300'],
    'sugerencia' => ['label' => __('support.type_sugerencia'), 'cls' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'],
  ];
@endphp

<div class="max-w-3xl mx-auto px-3 sm:px-6"
     x-data="{ showForm: false }"
     @toggle-ticket-form.window="showForm = !showForm"
     @keydown.escape.window="showForm = false">

  {{-- Mensaje de éxito --}}
  @if(session('ok'))
    <div class="mb-4 flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm
                bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/60
                text-emerald-800 dark:text-emerald-200">
      <svg class="w-4 h-4 shrink-0 text-emerald-500" viewBox="0 0 24 24" fill="none">
        <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      {{ session('ok') }}
    </div>
  @endif

  {{-- Formulario nuevo ticket --}}
  <div x-show="showForm"
       x-cloak
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 -translate-y-2"
       x-transition:enter-end="opacity-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 translate-y-0"
       x-transition:leave-end="opacity-0 -translate-y-2"
       class="mb-5 bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-4">
      {{ __('support.new_ticket_btn') }}
    </h3>
    <form method="POST" action="{{ route('support.store') }}" class="space-y-3">
      @csrf
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1.5">
            {{ __('support.type_label') }}
          </label>
          <select name="type" required class="input-enhanced w-full py-2 text-sm">
            <option value="consulta">{{ __('support.type_consulta') }}</option>
            <option value="problema">{{ __('support.type_problema') }}</option>
            <option value="sugerencia">{{ __('support.type_sugerencia') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1.5">
            {{ __('support.subject_label') }}
          </label>
          <input name="subject" type="text"
                 placeholder="{{ __('support.subject_placeholder') }}"
                 class="input-enhanced w-full py-2 text-sm">
        </div>
      </div>
      <div>
        <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1.5">
          {{ __('support.message_label') }}
        </label>
        <textarea name="message" rows="4" required
                  placeholder="{{ __('support.message_placeholder') }}"
                  class="input-enhanced w-full py-2 text-sm resize-none"></textarea>
      </div>
      <div class="flex justify-end gap-2 pt-1">
        <button type="button" @click="showForm = false"
                class="px-4 py-2 rounded-lg text-sm border border-neutral-200 dark:border-neutral-700
                       text-neutral-600 dark:text-neutral-300
                       hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
          {{ __('support.cancel_btn') }}
        </button>
        <button type="submit"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150 active:scale-[0.98]
                       bg-indigo-600 hover:bg-indigo-700 text-white">
          {{ __('support.send_btn') }}
        </button>
      </div>
    </form>
  </div>

  {{-- Filtros --}}
  <div class="mb-4 flex flex-wrap items-center gap-1.5">
    <span class="text-xs font-medium text-neutral-400 dark:text-neutral-500">{{ __('support.status_label') }}</span>

    @foreach(['' => __('support.status_all'), 'nuevo' => __('support.status_nuevo'), 'en_proceso' => __('support.status_en_proceso'), 'solucionado' => __('support.status_solucionado')] as $k => $label)
      <a href="{{ request()->fullUrlWithQuery(['status' => $k ?: null, 'page' => null]) }}"
         class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                {{ ($status === $k || ($k === '' && ($status === null || $status === '')))
                   ? 'bg-indigo-600 text-white shadow-sm'
                   : 'text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-700 dark:hover:text-neutral-200' }}">
        {{ $label }}
      </a>
    @endforeach

    <span class="w-px h-4 bg-neutral-200 dark:bg-neutral-700 mx-0.5 shrink-0"></span>

    <span class="text-xs font-medium text-neutral-400 dark:text-neutral-500">{{ __('support.type_filter_label') }}</span>

    @foreach(['' => __('support.status_all'), 'consulta' => __('support.type_consulta'), 'problema' => __('support.type_problema'), 'sugerencia' => __('support.type_sugerencia')] as $k => $label)
      <a href="{{ request()->fullUrlWithQuery(['type' => $k ?: null, 'page' => null]) }}"
         class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                {{ ($type === $k || ($k === '' && ($type === null || $type === '')))
                   ? 'bg-indigo-600 text-white shadow-sm'
                   : 'text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-700 dark:hover:text-neutral-200' }}">
        {{ $label }}
      </a>
    @endforeach
  </div>

  {{-- Lista de tickets --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 shadow-sm overflow-hidden">
    @forelse($tickets as $ticket)
      @php
        $sInfo = $statusMap[$ticket->status] ?? ['label' => ucfirst($ticket->status), 'cls' => 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300', 'bar' => 'bg-neutral-300'];
        $tInfo = $typeMap[$ticket->type]     ?? ['label' => ucfirst($ticket->type),   'cls' => 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300'];
      @endphp
      <a href="{{ route('support.show', $ticket) }}"
         class="flex items-center gap-4 px-5 py-4 group transition-colors
                border-b border-neutral-50 dark:border-neutral-800/60 last:border-0
                hover:bg-neutral-50/70 dark:hover:bg-neutral-800/30">

        {{-- Barra de estado (izquierda) --}}
        <div class="w-1 self-stretch rounded-full shrink-0 {{ $sInfo['bar'] }}"></div>

        {{-- Contenido --}}
        <div class="flex-1 min-w-0">
          <div class="font-medium text-neutral-900 dark:text-neutral-100 truncate">
            {{ $ticket->subject ?: __('support.no_subject') }}
          </div>
          <div class="mt-0.5 flex items-center gap-1.5 text-xs text-neutral-400 dark:text-neutral-500">
            <span>#{{ $ticket->id }}</span>
            <span>·</span>
            <span>{{ $ticket->updated_at?->diffForHumans() }}</span>
            @if(auth()->user()->isMaster())
              <span>·</span>
              <span>{{ $ticket->user->name }}</span>
            @endif
          </div>
        </div>

        {{-- Badges --}}
        <div class="flex items-center gap-2 shrink-0">
          <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium {{ $tInfo['cls'] }}">
            {{ $tInfo['label'] }}
          </span>
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium {{ $sInfo['cls'] }}">
            <span class="w-1.5 h-1.5 rounded-full bg-current shrink-0"></span>
            {{ $sInfo['label'] }}
          </span>
        </div>

        {{-- Chevron --}}
        <svg class="w-4 h-4 shrink-0 text-neutral-300 dark:text-neutral-600
                    group-hover:text-neutral-400 dark:group-hover:text-neutral-500 transition-colors"
             viewBox="0 0 24 24" fill="none">
          <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>
    @empty
      <div class="py-16 text-center">
        <div class="w-14 h-14 rounded-2xl bg-neutral-100 dark:bg-neutral-800
                    flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6 text-neutral-300 dark:text-neutral-600" viewBox="0 0 24 24" fill="none">
            <path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300">{{ __('support.no_tickets') }}</p>
        <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">
          {{ __('support.new_ticket_btn') }} para comenzar
        </p>
      </div>
    @endforelse
  </div>

  {{-- Paginación --}}
  @if($tickets->hasPages())
    <div class="mt-5">{{ $tickets->withQueryString()->links() }}</div>
  @endif

</div>
@endsection
