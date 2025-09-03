<div x-data="dashboardManager()" 
     x-init="init()"
     id="dashboard-root"
     class="overflow-hidden h-[var(--pageH)] bg-transparent">

  <div id="dashboard-container"
       class="mx-auto max-w-4xl px-2 sm:px-3 lg:px-4 py-2 lg:py-3 h-full flex flex-col min-h-0">

    {{-- Barra de control --}}
    <div class="flex justify-end mb-3" x-data="{ openAdd: false }">
      <div class="flex items-center gap-2">
        {{-- Botón entrar en edición --}}
        <button
          x-show="!editMode"
          @click="toggleEditMode()"
          class="w-8 h-8 flex items-center justify-center rounded bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 transition"
          title="Editar dashboard"
          x-transition
        >
          {{-- Imagen de edición que se invierte en modo oscuro --}}
          <img 
            src="/images/editar.png" 
            alt="Editar" 
            class="w-4 h-4 dark:invert dark:brightness-0 dark:hover:brightness-100 transition-all"
          >
        </button>

        {{-- En modo edición: Agregar + Salir --}}
        <template x-if="editMode">
          <div class="flex items-center gap-2">
            {{-- Dropdown de agregar widget --}}
            <div class="relative">
              <button
                @click="openAdd = !openAdd"
                class="px-2.5 py-1.5 rounded text-xs font-medium bg-blue-600 text-white hover:bg-blue-700 transition"
                :class="{ 'bg-blue-700': openAdd }"
              >
                Agregar widget
              </button>

              <div
                x-show="openAdd" 
                @click.away="openAdd = false"
                x-transition
                class="absolute right-0 mt-2 w-64 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg shadow-lg z-50 max-h-80 overflow-y-auto"
              >
                <div class="px-3 py-2 border-b border-neutral-200 dark:border-neutral-800">
                  <p class="text-xs font-semibold text-neutral-700 dark:text-neutral-200">Widgets disponibles</p>
                </div>

                @foreach($availableWidgets as $type => $conf)
                  @php $isAdded = collect($widgets)->pluck('type')->contains($type); @endphp
                  <div class="px-3 py-2 flex items-start justify-between gap-2 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                    <div class="min-w-0">
                      <p class="text-xs font-medium text-neutral-900 dark:text-neutral-100 truncate">{{ $conf['name'] }}</p>
                      <p class="text-[11px] text-neutral-500 dark:text-neutral-400 truncate">{{ ucfirst($conf['size']) }} · {{ $conf['description'] }}</p>
                    </div>
                    @if($isAdded)
                      <span class="text-[11px] text-green-600 dark:text-green-400 font-medium flex-shrink-0 mt-0.5">✓</span>
                    @else
                      <button
                        @click="addWidget('{{ $type }}'); openAdd = false"
                        class="text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:underline flex-shrink-0 mt-0.5"
                        :disabled="isAddingWidget"
                      >
                        Añadir
                      </button>
                    @endif
                  </div>
                @endforeach
              </div>
            </div>

            {{-- Botón salir --}}
            <button
              @click="toggleEditMode()"
              class="px-2.5 py-1.5 rounded text-xs font-medium bg-red-600 text-white hover:bg-red-700 transition"
            >
              Salir
            </button>
          </div>
        </template>
      </div>
    </div>

    {{-- Grid --}}
    <div class="flex-1 min-h-0">
      @if(!empty($widgets))
        <div id="dashboard-grid"
             x-ref="dashboardGrid"
             class="grid gap-3 lg:gap-4 min-h-0
                    grid-cols-2 sm:grid-cols-3
                    sm:[grid-template-rows:repeat(3,minmax(0,1fr))]
                    h-[var(--gridH)] overflow-hidden">

          @foreach($widgets as $widget)
            <div class="widget-item col-span-1 row-span-1 relative min-h-0"
                 data-widget-id="{{ $widget['id'] }}"
                 wire:key="wrapper-{{ $widget['id'] }}">

              {{-- Botón eliminar (solo en edición) --}}
              <button
                x-show="editMode && !isDragging"
                type="button"
                class="absolute top-2 right-2 z-30 w-6 h-6 flex items-center justify-center rounded-full
                       bg-white text-red-600 border border-red-300 shadow-sm
                       hover:bg-red-50 transition widget-remove-btn"
                title="Quitar widget"
                wire:click="removeWidget({{ $widget['id'] }})"
                :disabled="isDragging"
              >
                ✕
              </button>

              {{-- Widget --}}
              <div class="widget-card rounded-lg lg:rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-800
                          transition-all duration-200 h-full bg-white dark:bg-neutral-900 relative overflow-hidden min-h-0"
                   data-drag-handle
                   :class="{
                     'select-none cursor-grab active:cursor-grabbing': editMode,
                     'opacity-50': isDragging && $el.contains($data.draggedElement)
                   }">

                <div class="h-full overflow-auto min-h-0"
                     :class="editMode ? 'pointer-events-none' : 'pointer-events-auto'">
                  <livewire:dynamic-component
                    :component="$widget['component']"
                    :widget-id="$widget['id']"
                    :settings="$widget['settings']"
                    :key="'widget-' . $widget['id']" />
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="h-full grid place-items-center text-center">
          <p class="text-neutral-500 dark:text-neutral-400">Tu dashboard está vacío.</p>
          <button x-show="!editMode" @click="toggleEditMode()" class="mt-2 text-blue-600 hover:underline text-sm">
            Agregar widgets
          </button>
        </div>
      @endif
    </div>

    {{-- Loading overlay --}}
    <div x-show="isLoading" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
    </div>
  </div>

  @push('scripts')
  <script>
    function dashboardManager() {
      return {
        editMode: @js($editMode),
        isLoading: false,
        isDragging: false,
        draggedElement: null,
        sortable: null,
        resizeObserver: null,
        pressHoldMs: 500,

        init() {
          this.setupHeightsObservers();
          this.$nextTick(() => this.initSortable());
          
          this.setupEventListeners();
          this.syncWithLivewire();
        },

        setupEventListeners() {
          // Sincronizar con Livewire
          Livewire.on('dashboard-edit-toggled', (e) => {
            this.editMode = !!e.editMode;
            this.updateSortableState();
          });

          Livewire.on('widget-added', () => {
            this.isLoading = false;
            this.$nextTick(() => this.reinitSortable());
          });

          Livewire.on('widget-removed', () => {
            this.$nextTick(() => this.reinitSortable());
          });

          // Manejar navegación
          document.addEventListener('livewire:navigated', () => {
            this.$nextTick(() => this.reinitSortable());
          });
        },

        syncWithLivewire() {
          // Emitir estado inicial
          if (this.editMode) {
            this.$wire.toggleEditMode(this.editMode);
          }
        },

        toggleEditMode() {
          this.editMode = !this.editMode;
          this.$wire.toggleEditMode(this.editMode);
          this.updateSortableState();
        },

        updateSortableState() {
          if (this.sortable) {
            this.sortable.option('disabled', !this.editMode);
          }
        },

        async addWidget(type) {
          this.isLoading = true;
          try {
            await this.$wire.addWidget(type);
          } catch (error) {
            console.error('Error adding widget:', error);
            this.isLoading = false;
          }
        },

        initSortable() {
          const grid = this.$refs.dashboardGrid;
          if (!grid || grid.children.length === 0) return;

          if (this.sortable) {
            this.destroySortable();
          }

          try {
            this.sortable = new Sortable(grid, {
              animation: 300,
              easing: "cubic-bezier(0.4, 0, 0.2, 1)",
              disabled: !this.editMode,
              ghostClass: 'sortable-ghost',
              chosenClass: 'sortable-chosen',
              dragClass: 'sortable-drag',
              handle: '[data-drag-handle]',
              delay: this.pressHoldMs,
              delayOnTouchOnly: true,
              touchStartThreshold: 5,
              forceFallback: false,
              fallbackClass: 'sortable-fallback',
              filter: '.widget-remove-btn, .pointer-events-none',

              onStart: (evt) => {
                this.isDragging = true;
                this.draggedElement = evt.item;
                document.body.style.cursor = 'grabbing';
                evt.item.style.zIndex = '1000';
              },

              onEnd: (evt) => {
                this.isDragging = false;
                this.draggedElement = null;
                document.body.style.cursor = '';
                evt.item.style.zIndex = '';
                
                if (evt.oldIndex !== evt.newIndex) {
                  this.updateWidgetOrder();
                }
                
                this.recalcHeights();
              },

              onSort: () => {
                this.recalcHeights();
              }
            });
          } catch (error) {
            console.error('Error initializing sortable:', error);
          }
        },

        destroySortable() {
          if (this.sortable) {
            try {
              this.sortable.destroy();
            } catch (e) {
              console.warn('Error destroying sortable:', e);
            }
            this.sortable = null;
          }
        },

        reinitSortable() {
          this.destroySortable();
          this.$nextTick(() => this.initSortable());
        },

        updateWidgetOrder() {
          const grid = this.$refs.dashboardGrid;
          if (!grid) return;

          const widgetOrder = Array.from(grid.children)
            .map(el => el.dataset.widgetId)
            .filter(id => id && id !== 'undefined');
          
          if (widgetOrder.length > 0 && this.$wire?.updateWidgetPositions) {
            this.$wire.updateWidgetPositions(widgetOrder);
          }
        },

        setupHeightsObservers() {
          // Configurar observers para alturas responsivas
          this.recalcHeights();
          
          if (!this.resizeObserver) {
            this.resizeObserver = new ResizeObserver(() => {
              this.debouncedRecalcHeights();
            });
            
            const container = document.getElementById('dashboard-container');
            if (container) {
              this.resizeObserver.observe(container);
            }
          }
        },

        recalcHeights() {
          requestAnimationFrame(() => {
            const root = document.getElementById('dashboard-root');
            const container = document.getElementById('dashboard-container');
            const grid = this.$refs.dashboardGrid;
            
            if (root && container && grid) {
              const containerHeight = container.offsetHeight;
              const controlBarHeight = container.querySelector('.flex.justify-end')?.offsetHeight || 0;
              const gridHeight = containerHeight - controlBarHeight - 16; // 16px de padding
              
              root.style.setProperty('--gridH', `${gridHeight}px`);
            }
          });
        },

        debouncedRecalcHeights: _.debounce(function() {
          this.recalcHeights();
        }, 100),

        // Cleanup
        destroy() {
          this.destroySortable();
          if (this.resizeObserver) {
            this.resizeObserver.disconnect();
          }
        }
      }
    }

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof _.debounce === 'undefined') {
        _.debounce = function(func, wait) {
          let timeout;
          return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
          };
        };
      }
    });
  </script>
  @endpush
</div>