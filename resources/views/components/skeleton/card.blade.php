@props(['rows' => 3])

<div {{ $attributes->merge(['class' => 'animate-pulse bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-6']) }}>
    <!-- Header skeleton -->
    <div class="flex items-center justify-between mb-6">
        <div class="h-6 bg-neutral-200 dark:bg-neutral-700 rounded w-1/3"></div>
        <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-16"></div>
    </div>

    <!-- Content rows -->
    @for ($i = 0; $i < $rows; $i++)
    <div class="space-y-3 mb-4">
        <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-full"></div>
        <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-5/6"></div>
    </div>
    @endfor

    <!-- Footer -->
    <div class="mt-6 pt-4 border-t border-neutral-100 dark:border-neutral-800">
        <div class="h-10 bg-neutral-200 dark:bg-neutral-700 rounded w-full"></div>
    </div>
</div>
