@props([
    'items' => [], // [['label' => 'Home', 'url' => '/'], ...]
])

@if(count($items) > 0)
<nav {{ $attributes->merge(['class' => 'flex items-center text-sm mb-4']) }} aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2">
        @foreach($items as $index => $item)
            <li class="inline-flex items-center">
                @if($index > 0)
                    <x-svg-icon name="chevron-right" size="4" class="text-neutral-400 dark:text-neutral-500 mx-1 md:mx-2" />
                @endif

                @if(isset($item['url']) && $index !== count($items) - 1)
                    <a href="{{ $item['url'] }}"
                       class="text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 transition-colors">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-neutral-900 dark:text-neutral-100 font-medium">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
