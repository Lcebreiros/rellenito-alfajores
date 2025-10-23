@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Clientes</h1>
@endsection

@section('header_actions')
  <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
    <i class="fas fa-user-plus"></i> Nuevo cliente
  </a>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">{{ session('ok') }}</div>
  @endif

  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-4 mb-4">
    <form method="GET" class="flex gap-2 items-center">
      <div class="relative flex-1">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></i>
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre, email, teléfono, DNI…"
               class="w-full pl-9 pr-4 py-2.5 rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <button class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Buscar</button>
      @if($q !== '')
        <a href="{{ route('clients.index') }}" class="px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200">Limpiar</a>
      @endif
    </form>
  </div>

  @if($clients->count())
    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[880px] text-sm">
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
                <td class="px-3 py-3">{{ $c->email ?: '—' }}</td>
                <td class="px-3 py-3">{{ $c->phone ?: '—' }}</td>
                <td class="px-3 py-3">{{ $c->document_number ?: '—' }}</td>
                <td class="px-3 py-3">$ {{ number_format((float)($c->balance ?? 0), 2, ',', '.') }}</td>
                <td class="px-3 py-3">
                  <a href="{{ route('clients.show', $c) }}" class="inline-flex items-center gap-1.5 rounded border border-neutral-300 px-2.5 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800"><i class="fas fa-eye"></i> Ver</a>
                  <a href="{{ route('clients.edit', $c) }}" class="inline-flex items-center gap-1.5 rounded border border-neutral-300 px-2.5 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800"><i class="fas fa-pen"></i> Editar</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="p-3">{{ $clients->links() }}</div>
    </div>
  @else
    <div class="text-center py-16 text-neutral-600 dark:text-neutral-300">No hay clientes.</div>
  @endif
  </div>
@endsection
