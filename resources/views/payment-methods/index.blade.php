@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Métodos de Pago</h1>
@endsection

@section('header_actions')
  <a href="{{ route('payment-methods.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
    <x-heroicon-o-plus class="w-5 h-5" /> Nuevo método
  </a>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">{{ session('ok') }}</div>
  @endif

  @if(session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">{{ session('error') }}</div>
  @endif

  @if($paymentMethods->count())
    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-neutral-100/70 dark:bg-neutral-800/60">
            <tr class="text-xs uppercase tracking-wide text-neutral-600 dark:text-neutral-300">
              <th class="px-3 py-3 text-left">Orden</th>
              <th class="px-3 py-3 text-left">Método</th>
              <th class="px-3 py-3 text-left">Slug</th>
              <th class="px-3 py-3 text-left">Descripción</th>
              <th class="px-3 py-3 text-left">Gateway</th>
              <th class="px-3 py-3 text-center">Estado</th>
              <th class="px-3 py-3 text-left">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
            @foreach($paymentMethods as $pm)
              <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors">
                <td class="px-3 py-3 text-neutral-600 dark:text-neutral-400">{{ $pm->sort_order }}</td>
                <td class="px-3 py-3">
                  <div class="flex items-center gap-2">
                    @if($pm->icon)
                      <x-dynamic-component :component="'heroicon-o-' . $pm->getIcon()" class="w-5 h-5 text-neutral-600 dark:text-neutral-400" />
                    @endif
                    <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $pm->name }}</span>
                  </div>
                </td>
                <td class="px-3 py-3 font-mono text-xs text-neutral-600 dark:text-neutral-400">{{ $pm->slug }}</td>
                <td class="px-3 py-3 text-neutral-600 dark:text-neutral-400">{{ Str::limit($pm->description, 40) }}</td>
                <td class="px-3 py-3">
                  @if($pm->requires_gateway)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                      <x-heroicon-s-link class="w-3 h-3" /> {{ $pm->gateway_provider ?? 'API' }}
                    </span>
                  @else
                    <span class="text-neutral-400 dark:text-neutral-500 text-xs">Manual</span>
                  @endif
                </td>
                <td class="px-3 py-3 text-center">
                  @if($pm->is_active)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                      <x-heroicon-s-check-circle class="w-3 h-3" /> Activo
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
                      <x-heroicon-s-x-circle class="w-3 h-3" /> Inactivo
                    </span>
                  @endif
                </td>
                <td class="px-3 py-3">
                  <div class="flex gap-1">
                    <form action="{{ route('payment-methods.toggle', $pm) }}" method="POST" class="inline">
                      @csrf
                      <button type="submit"
                              class="inline-flex items-center gap-1.5 rounded border border-neutral-300 px-2.5 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800"
                              title="{{ $pm->is_active ? 'Desactivar' : 'Activar' }}">
                        @if($pm->is_active)
                          <x-heroicon-o-eye-slash class="w-4 h-4" />
                        @else
                          <x-heroicon-o-eye class="w-4 h-4" />
                        @endif
                      </button>
                    </form>
                    <a href="{{ route('payment-methods.edit', $pm) }}"
                       class="inline-flex items-center gap-1.5 rounded border border-neutral-300 px-2.5 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                      <x-heroicon-o-pencil class="w-4 h-4" /> Editar
                    </a>
                    <form action="{{ route('payment-methods.destroy', $pm) }}" method="POST" class="inline"
                          onsubmit="return confirm('¿Estás seguro de eliminar este método de pago?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                              class="inline-flex items-center gap-1.5 rounded border border-red-300 px-2.5 py-1.5 text-xs text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20">
                        <x-heroicon-o-trash class="w-4 h-4" />
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @else
    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-8">
      <div class="text-center py-8">
        <x-heroicon-o-credit-card class="w-16 h-16 mx-auto text-neutral-300 dark:text-neutral-700 mb-4" />
        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">No hay métodos de pago</h3>
        <p class="text-neutral-600 dark:text-neutral-400 mb-4">Crea tu primer método de pago para comenzar</p>
        <a href="{{ route('payment-methods.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
          <x-heroicon-o-plus class="w-5 h-5" /> Crear método de pago
        </a>
      </div>
    </div>
  @endif
</div>
@endsection
