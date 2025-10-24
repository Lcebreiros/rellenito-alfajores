<button type="button"
        wire:click="addOne"
        wire:loading.attr="disabled"
        class="group text-left w-full overflow-hidden rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition
               dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-indigo-500/50">
  @if($service)
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <h3 class="text-sm sm:text-base font-medium text-neutral-900 dark:text-neutral-100 line-clamp-2">
          {{ $service->name }}
        </h3>
        @if($service->description)
          <div class="mt-0.5 text-[12px] text-neutral-500 dark:text-neutral-400 line-clamp-2">
            {{ $service->description }}
          </div>
        @endif
      </div>
      <div class="shrink-0 text-[11px] sm:text-xs rounded-full px-2 py-0.5 font-medium
                   {{ $isActive 
                      ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' 
                      : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' }}">
        {{ $isActive ? 'Activo' : 'Inactivo' }}
      </div>
    </div>

    <div class="mt-3 flex items-center justify-between gap-2">
      <span class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
        $ {{ number_format((float) $service->price, 2, ',', '.') }}
      </span>
    </div>
  @else
    <div class="text-sm text-neutral-500">Servicio no disponible.</div>
  @endif
</button>
