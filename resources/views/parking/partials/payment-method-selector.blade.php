@props(['paymentMethods' => collect()])

@if($paymentMethods->isNotEmpty())
  <div class="space-y-2"
       x-data="{
         selected: {{ json_encode(request()->input('payment_method_ids', [])) }},
         methods: {{ $paymentMethods->map(fn($pm) => [
            'id' => $pm->id,
            'name' => $pm->name,
            'logo' => $pm->hasLogo() ? asset('images/'.$pm->getLogo()) : null,
            'icon' => $pm->getIcon(),
         ])->values()->toJson() }},
         toggle(id) {
           id = Number(id);
           if (this.selected.includes(id)) {
             this.selected = this.selected.filter(v => v !== id);
           } else {
             this.selected.push(id);
           }
         }
       }">
    <p class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">Método de pago</p>
    <div class="flex flex-wrap gap-2">
      @foreach($paymentMethods as $pm)
        <label
          class="relative inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold shadow-sm transition-all duration-200
                 cursor-pointer select-none
                 dark:border-neutral-700 dark:bg-neutral-900"
          :class="selected.includes({{ $pm->id }})
            ? 'border-indigo-500 bg-indigo-50/80 ring-2 ring-indigo-200 dark:bg-indigo-900/30 dark:ring-indigo-600/40'
            : 'border-neutral-200 bg-white hover:border-indigo-200 hover:bg-indigo-50/60 dark:hover:border-indigo-500/30 dark:hover:bg-neutral-800/70'"
          @click.prevent="toggle({{ $pm->id }})"
        >
          <input
            type="checkbox"
            x-model="selected"
            name="payment_method_ids[]"
            value="{{ $pm->id }}"
            class="sr-only"
          />
          <span class="flex h-8 w-8 items-center justify-center overflow-hidden rounded border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            @if($pm->hasLogo())
              <img src="{{ asset('images/' . $pm->getLogo()) }}" alt="{{ $pm->name }}" class="h-full w-full object-contain p-1">
            @else
              <x-dynamic-component
                :component="'heroicon-o-' . $pm->getIcon()"
                class="h-5 w-5 text-neutral-600 dark:text-neutral-300"
              />
            @endif
          </span>
          <span class="text-neutral-800 dark:text-neutral-100">{{ $pm->name }}</span>
          <span class="absolute -right-1.5 -top-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-bold text-white transition
                       opacity-0 scale-75"
                :class="selected.includes({{ $pm->id }}) ? 'opacity-100 scale-100' : ''">
            ✓
          </span>
        </label>
      @endforeach
    </div>

    <div class="flex items-center gap-2" x-show="selected.length" x-transition>
      <span class="text-[11px] font-semibold text-neutral-500 dark:text-neutral-400">Seleccionados:</span>
      <div class="flex flex-wrap items-center gap-1.5">
        <template x-for="id in selected" :key="id">
          <div class="flex items-center gap-1 rounded-full border border-neutral-200 bg-white px-2 py-1 text-[11px] font-semibold shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
            <template x-if="methods.find(m => m.id === id)?.logo">
              <img :src="methods.find(m => m.id === id).logo" alt="" class="h-5 w-5 object-contain">
            </template>
            <template x-if="!methods.find(m => m.id === id)?.logo">
              <svg class="h-5 w-5 text-neutral-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.5h19.5m-16.5 6h13.5M6 5.25h12A1.5 1.5 0 0119.5 6.75v10.5A1.5 1.5 0 0118 18.75H6A1.5 1.5 0 014.5 17.25V6.75A1.5 1.5 0 016 5.25z" />
              </svg>
            </template>
            <span x-text="methods.find(m => m.id === id)?.name || 'Pago'"></span>
          </div>
        </template>
      </div>
    </div>
  </div>
@endif
