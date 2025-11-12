<div x-data="{ on: @entangle('enabled') }" class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm">
  <div class="px-5 sm:px-6 py-4 border-b border-neutral-100 dark:border-neutral-800/60 flex items-center justify-between">
    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Agendar</h3>
    <label class="inline-flex items-center cursor-pointer">
      <input type="checkbox" class="sr-only peer" wire:model="enabled" x-model="on">
      <div class="w-11 h-6 bg-neutral-200 peer-focus:outline-none rounded-full peer dark:bg-neutral-700 peer-checked:bg-indigo-600 relative transition">
        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transform peer-checked:translate-x-5 transition"></div>
      </div>
    </label>
  </div>
  <div class="px-5 sm:px-6 py-4">
    <div class="grid gap-2" x-show="on" x-cloak>
        <label for="schedule-datetime" class="text-xs text-neutral-600 dark:text-neutral-400">Fecha y hora</label>
        <input id="schedule-datetime" type="datetime-local" step="60" wire:model.lazy="datetime"
               class="w-full px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-sm"/>
        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Se guardará como agendado al confirmar el pedido.</p>
    </div>
    <p class="text-xs text-neutral-500 dark:text-neutral-400" x-show="!on">Activá el switch y elegí fecha. Al confirmar, se agenda.</p>
  </div>
</div>
