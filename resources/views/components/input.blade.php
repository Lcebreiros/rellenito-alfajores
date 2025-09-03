@props(['disabled' => false])

<input
  {{ $disabled ? 'disabled' : '' }}
  {!! $attributes->merge([
    'class' => '
      rounded-md shadow-sm transition
      border-gray-300 focus:border-indigo-500 focus:ring-indigo-500
      dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400
      disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed
      dark:disabled:bg-neutral-800 dark:disabled:text-neutral-500
    ',
  ]) !!}
>
