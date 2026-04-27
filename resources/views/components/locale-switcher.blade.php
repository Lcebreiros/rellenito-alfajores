@php
    $current = app()->getLocale();
    $locales = [
        'es' => ['label' => 'ES', 'flag' => '🇦🇷', 'name' => __('ui.lang_es')],
        'en' => ['label' => 'EN', 'flag' => '🇺🇸', 'name' => __('ui.lang_en')],
        'pt' => ['label' => 'PT', 'flag' => '🇧🇷', 'name' => __('ui.lang_pt')],
    ];
@endphp

<div x-data="{ open: false }" class="relative">
    {{-- Botón disparador --}}
    <button
        type="button"
        @click="open = !open"
        @keydown.escape.window="open = false"
        class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg text-xs font-semibold uppercase tracking-wide
               text-white/70 hover:text-white hover:bg-white/10 transition-colors"
        :aria-expanded="open"
        aria-haspopup="true"
        :title="'{{ __('ui.language') }}'"
    >
        <span>{{ $locales[$current]['flag'] }}</span>
        <span class="nav-label">{{ $locales[$current]['label'] }}</span>
        <svg x-show="!open" class="nav-label w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
        <svg x-show="open" x-cloak class="nav-label w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="open = false"
        class="absolute bottom-full left-0 mb-2 w-36 rounded-xl shadow-2xl
               bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700
               py-1 z-50 origin-bottom-left"
        role="menu"
    >
        @foreach ($locales as $code => $info)
            <form method="POST" action="{{ route('locale.switch', $code) }}">
                @csrf
                <button
                    type="submit"
                    role="menuitem"
                    class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-neutral-700 dark:text-neutral-200
                           hover:bg-neutral-100 dark:hover:bg-neutral-700/70 transition-colors
                           {{ $current === $code ? 'font-semibold bg-neutral-50 dark:bg-neutral-700/40' : '' }}"
                >
                    <span class="text-base leading-none">{{ $info['flag'] }}</span>
                    <span>{{ $info['name'] }}</span>
                    @if ($current === $code)
                        <svg class="ml-auto w-3.5 h-3.5 text-violet-600 dark:text-violet-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</div>
