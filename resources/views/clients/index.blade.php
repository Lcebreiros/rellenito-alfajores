@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Clientes</h1>
@endsection

@section('header_actions')
  <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
    <x-svg-icon name="user-plus" size="5" /> Nuevo cliente
  </a>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">
  {{-- Success message --}}
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300 flex items-center gap-2">
      <x-svg-icon name="check" size="5" class="text-emerald-600 dark:text-emerald-400" />
      {{ session('ok') }}
    </div>
  @endif

  {{-- Search form --}}
  <div class="panel-glass shadow-sm p-4 mb-4">
    <form method="GET" class="flex gap-2 items-center">
      <div class="relative flex-1">
        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">
          <x-svg-icon name="search" size="5" />
        </div>
        <input type="text"
               name="q"
               value="{{ $q }}"
               placeholder="Buscar por nombre, email, teléfono, DNI…"
               class="input-enhanced w-full pl-10 pr-4 py-2.5">
      </div>
      <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
        Buscar
      </button>
      @if($q !== '')
        <a href="{{ route('clients.index') }}"
           class="px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
          Limpiar
        </a>
      @endif
    </form>
  </div>

  {{-- Clients table or empty state --}}
  @if($clients->count())
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-enhanced w-full min-w-[880px] text-sm">
          <thead class="bg-neutral-100/70 dark:bg-neutral-800/60">
            <tr class="text-xs uppercase tracking-wide text-neutral-600 dark:text-neutral-300">
              <th class="px-3 py-3 text-left">Nombre</th>
              <th class="px-3 py-3 text-left">Email</th>
              <th class="px-3 py-3 text-left">Teléfono</th>
              <th class="px-3 py-3 text-left">DNI</th>
              <th class="px-3 py-3 text-left">Saldo</th>
              <th class="px-3 py-3 text-left">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
            @foreach($clients as $c)
              <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors">
                <td class="px-3 py-3 font-medium text-neutral-900 dark:text-neutral-100">{{ $c->name }}</td>
                <td class="px-3 py-3 text-neutral-700 dark:text-neutral-300">{{ $c->email ?: '—' }}</td>
                <td class="px-3 py-3 text-neutral-700 dark:text-neutral-300">{{ $c->phone ?: '—' }}</td>
                <td class="px-3 py-3 text-neutral-700 dark:text-neutral-300">{{ $c->document_number ?: '—' }}</td>
                <td class="px-3 py-3 text-neutral-900 dark:text-neutral-100 font-medium tabular-nums">
                  $ {{ number_format((float)($c->balance ?? 0), 2, ',', '.') }}
                </td>
                <td class="px-3 py-3">
                  <div class="flex items-center gap-2">
                    <a href="{{ route('clients.show', $c) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-300 px-2.5 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition-colors">
                      <x-svg-icon name="eye" size="4" />
                      Ver
                    </a>
                    <a href="{{ route('clients.edit', $c) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-300 px-2.5 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition-colors">
                      <x-svg-icon name="edit" size="4" />
                      Editar
                    </a>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="p-3 border-t border-neutral-200 dark:border-neutral-800">
        {{ $clients->links() }}
      </div>
    </div>
  @else
    <x-empty-state
      icon="user"
      title="No hay clientes aún"
      description="Comienza agregando tu primer cliente para gestionar tus ventas y relaciones comerciales."
      :action-url="route('clients.create')"
      action-text="Crear primer cliente"
      action-icon="user-plus"
    />
  @endif
</div>
@endsection
