@props(['items' => 5])

<div {{ $attributes->merge(['class' => 'animate-pulse space-y-3']) }}>
    @for ($i = 0; $i < $items; $i++)
    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-4">
        <div class="flex items-center space-x-4">
            <!-- Avatar/Icon -->
            <div class="flex-shrink-0 w-12 h-12 bg-neutral-200 dark:bg-neutral-700 rounded-full"></div>

            <!-- Content -->
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-1/3"></div>
                <div class="h-3 bg-neutral-100 dark:bg-neutral-800 rounded w-full"></div>
                <div class="h-3 bg-neutral-100 dark:bg-neutral-800 rounded w-2/3"></div>
            </div>

            <!-- Action button -->
            <div class="flex-shrink-0 w-20 h-8 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
        </div>
    </div>
    @endfor
</div>
