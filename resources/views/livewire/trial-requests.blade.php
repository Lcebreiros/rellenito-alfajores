<div class="min-h-screen w-full overflow-x-hidden">
  {{-- Header --}}
  <div class="mb-4 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-semibold text-neutral-900 dark:text-neutral-100">Solicitudes de Prueba</h1>
    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-1">Gestiona las solicitudes de prueba gratis</p>
  </div>

  {{-- Mensajes --}}
  @if (session('success'))
    <div class="mb-4 p-3 rounded-md bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
      {{ session('success') }}
    </div>
  @endif

  @if (session('error'))
    <div class="mb-4 p-3 rounded-md bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
      {{ session('error') }}
    </div>
  @endif

  {{-- Stats --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">Pendientes</p>
          <p class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $stats['pending'] }}</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-yellow-500/10 flex items-center justify-center">
          <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">Aprobadas</p>
          <p class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $stats['approved'] }}</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-green-500/10 flex items-center justify-center">
          <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">Rechazadas</p>
          <p class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $stats['rejected'] }}</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center">
          <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  {{-- Filtros --}}
  <div class="mb-4 flex flex-wrap gap-2">
    <button wire:click="setFilter('pending')"
            class="px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition {{ $filter === 'pending' ? 'bg-yellow-500 text-white' : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
      Pendientes
    </button>
    <button wire:click="setFilter('approved')"
            class="px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition {{ $filter === 'approved' ? 'bg-green-500 text-white' : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
      Aprobadas
    </button>
    <button wire:click="setFilter('rejected')"
            class="px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition {{ $filter === 'rejected' ? 'bg-red-500 text-white' : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
      Rechazadas
    </button>
    <button wire:click="setFilter('all')"
            class="px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition {{ $filter === 'all' ? 'bg-indigo-500 text-white' : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
      Todas
    </button>
  </div>

  {{-- Lista de solicitudes --}}
  <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900 overflow-hidden w-full">
    @forelse($requests as $request)
      <div class="p-3 sm:p-4 border-b border-neutral-200 dark:border-neutral-800 last:border-b-0">
        <div class="flex flex-col sm:flex-row items-start justify-between gap-3">
          <div class="flex-1 w-full min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <h3 class="text-base sm:text-lg font-semibold text-neutral-900 dark:text-neutral-100 truncate">{{ $request->name }}</h3>
              <span class="px-2 py-1 text-xs font-medium rounded-full
                {{ $request->isPending() ? 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-400' : '' }}
                {{ $request->isApproved() ? 'bg-green-500/10 text-green-700 dark:text-green-400' : '' }}
                {{ $request->isRejected() ? 'bg-red-500/10 text-red-700 dark:text-red-400' : '' }}">
                {{ ucfirst($request->status) }}
              </span>
              <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-500/10 text-indigo-700 dark:text-indigo-400">
                {{ $request->plan_name }}
              </span>
            </div>

            <div class="flex items-center gap-4 text-sm text-neutral-600 dark:text-neutral-400 mb-2">
              <span>✉️ {{ $request->email }}</span>
              <span>📅 {{ $request->created_at->diffForHumans() }}</span>
            </div>

            @if($request->isApproved() && $request->user)
              <div class="text-sm text-green-600 dark:text-green-400">
                ✅ Usuario creado: {{ $request->user->email }}
              </div>
            @endif

            @if($request->isRejected() && $request->notes)
              <div class="text-sm text-red-600 dark:text-red-400">
                📝 Motivo: {{ $request->notes }}
              </div>
            @endif
          </div>

          {{-- Acciones --}}
          @if($request->isPending())
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto sm:ml-4">
              <button wire:click="approve({{ $request->id }})"
                      wire:loading.attr="disabled"
                      wire:confirm="¿Aprobar esta solicitud y crear el usuario?"
                      class="px-3 sm:px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-xs sm:text-sm font-medium transition disabled:opacity-50 w-full sm:w-auto">
                ✅ Aprobar
              </button>
              <button wire:click="reject({{ $request->id }})"
                      wire:loading.attr="disabled"
                      wire:confirm="¿Rechazar esta solicitud?"
                      class="px-3 sm:px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-medium transition disabled:opacity-50 w-full sm:w-auto">
                ❌ Rechazar
              </button>
            </div>
          @endif
        </div>
      </div>
    @empty
      <div class="p-8 text-center text-neutral-500 dark:text-neutral-400">
        No hay solicitudes {{ $filter !== 'all' ? $filter : '' }}
      </div>
    @endforelse
  </div>

  {{-- Paginación --}}
  <div class="mt-4">
    {{ $requests->links() }}
  </div>
</div>
