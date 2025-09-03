<a {{ $attributes->merge([
    'class' => '
        block w-full px-4 py-2 text-start text-sm leading-5 transition duration-150 ease-in-out
        text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100
        dark:text-neutral-200 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800
    '
]) }}>
    {{ $slot }}
</a>
