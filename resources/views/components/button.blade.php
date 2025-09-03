<button
  {{ $attributes->merge([
    'type' => 'submit',
    'class' => '
      inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest
      border border-transparent
      text-white
      bg-gray-800 hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900
      dark:bg-indigo-600 dark:hover:bg-indigo-500 dark:focus:bg-indigo-500 dark:active:bg-indigo-700

      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900
      disabled:opacity-50 transition ease-in-out duration-150
    '
  ]) }}
>
  {{ $slot }}
</button>
