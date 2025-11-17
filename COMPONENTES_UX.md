# Componentes UX/UI - Rellenito Alfajores

Documentación de los nuevos componentes de loading states, skeleton screens y mejoras de dark mode.

---

## 🎨 Componentes de Loading States

### 1. Loading Button
Botón con estado de carga integrado.

**Uso:**
```blade
<x-loading-button
    loading-text="Procesando..."
    class="w-full">
    Guardar cambios
</x-loading-button>
```

**Con Alpine.js:**
```html
<button type="submit"
        :disabled="loading"
        :class="{'opacity-75 cursor-not-allowed': loading}"
        class="w-full bg-indigo-600 text-white py-2 rounded">
  <svg x-show="loading" class="animate-spin h-4 w-4 mr-2">...</svg>
  <span x-show="!loading">Guardar</span>
  <span x-show="loading">Guardando...</span>
</button>
```

---

### 2. Loading Spinner
Spinner animado configurable.

**Uso:**
```blade
<x-loading-spinner size="md" color="indigo" />

<!-- Tamaños disponibles: sm, md, lg, xl -->
<!-- Colores disponibles: indigo, white, gray, primary -->
```

---

### 3. Loading Overlay
Overlay de pantalla completa con mensaje personalizable.

**Uso:**
```blade
<x-loading-overlay :show="loading" message="Procesando pedido..." />
```

**Con Alpine.js:**
```html
<div x-data="{ loading: false }">
    <x-loading-overlay :show="loading" message="Cargando..." />
    <button @click="loading = true">Cargar</button>
</div>
```

---

## 📦 Componentes Skeleton Screens

### 1. Skeleton Card
Card placeholder con animación de carga.

**Uso:**
```blade
<x-skeleton.card :rows="3" />
```

**Parámetros:**
- `rows`: Número de filas de contenido (default: 3)

---

### 2. Skeleton Table
Tabla placeholder.

**Uso:**
```blade
<x-skeleton.table :rows="5" :columns="4" />
```

**Parámetros:**
- `rows`: Número de filas (default: 5)
- `columns`: Número de columnas (default: 4)

---

### 3. Skeleton Grid
Grid de items (productos, cards, etc).

**Uso:**
```blade
<x-skeleton.grid :items="6" :columns="3" />
```

**Parámetros:**
- `items`: Número de items (default: 6)
- `columns`: Número de columnas (default: 3)

---

### 4. Skeleton Form
Formulario placeholder.

**Uso:**
```blade
<x-skeleton.form :fields="4" />
```

**Parámetros:**
- `fields`: Número de campos (default: 4)

---

### 5. Skeleton List
Lista de items.

**Uso:**
```blade
<x-skeleton.list :items="5" />
```

**Parámetros:**
- `items`: Número de items (default: 5)

---

## 🌗 Dark Mode Mejorado

### Clases CSS Utilitarias

#### Componentes de Card
```css
.card-dark        /* Card básico con dark mode */
.card-elevated    /* Card con elevación y sombras */
```

#### Textos
```css
.text-primary     /* Texto principal (900/100) */
.text-secondary   /* Texto secundario (700/300) */
.text-muted       /* Texto atenuado (500/400) */
.text-subtle      /* Texto sutil (400/500) */
```

#### Backgrounds
```css
.bg-surface       /* Superficie base */
.bg-elevated      /* Superficie elevada */
.bg-base          /* Fondo de página */
```

#### Borders
```css
.border-light     /* Borde claro */
.border-medium    /* Borde medio */
.divider-dark     /* Divisor con dark mode */
```

#### Inputs
```css
.input-dark       /* Input con dark mode completo */
```

#### Botones
```css
.btn-primary      /* Botón primario con dark mode */
.btn-secondary    /* Botón secundario con dark mode */
```

#### Skeleton
```css
.skeleton         /* Elemento skeleton animado */
.skeleton-text    /* Texto skeleton */
```

#### Transiciones
```css
.dark-transition  /* Transición suave para dark mode */
```

---

### Scrollbar Personalizado
```css
.scrollbar-thin   /* Scrollbar delgado con dark mode */
```

**Uso:**
```html
<div class="overflow-y-auto scrollbar-thin">
    <!-- Contenido con scroll -->
</div>
```

---

## 🎨 Paleta de Colores Dark Mode

### Colores Dark
```
dark-50  a dark-950  - Escala de grises optimizada
```

### Colores Primary
```
primary-50 a primary-950 - Indigo optimizado para dark mode
```

### Colores Especiales
```
dark-card      - #1a1d23 (Background de cards)
dark-elevated  - #212529 (Background elevado)
dark-border    - #2d3139 (Bordes)
```

### Sombras Dark Mode
```css
.shadow-dark-sm
.shadow-dark
.shadow-dark-md
.shadow-dark-lg
.shadow-dark-xl
```

---

## 📝 Ejemplos de Uso Completo

### Ejemplo 1: Formulario con Loading State
```html
<div x-data="{ loading: false }">
    <form @submit.prevent="loading = true; $el.submit()">
        <!-- Campos del formulario -->
        <input type="text" class="input-dark">

        <!-- Botón con loading -->
        <button type="submit"
                :disabled="loading"
                :class="{'opacity-75 cursor-not-allowed': loading}"
                class="btn-primary">
            <svg x-show="loading" class="animate-spin h-4 w-4 mr-2">...</svg>
            <span x-show="!loading">Guardar</span>
            <span x-show="loading">Guardando...</span>
        </button>
    </form>
</div>
```

---

### Ejemplo 2: Lista con Skeleton
```html
<div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 2000)">
    <!-- Skeleton mientras carga -->
    <div x-show="loading">
        <x-skeleton.list :items="5" />
    </div>

    <!-- Contenido real -->
    <div x-show="!loading" class="space-y-3">
        @foreach($items as $item)
            <div class="card-dark p-4">
                {{ $item->name }}
            </div>
        @endforeach
    </div>
</div>
```

---

### Ejemplo 3: Grid con Skeleton
```html
<div x-data="{ loading: true }">
    <div x-show="loading">
        <x-skeleton.grid :items="6" :columns="3" />
    </div>

    <div x-show="!loading" class="grid grid-cols-3 gap-6">
        <!-- Items reales -->
    </div>
</div>
```

---

## 🚀 Mejores Prácticas

### 1. Loading States
- Siempre deshabilita los botones durante loading
- Muestra feedback visual claro (spinner + texto)
- No uses múltiples loading states simultáneos
- Mantén el loading state mientras dura la operación

### 2. Skeleton Screens
- Usa skeletons que coincidan con el layout real
- No muestres skeletons por más de 3-5 segundos
- Usa transiciones suaves (x-transition)
- Mantén la estructura y espaciado del contenido real

### 3. Dark Mode
- Usa las clases utilitarias provistas
- Mantén contraste adecuado (WCAG AA mínimo)
- Prueba ambos modos durante desarrollo
- Usa `dark-transition` para cambios suaves

### 4. Accesibilidad
- Agrega `aria-busy="true"` durante loading
- Usa `aria-live` para anuncios dinámicos
- Mantén focus management adecuado
- Asegura contraste de colores (4.5:1 mínimo)

---

## 🔧 Compilación

Después de hacer cambios en CSS o componentes:

```bash
npm run build        # Producción
npm run dev          # Desarrollo con watch
```

---

## 📚 Referencias

- **Tailwind CSS**: https://tailwindcss.com/docs
- **Alpine.js**: https://alpinejs.dev/
- **Laravel Blade Components**: https://laravel.com/docs/11.x/blade#components

---

Generado para Rellenito Alfajores - Sistema de Gestión
