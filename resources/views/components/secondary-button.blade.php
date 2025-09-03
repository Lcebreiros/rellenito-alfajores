<button
  {{ $attributes->merge([
    'type' => 'button',
    'class' => '
      inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm
      border transition ease-in-out duration-150
      bg-white text-gray-700 border-gray-300
      hover:bg-gray-50
      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
      disabled:opacity-25

      dark:bg-neutral-800 dark:text-neutral-200 dark:border-neutral-600
      dark:hover:bg-neutral-700
      dark:focus:ring-offset-neutral-900
    '
  ]) }}
>
  {{ $slot }}
</button>
