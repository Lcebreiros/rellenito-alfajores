@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400
               text-start text-base font-medium
               text-indigo-700 dark:text-indigo-300
               bg-indigo-50 dark:bg-indigo-600/20
               focus:outline-none
               focus:text-indigo-800 dark:focus:text-indigo-200
               focus:bg-indigo-100 dark:focus:bg-indigo-600/30
               focus:border-indigo-700
               transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent
               text-start text-base font-medium
               text-gray-600 dark:text-neutral-400
               hover:text-gray-800 dark:hover:text-neutral-200
               hover:bg-gray-50 dark:hover:bg-neutral-800
               hover:border-gray-300 dark:hover:border-neutral-600
               focus:outline-none
               focus:text-gray-800 dark:focus:text-neutral-200
               focus:bg-gray-50 dark:focus:bg-neutral-800
               focus:border-gray-300 dark:focus:border-neutral-600
               transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
