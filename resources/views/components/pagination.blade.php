@props(['paginator'])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" {{ $attributes->merge(['class' => 'flex items-center justify-between']) }}>
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-500 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 cursor-default leading-5 rounded-lg">
                    Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 leading-5 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                    Anterior
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 leading-5 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                    Siguiente
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-neutral-500 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 cursor-default leading-5 rounded-lg">
                    Siguiente
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-neutral-700 dark:text-neutral-300 leading-5">
                    Mostrando
                    <span class="font-medium">{{ $paginator->firstItem() }}</span>
                    a
                    <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    de
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    resultados
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-lg shadow-sm">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Anterior">
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-neutral-500 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 cursor-default rounded-l-lg leading-5" aria-hidden="true">
                                <x-svg-icon name="chevron-left" size="5" />
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-neutral-500 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 rounded-l-lg leading-5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors" aria-label="Anterior">
                            <x-svg-icon name="chevron-left" size="5" />
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($paginator->links()->elements[0] as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page">
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-indigo-600 border border-indigo-600 cursor-default leading-5">{{ $page }}</span>
                            </span>
                        @elseif (is_string($page))
                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 cursor-default leading-5">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 leading-5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors" aria-label="Ir a página {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-neutral-500 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 rounded-r-lg leading-5 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors" aria-label="Siguiente">
                            <x-svg-icon name="chevron-right" size="5" />
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="Siguiente">
                            <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-neutral-500 bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 cursor-default rounded-r-lg leading-5" aria-hidden="true">
                                <x-svg-icon name="chevron-right" size="5" />
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
