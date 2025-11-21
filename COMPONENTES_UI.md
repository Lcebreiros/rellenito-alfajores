# 🎨 Guía de Componentes UI/UX

Documentación completa de los componentes UI mejorados para Gestior.

---

## 📦 Componentes Disponibles

### 1. **x-svg-icon** - Sistema de Íconos SVG

Iconos SVG optimizados y consistentes en todo el proyecto.

#### Uso Básico:
```blade
<x-svg-icon name="search" size="5" />
<x-svg-icon name="user" size="6" class="text-indigo-600" />
<x-svg-icon name="check" size="4" stroke="2" />
```

#### Parámetros:
- `name` (requerido): Nombre del ícono
- `size` (opcional): Tamaño en unidades Tailwind (default: "5")
- `stroke` (opcional): Grosor del trazo (default: "1.5")
- `class` (opcional): Clases adicionales de Tailwind

#### Íconos Disponibles (30+):

**Navegación & Acciones:**
- `search`, `eye`, `edit`, `trash`, `user-plus`, `plus`, `x`, `check`
- `chevron-right`, `chevron-left`, `chevron-down`

**Estado & Alertas:**
- `exclamation`, `info`, `question`

**Negocios:**
- `document`, `box`, `chart`, `filter`, `download`, `calendar`, `cog`

**Social:**
- `bell`, `user`

#### Ejemplo en Vista:
```blade
{{-- Botón con ícono --}}
<button class="btn-primary">
  <x-svg-icon name="user-plus" size="5" />
  Nuevo Cliente
</button>

{{-- Input con ícono --}}
<div class="relative">
  <div class="absolute left-3 top-1/2 -translate-y-1/2">
    <x-svg-icon name="search" size="5" class="text-neutral-400" />
  </div>
  <input class="pl-10 ..." />
</div>
```

---

### 2. **x-empty-state** - Estados Vacíos

Estado vacío elegante con ilustración, mensaje y call-to-action.

#### Uso Básico:
```blade
<x-empty-state
  icon="user"
  title="No hay clientes aún"
  description="Comienza agregando tu primer cliente para gestionar tus ventas."
  :action-url="route('clients.create')"
  action-text="Crear primer cliente"
  action-icon="user-plus"
/>
```

#### Parámetros:
- `icon` (opcional): Ícono ilustrativo (default: "box")
- `title` (opcional): Título del mensaje
- `description` (opcional): Descripción explicativa
- `actionUrl` (opcional): URL del botón CTA
- `actionText` (opcional): Texto del botón
- `actionIcon` (opcional): Ícono del botón (default: "plus")

#### Contenido Custom (slot):
```blade
<x-empty-state icon="calendar" title="Sin eventos">
  <p class="text-sm text-neutral-500">
    Agrega un evento para empezar a organizar tu calendario.
  </p>
  <a href="#" class="btn-primary mt-4">Agregar Evento</a>
</x-empty-state>
```

---

### 3. **x-confirm-modal** - Modal de Confirmación

Modal de confirmación para acciones críticas (eliminar, cambios irreversibles).

#### Uso Básico:
```blade
{{-- Definir el modal --}}
<x-confirm-modal
  id="delete-client-modal"
  title="¿Eliminar cliente?"
  description="Esta acción no se puede deshacer. El cliente será eliminado permanentemente."
  confirm-text="Sí, eliminar"
  cancel-text="Cancelar"
  type="danger"
  wire:click="deleteClient"
/>

{{-- Botón que abre el modal --}}
<button @click="$dispatch('open-modal-delete-client-modal')">
  Eliminar
</button>
```

#### Parámetros:
- `id` (requerido): ID único del modal
- `title` (opcional): Título del modal (default: "¿Confirmar acción?")
- `description` (opcional): Descripción (default: "Esta acción no se puede deshacer.")
- `confirmText` (opcional): Texto botón confirmar (default: "Confirmar")
- `cancelText` (opcional): Texto botón cancelar (default: "Cancelar")
- `icon` (opcional): Ícono (default: "exclamation")
- `type` (opcional): Tipo visual: `danger`, `warning`, `info` (default: "danger")

#### Tipos Disponibles:
```blade
{{-- Peligro (rojo) - para eliminaciones --}}
<x-confirm-modal type="danger" ... />

{{-- Advertencia (amarillo) - para cambios importantes --}}
<x-confirm-modal type="warning" ... />

{{-- Información (azul) - para confirmaciones generales --}}
<x-confirm-modal type="info" ... />
```

#### Contenido Custom (slot):
```blade
<x-confirm-modal id="transfer-modal" title="Transferir fondos">
  <div class="space-y-2 text-sm">
    <p><strong>Origen:</strong> Cuenta Principal</p>
    <p><strong>Destino:</strong> Cuenta Secundaria</p>
    <p><strong>Monto:</strong> $1,500.00</p>
  </div>
</x-confirm-modal>
```

---

### 4. **x-tooltip** - Tooltips Informativos

Tooltips para ayudar a entender campos o funcionalidades complejas.

#### Uso Básico:
```blade
<x-tooltip text="Se enviará alerta cuando el stock llegue a este nivel" position="top">
  <x-svg-icon name="question" size="4" class="text-neutral-400 cursor-help" />
</x-tooltip>
```

#### Parámetros:
- `text` (requerido): Texto del tooltip
- `position` (opcional): Posición: `top`, `bottom`, `left`, `right` (default: "top")

#### Ejemplo en Formulario:
```blade
<label class="flex items-center gap-2">
  Stock mínimo
  <x-tooltip text="Cantidad mínima antes de recibir alertas" position="right">
    <x-svg-icon name="info" size="4" class="text-neutral-400" />
  </x-tooltip>
</label>
<input type="number" name="min_stock" ... />
```

#### Tooltip con Contenido Custom:
```blade
<x-tooltip position="bottom">
  <x-slot:text>
    <strong>Tip:</strong> Usa Ctrl+K para búsqueda rápida
  </x-slot:text>

  <button class="btn-secondary">
    ¿Atajos de teclado?
  </button>
</x-tooltip>
```

---

### 5. **x-breadcrumbs** - Navegación de Migas

Breadcrumbs para navegación jerárquica.

#### Uso Básico:
```blade
<x-breadcrumbs :items="[
  ['label' => 'Inicio', 'url' => route('dashboard')],
  ['label' => 'Productos', 'url' => route('products.index')],
  ['label' => 'Editar'],
]" />
```

#### Parámetros:
- `items` (requerido): Array de items con `label` y `url` (opcional)

#### Ejemplo en Vista:
```blade
@section('content')
<div class="max-w-screen-2xl mx-auto px-4">
  <x-breadcrumbs :items="[
    ['label' => 'Dashboard', 'url' => route('dashboard')],
    ['label' => 'Clientes', 'url' => route('clients.index')],
    ['label' => $client->name],
  ]" />

  <h1>{{ $client->name }}</h1>
  ...
</div>
@endsection
```

---

### 6. **x-toast** - Notificaciones Toast

Notificaciones temporales para feedback de acciones.

#### Uso con Sistema Global:

Ya incluido en `layouts/app.blade.php` con `<x-toast-container />`.

#### Mostrar Toast desde JavaScript:
```javascript
// Éxito
window.showToast('success', 'Cliente creado correctamente', 'Éxito');

// Error
window.showToast('error', 'No se pudo guardar el cliente', 'Error');

// Advertencia
window.showToast('warning', 'Revisa los campos marcados', 'Atención');

// Información
window.showToast('info', 'Los cambios se guardarán automáticamente', 'Info');
```

#### Desde Livewire:
```php
// En tu componente Livewire
$this->dispatch('toast', [
    'type' => 'success',
    'message' => 'Pedido actualizado correctamente',
    'title' => 'Éxito',
    'duration' => 5000
]);
```

#### Parámetros del Toast:
- `type`: `success`, `error`, `warning`, `info`
- `message`: Mensaje principal
- `title` (opcional): Título del toast
- `duration` (opcional): Duración en ms (default: 5000)

---

## 🎨 Clases CSS Nuevas

### Botones con Animaciones
```blade
{{-- Botón primario con bounce --}}
<button class="btn-primary">Guardar</button>

{{-- Botón secundario --}}
<button class="btn-secondary">Cancelar</button>

{{-- Botón peligro --}}
<button class="btn-danger">Eliminar</button>
```

**Efecto:** `active:scale-[0.98]` al hacer clic (efecto de presión)

---

### Enlaces Consistentes
```blade
{{-- Enlace principal con underline en hover --}}
<a href="#" class="link">Ver más</a>

{{-- Enlace muted sin underline --}}
<a href="#" class="link-muted">Cancelar</a>
```

---

### Cards con Hover
```blade
<div class="card-dark card-hover">
  <!-- Efecto de elevación y movimiento al hover -->
</div>
```

---

### Skeleton Mejorado con Shimmer
```blade
<div class="skeleton w-full h-32">
  <!-- Animación de shimmer automática -->
</div>

<div class="skeleton-text w-2/3">
  <!-- Línea de texto skeleton -->
</div>
```

---

### Inputs con Animación de Enfoque
```blade
<input class="input-dark input-focus" ... />
<!-- Efecto de scale y ring al enfocarse -->
```

---

## 📚 Ejemplos Completos

### Ejemplo 1: Lista con Estado Vacío

```blade
@if($items->count())
  <table>
    <!-- tabla con datos -->
  </table>
@else
  <x-empty-state
    icon="box"
    title="No hay productos"
    description="Agrega tu primer producto al inventario."
    :action-url="route('products.create')"
    action-text="Crear producto"
  />
@endif
```

---

### Ejemplo 2: Formulario con Tooltips

```blade
<form>
  <div class="space-y-4">
    <div>
      <label class="flex items-center gap-2">
        Precio de venta
        <x-tooltip text="Precio sin IVA incluido" position="right">
          <x-svg-icon name="info" size="4" class="text-neutral-400" />
        </x-tooltip>
      </label>
      <input type="number" name="price" class="input-dark input-focus" />
    </div>

    <div class="flex gap-2">
      <button type="submit" class="btn-primary">
        <x-svg-icon name="check" size="5" />
        Guardar
      </button>
      <a href="{{ route('products.index') }}" class="btn-secondary">
        Cancelar
      </a>
    </div>
  </div>
</form>
```

---

### Ejemplo 3: Acción Destructiva con Confirmación

```blade
{{-- Modal de confirmación --}}
<x-confirm-modal
  id="delete-order-{{ $order->id }}"
  title="¿Eliminar pedido #{{ $order->id }}?"
  description="Esta acción no se puede deshacer. El pedido será eliminado permanentemente."
  type="danger"
  wire:click="deleteOrder({{ $order->id }})"
/>

{{-- Botón que abre el modal --}}
<button
  @click="$dispatch('open-modal-delete-order-{{ $order->id }}')"
  class="btn-danger">
  <x-svg-icon name="trash" size="4" />
  Eliminar
</button>
```

---

### Ejemplo 4: Breadcrumbs en Vista Detalle

```blade
@section('content')
<div class="max-w-screen-2xl mx-auto px-4">
  {{-- Navegación --}}
  <x-breadcrumbs :items="[
    ['label' => 'Dashboard', 'url' => route('dashboard')],
    ['label' => 'Pedidos', 'url' => route('orders.index')],
    ['label' => 'Pedido #' . $order->id],
  ]" />

  {{-- Contenido --}}
  <div class="mt-4">
    <h1>Pedido #{{ $order->id }}</h1>
    ...
  </div>
</div>
@endsection
```

---

### Ejemplo 5: Feedback con Toast

```blade
{{-- En Livewire Component --}}
<div>
  <form wire:submit="save">
    <input wire:model="name" ... />
    <button type="submit" class="btn-primary">Guardar</button>
  </form>
</div>

@script
<script>
$wire.on('client-saved', () => {
    window.showToast('success', 'Cliente guardado correctamente', 'Éxito');
});

$wire.on('client-error', () => {
    window.showToast('error', 'Hubo un error al guardar', 'Error');
});
</script>
@endscript
```

---

## 🚀 Mejores Prácticas

### 1. **Íconos Consistentes**
- Usa `size="4"` para íconos inline en texto
- Usa `size="5"` para íconos en botones
- Usa `size="6"` o mayor para íconos destacados

### 2. **Estados Vacíos**
- Siempre incluye un CTA (call-to-action)
- Usa descripciones que guíen al usuario
- Elige íconos relevantes al contexto

### 3. **Modales de Confirmación**
- Usa `type="danger"` para eliminaciones
- Usa `type="warning"` para cambios importantes
- Siempre explica las consecuencias

### 4. **Tooltips**
- Usa para explicar campos no obvios
- Mantén el texto corto (1-2 líneas)
- Posiciona según el espacio disponible

### 5. **Toasts**
- Úsalos para feedback inmediato
- Duración default (5s) es suficiente
- Evita toasts para errores críticos (usa modales)

---

## 🎯 Checklist de Migración

Si tienes vistas existentes que quieres actualizar:

- [ ] Reemplazar Font Awesome con `<x-svg-icon>`
- [ ] Reemplazar estados vacíos simples con `<x-empty-state>`
- [ ] Agregar animaciones a botones (`btn-primary`, etc.)
- [ ] Agregar tooltips en campos complejos
- [ ] Implementar breadcrumbs en vistas nested
- [ ] Agregar confirmación en acciones destructivas
- [ ] Reemplazar alerts con sistema de toasts
- [ ] Usar clases `.link` para enlaces consistentes
- [ ] Agregar `transition-colors` a elementos interactivos

---

## 📝 Notas Finales

- Todos los componentes son **accesibles** (ARIA labels, keyboard navigation)
- Totalmente **compatible con dark mode**
- **Sin dependencias externas** (eliminamos Font Awesome)
- **Optimizados** para performance (Alpine.js liviano)
- **Responsive** en todos los tamaños de pantalla

**¡Disfruta construyendo interfaces profesionales! 🚀**
