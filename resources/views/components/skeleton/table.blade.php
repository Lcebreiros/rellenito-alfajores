@props(['rows' => 5, 'columns' => 4])

<div {{ $attributes->merge(['class' => 'animate-pulse']) }}>
    <!-- Table header -->
    <div class="bg-neutral-50 dark:bg-neutral-900/50 rounded-t-2xl border border-neutral-200 dark:border-neutral-800 px-6 py-4">
        <div class="grid gap-4" style="grid-template-columns: repeat({{ $columns }}, 1fr);">
            @for ($i = 0; $i < $columns; $i++)
            <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
            @endfor
        </div>
    </div>

    <!-- Table rows -->
    <div class="bg-white dark:bg-neutral-900 rounded-b-2xl border-x border-b border-neutral-200 dark:border-neutral-800">
        @for ($row = 0; $row < $rows; $row++)
        <div class="px-6 py-4 {{ $row > 0 ? 'border-t border-neutral-100 dark:border-neutral-800' : '' }}">
            <div class="grid gap-4" style="grid-template-columns: repeat({{ $columns }}, 1fr);">
                @for ($col = 0; $col < $columns; $col++)
                <div class="space-y-2">
                    <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-3/4"></div>
                    <div class="h-3 bg-neutral-100 dark:bg-neutral-800 rounded w-1/2"></div>
                </div>
                @endfor
            </div>
        </div>
        @endfor
    </div>
</div>
