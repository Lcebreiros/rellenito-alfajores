<input
  type="checkbox"
  {!! $attributes->merge([
      'class' => '
        rounded shadow-sm
        border-gray-300 text-indigo-600 focus:ring-indigo-500
        dark:border-neutral-700 dark:bg-neutral-900 dark:checked:bg-indigo-600 dark:focus:ring-indigo-400
      '
  ]) !!}
>
