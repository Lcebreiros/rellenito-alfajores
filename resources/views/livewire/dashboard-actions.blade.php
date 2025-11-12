{{-- resources/views/livewire/dashboard-actions.blade.php --}}
<div x-data="{ addOpen: false, editMode: @entangle('editMode').live }" class="flex items-center gap-2">
  {{-- Botón Añadir widget --}}
  <div class="relative" x-show="editMode" x-transition.opacity>
    <button
      type="button"
      @click="addOpen = !addOpen"
      class="px-3 py-2 rounded-lg bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-sm font-semibold transition-colors"
      title="Añadir un widget al tablero"
    >
      + Añadir widget
    </button>

    <div
      x-show="addOpen && editMode"
      @click.outside="addOpen=false"
      x-transition.opacity
      class="absolute right-0 mt-2 w-56 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-lg p-2 z-10"
    >
      @forelse($available as $key => $meta)
        <button
          type="button"
          @click="addOpen=false"
          wire:click="addWidget('{{ $key }}')"
          class="w-full text-left px-3 py-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 text-sm transition-colors"
        >
          {{ $meta['label'] ?? $key }}
        </button>
      @empty
        <div class="px-3 py-2 text-sm text-neutral-500">No hay widgets detectados.</div>
      @endforelse
    </div>
  </div>

  {{-- Botón Editar --}}
  <button
    type="button"
    wire:click="toggleEdit"
    class="px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition-colors"
  >
    {{ $editMode ? 'Salir de edición' : 'Editar' }}
  </button>
</div>
