@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
  {{-- Header --}}
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Descuentos y Bonificaciones</h1>
      <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
        Gestiona descuentos para parking, comercios y servicios
      </p>
    </div>
    <a href="{{ route('discounts.create') }}"
       class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold transition shadow-md">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
      </svg>
      Nuevo Descuento
    </a>
  </div>

  {{-- Success message --}}
  @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('success') }}
    </div>
  @endif

  {{-- Lista de descuentos --}}
  @if($discounts->isEmpty())
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">No hay descuentos creados</h3>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
          Crea tu primer descuento para ofrecer promociones a tus clientes
        </p>
        <a href="{{ route('discounts.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Crear Descuento
        </a>
      </div>
    </div>
  @else
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/50">
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Nombre</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Tipo</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Valor</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Socio/Aliado</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Vigencia</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Estado</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @foreach($discounts as $discount)
              <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition">
                <td class="px-4 py-3 text-sm">
                  <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ $discount->name }}</div>
                  @if($discount->code)
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 font-mono mt-0.5">{{ $discount->code }}</div>
                  @endif
                </td>
                <td class="px-4 py-3 text-sm">
                  @if($discount->type === 'free_minutes')
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400 text-xs font-semibold">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      Minutos gratis
                    </span>
                  @elseif($discount->type === 'percentage')
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 text-xs font-semibold">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                      </svg>
                      Porcentaje
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-semibold">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      Monto fijo
                    </span>
                  @endif
                </td>
                <td class="px-4 py-3 text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                  @if($discount->type === 'free_minutes')
                    {{ $discount->value }} min
                  @elseif($discount->type === 'percentage')
                    {{ $discount->value }}%
                  @else
                    ${{ number_format($discount->value, 2, ',', '.') }}
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $discount->partner ?: '-' }}
                </td>
                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                  @if($discount->starts_at || $discount->ends_at)
                    @if($discount->starts_at)
                      <div>Desde: {{ $discount->starts_at->format('d/m/Y') }}</div>
                    @endif
                    @if($discount->ends_at)
                      <div>Hasta: {{ $discount->ends_at->format('d/m/Y') }}</div>
                    @endif
                  @else
                    Permanente
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-center">
                  @if($discount->is_active)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-semibold">
                      <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                      Activo
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400 text-xs font-semibold">
                      <div class="w-2 h-2 rounded-full bg-neutral-400"></div>
                      Inactivo
                    </span>
                  @endif
                </td>
                <td class="px-4 py-3 text-sm">
                  <div class="flex items-center justify-center gap-2">
                    {{-- Toggle activo/inactivo --}}
                    <form method="POST" action="{{ route('discounts.toggle', $discount) }}" class="inline">
                      @csrf
                      <button type="submit"
                              class="inline-flex items-center gap-1 px-2 py-1 rounded-lg {{ $discount->is_active ? 'bg-neutral-200 hover:bg-neutral-300 dark:bg-neutral-700 dark:hover:bg-neutral-600' : 'bg-emerald-600 hover:bg-emerald-700' }} text-xs font-semibold transition"
                              title="{{ $discount->is_active ? 'Desactivar' : 'Activar' }}">
                        @if($discount->is_active)
                          <svg class="w-3 h-3 text-neutral-700 dark:text-neutral-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                          </svg>
                        @else
                          <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                          </svg>
                        @endif
                      </button>
                    </form>

                    {{-- Editar --}}
                    <a href="{{ route('discounts.edit', $discount) }}"
                       class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition"
                       title="Editar">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </a>

                    {{-- Eliminar --}}
                    <form method="POST" action="{{ route('discounts.destroy', $discount) }}" class="inline"
                          onsubmit="return confirm('¿Estás seguro de eliminar este descuento?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                              class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition"
                              title="Eliminar">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
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

    {{-- Paginación --}}
    @if($discounts->hasPages())
      <div class="mt-6">
        {{ $discounts->links() }}
      </div>
    @endif
  @endif
</div>
@endsection
