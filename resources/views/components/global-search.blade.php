{{-- Búsqueda global con Ctrl/Cmd+K --}}
<div x-data="globalSearch()" x-cloak>
    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 z-50 bg-neutral-900/50 dark:bg-black/70 backdrop-blur-sm"
         style="display: none;">
    </div>

    {{-- Search Modal --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed inset-x-0 top-[10%] z-50 mx-auto max-w-2xl px-4"
         style="display: none;">

        <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl border border-neutral-200 dark:border-neutral-800 overflow-hidden">
            {{-- Search Input --}}
            <div class="flex items-center gap-3 p-4 border-b border-neutral-200 dark:border-neutral-800">
                <x-icon name="search" size="5" class="text-neutral-400" />
                <input x-ref="searchInput"
                       x-model="query"
                       @keydown.escape="open = false"
                       @keydown.down.prevent="selectedIndex = Math.min(selectedIndex + 1, results.length - 1)"
                       @keydown.up.prevent="selectedIndex = Math.max(selectedIndex - 1, 0)"
                       @keydown.enter.prevent="navigateToSelected()"
                       type="text"
                       placeholder="Buscar productos, pedidos, clientes..."
                       class="flex-1 bg-transparent border-0 text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:ring-0 text-lg">
                <kbd class="hidden sm:inline-flex px-2 py-1 text-xs font-semibold text-neutral-500 bg-neutral-100 dark:bg-neutral-800 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700 rounded">
                    ESC
                </kbd>
            </div>

            {{-- Results --}}
            <div class="max-h-96 overflow-y-auto scrollbar-thin">
                <template x-if="loading">
                    <div class="p-8 text-center">
                        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-indigo-600 border-r-transparent"></div>
                        <p class="mt-2 text-sm text-neutral-500">Buscando...</p>
                    </div>
                </template>

                <template x-if="!loading && query.length > 0 && results.length === 0">
                    <div class="p-8 text-center">
                        <x-icon name="search" size="12" class="mx-auto text-neutral-300 dark:text-neutral-700 mb-3" />
                        <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">No se encontraron resultados</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Intenta con otros términos de búsqueda</p>
                    </div>
                </template>

                <template x-if="!loading && results.length > 0">
                    <div class="py-2">
                        <template x-for="(result, index) in results" :key="result.id">
                            <a :href="result.url"
                               @mouseenter="selectedIndex = index"
                               :class="selectedIndex === index ? 'bg-neutral-100 dark:bg-neutral-800' : ''"
                               class="flex items-center gap-3 px-4 py-3 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors cursor-pointer">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                                        <span class="text-indigo-600 dark:text-indigo-400 text-xs font-semibold uppercase" x-text="result.type.charAt(0)"></span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate" x-text="result.title"></p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate" x-text="result.subtitle"></p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                          :class="{
                                              'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300': result.type === 'producto',
                                              'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300': result.type === 'pedido',
                                              'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300': result.type === 'cliente',
                                          }"
                                          x-text="result.type"></span>
                                </div>
                            </a>
                        </template>
                    </div>
                </template>

                <template x-if="!loading && query.length === 0">
                    <div class="p-8 text-center">
                        <x-icon name="search" size="12" class="mx-auto text-neutral-300 dark:text-neutral-700 mb-3" />
                        <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">Búsqueda rápida</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Escribe para buscar productos, pedidos o clientes</p>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-4 py-3 bg-neutral-50 dark:bg-neutral-800/50 border-t border-neutral-200 dark:border-neutral-800 text-xs text-neutral-500">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-white dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 rounded">↑</kbd>
                        <kbd class="px-1.5 py-0.5 bg-white dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 rounded">↓</kbd>
                        navegar
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-white dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 rounded">↵</kbd>
                        abrir
                    </span>
                </div>
                <span>Presiona <kbd class="px-1.5 py-0.5 bg-white dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 rounded">ESC</kbd> para cerrar</span>
            </div>
        </div>
    </div>
</div>

<script>
function globalSearch() {
    return {
        open: false,
        query: '',
        results: [],
        selectedIndex: 0,
        loading: false,
        searchTimeout: null,

        init() {
            // Keyboard shortcut: Cmd/Ctrl + K
            document.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    this.open = !this.open;
                    if (this.open) {
                        this.$nextTick(() => this.$refs.searchInput.focus());
                    }
                }
            });

            // Watch query changes
            this.$watch('query', (value) => {
                clearTimeout(this.searchTimeout);

                if (value.length < 2) {
                    this.results = [];
                    return;
                }

                this.loading = true;
                this.searchTimeout = setTimeout(() => {
                    this.performSearch(value);
                }, 300);
            });

            // Reset on close
            this.$watch('open', (value) => {
                if (!value) {
                    this.query = '';
                    this.results = [];
                    this.selectedIndex = 0;
                }
            });
        },

        async performSearch(query) {
            try {
                const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.results = data.results || [];
                } else {
                    console.error('Search failed:', response.statusText);
                    this.results = [];
                }
            } catch (error) {
                console.error('Search error:', error);
                this.results = [];
            } finally {
                this.loading = false;
            }
        },

        navigateToSelected() {
            if (this.results[this.selectedIndex]) {
                window.location.href = this.results[this.selectedIndex].url;
            }
        }
    };
}
</script>
