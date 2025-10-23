@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $client->name }}</h1>
      <div class="text-sm text-neutral-500 dark:text-neutral-400">{{ $client->email ?: 'Sin email' }} · {{ $client->phone ?: 'Sin contacto' }}</div>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('clients.index') }}" class="px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-sm">Volver</a>
      <a href="{{ route('clients.edit', $client) }}" class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm">Editar</a>
    </div>
  </div>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6 space-y-6">
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">{{ session('ok') }}</div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
      <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-4">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Datos</h3>
        <div class="text-sm text-neutral-700 dark:text-neutral-300 space-y-1">
          <div><span class="text-neutral-500">Email:</span> {{ $client->email ?: '—' }}</div>
          <div><span class="text-neutral-500">Contacto:</span> {{ $client->phone ?: '—' }}</div>
          <div><span class="text-neutral-500">DNI:</span> {{ $client->document_number ?: '—' }}</div>
          <div><span class="text-neutral-500">Dirección:</span> {{ $client->address ?: '—' }} ({{ $client->city ?: '—' }}, {{ $client->province ?: '—' }}, {{ $client->country ?: '—' }})</div>
          <div><span class="text-neutral-500">Tags:</span> 
            @if(is_array($client->tags) && count($client->tags))
              @foreach($client->tags as $t)
                <span class="inline-flex items-center px-2 py-0.5 text-[11px] rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 mr-1">{{ $t }}</span>
              @endforeach
            @else
              —
            @endif
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-4">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Historial de compras (últimos 20)</h3>
        @if(($client->orders ?? collect())->count())
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="text-neutral-500">
                  <th class="px-2 py-2 text-left">ID</th>
                  <th class="px-2 py-2 text-left">Fecha</th>
                  <th class="px-2 py-2 text-left">Total</th>
                  <th class="px-2 py-2 text-left">Estado</th>
                  <th class="px-2 py-2 text-left">Acción</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                @foreach($client->orders as $o)
                  <tr>
                    <td class="px-2 py-2">#{{ $o->order_number ?? $o->id }}</td>
                    <td class="px-2 py-2">{{ $o->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="px-2 py-2">$ {{ number_format((float)($o->total ?? 0), 2, ',', '.') }}</td>
                    <td class="px-2 py-2">{{ is_string($o->status) ? ucfirst($o->status) : ($o->status->name ?? '—') }}</td>
                    <td class="px-2 py-2"><a href="{{ route('orders.show', $o) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver</a></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-sm text-neutral-600 dark:text-neutral-300">Sin compras registradas.</div>
        @endif
      </div>
    </div>

    <div class="space-y-4">
      <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-4">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Saldo y cuentas</h3>
        <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">$ {{ number_format((float)($client->balance ?? 0), 2, ',', '.') }}</div>
        <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">(Cuentas a cobrar – próximo: próximamente)</div>
      </div>

      <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-4">
        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Notas</h3>
        <div class="prose dark:prose-invert max-w-none text-sm">{{ $client->notes ?: '—' }}</div>
      </div>
    </div>
  </div>
</div>
@endsection

