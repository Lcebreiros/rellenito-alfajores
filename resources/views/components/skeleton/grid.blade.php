@props(['items' => 6, 'columns' => 3])

<div {{ $attributes->merge(['class' => 'grid gap-6']) }} style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));">
    @for ($i = 0; $i < $items; $i++)
    <div class="animate-pulse bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
        <!-- Image placeholder -->
        <div class="h-48 bg-neutral-200 dark:bg-neutral-700"></div>

        <!-- Content -->
        <div class="p-4 space-y-3">
            <!-- Title -->
            <div class="h-5 bg-neutral-200 dark:bg-neutral-700 rounded w-3/4"></div>

            <!-- Description -->
            <div class="space-y-2">
                <div class="h-3 bg-neutral-100 dark:bg-neutral-800 rounded w-full"></div>
                <div class="h-3 bg-neutral-100 dark:bg-neutral-800 rounded w-5/6"></div>
            </div>

            <!-- Footer -->
            <div class="pt-3 flex items-center justify-between">
                <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-20"></div>
                <div class="h-8 bg-neutral-200 dark:bg-neutral-700 rounded w-24"></div>
            </div>
        </div>
    </div>
    @endfor
</div>
