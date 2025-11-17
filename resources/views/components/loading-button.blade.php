@props(['loading' => false, 'loadingText' => 'Procesando...'])

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'relative inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-800 transition ease-in-out duration-150']) }}
        :disabled="loading"
        :class="{ 'opacity-75 cursor-not-allowed': loading }">

    <!-- Loading spinner -->
    <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>

    <!-- Button text -->
    <span x-show="!loading">{{ $slot }}</span>
    <span x-show="loading">{{ $loadingText }}</span>
</button>
