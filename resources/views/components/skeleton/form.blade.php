@props(['fields' => 4])

<div {{ $attributes->merge(['class' => 'animate-pulse bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6']) }}>
    <!-- Form title -->
    <div class="mb-6">
        <div class="h-6 bg-neutral-200 dark:bg-neutral-700 rounded w-1/3 mb-2"></div>
        <div class="h-4 bg-neutral-100 dark:bg-neutral-800 rounded w-2/3"></div>
    </div>

    <!-- Form fields -->
    <div class="space-y-6">
        @for ($i = 0; $i < $fields; $i++)
        <div class="space-y-2">
            <!-- Label -->
            <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-1/4"></div>

            <!-- Input -->
            <div class="h-10 bg-neutral-100 dark:bg-neutral-800 rounded-lg w-full"></div>

            <!-- Helper text (occasionally) -->
            @if ($i % 2 === 0)
            <div class="h-3 bg-neutral-100 dark:bg-neutral-800 rounded w-2/3"></div>
            @endif
        </div>
        @endfor
    </div>

    <!-- Submit button -->
    <div class="mt-8 pt-6 border-t border-neutral-100 dark:border-neutral-800">
        <div class="h-11 bg-neutral-200 dark:bg-neutral-700 rounded-lg w-full"></div>
    </div>
</div>
