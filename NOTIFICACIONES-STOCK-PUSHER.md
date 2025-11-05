# Notificaciones de Stock en Tiempo Real con Pusher ✅

## Estado Actual

✅ **FUNCIONANDO** - Las notificaciones de stock se envían en tiempo real vía Pusher

## Configuración Actual

### 1. Evento: `NewNotification`

**Archivo**: `app/Events/NewNotification.php`

```php
class NewNotification implements ShouldBroadcastNow  // ← Clave: ShouldBroadcastNow
{
    public $notification;

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->notification->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.new';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,  // 'low_stock' o 'out_of_stock'
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'data' => $this->notification->data,
            'created_at' => $this->notification->created_at->toISOString(),
        ];
    }
}
```

**Clave**: Usa `ShouldBroadcastNow` para emitir inmediatamente sin depender de queue workers.

### 2. Observer: `ProductObserver`

**Archivo**: `app/Observers/ProductObserver.php`

El observer escucha cambios en el stock del producto y dispara notificaciones:

#### Stock Bajo (`low_stock`)

Se dispara cuando:
- El stock **baja del umbral** configurado por el usuario
- Solo si antes estaba **por encima** del umbral

```php
// Líneas 58-76
if ($user->notify_low_stock && $newStock > 0 && $newStock <= $user->low_stock_threshold) {
    if ($oldStock > $user->low_stock_threshold) {
        // Crear notificación en DB
        $n = UserNotification::create([
            'user_id' => $user->id,
            'type' => 'low_stock',
            'title' => 'Stock bajo',
            'message' => "{$product->name} tiene {$newStock} unidades (umbral: {$user->low_stock_threshold})",
            'data' => [
                'product_id' => $product->id,
                'url' => route('stock.show', $product->id),
            ],
        ]);

        // Disparar evento Pusher
        broadcast(new NewNotification($n))->toOthers();
    }
}
```

#### Sin Stock (`out_of_stock`)

Se dispara cuando:
- El stock llega a **0 unidades**
- Solo si antes tenía stock

```php
// Líneas 39-55
if ($user->notify_out_of_stock && $newStock === 0 && $oldStock > 0) {
    $n = UserNotification::create([
        'user_id' => $user->id,
        'type' => 'out_of_stock',
        'title' => 'Producto sin stock',
        'message' => "Sin stock: {$product->name} se ha quedado sin unidades",
        'data' => [
            'product_id' => $product->id,
            'url' => route('stock.show', $product->id),
        ],
    ]);

    broadcast(new NewNotification($n))->toOthers();
}
```

### 3. Frontend: Componente Campana

**Archivo**: `resources/views/components/notifications-bell.blade.php`

Ya tiene soporte para ambos tipos de notificaciones de stock:

#### Low Stock (Líneas 123-141)
```blade
@elseif($n->type === 'low_stock')
  <a href="{{ $n->data['url'] ?? route('stock.show', $n->data['product_id'] ?? 0) }}"
     @click.prevent="markAsRead('{{ $n->id }}'); setTimeout(() => window.location.href = '{{ $n->data['url'] ?? '#' }}', 100)"
     class="block">
    <div class="flex items-start gap-2">
      <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
        <i class="fas fa-triangle-exclamation text-amber-600 dark:text-amber-400 text-sm"></i>
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-sm font-medium">{{ $n->title }}</div>
        <div class="text-xs line-clamp-2">{{ $n->message }}</div>
      </div>
    </div>
  </a>
```

#### Out of Stock (Líneas 143-162)
```blade
@elseif($n->type === 'out_of_stock')
  <a href="{{ $n->data['url'] ?? route('stock.show', $n->data['product_id'] ?? 0) }}"
     @click.prevent="markAsRead('{{ $n->id }}'); setTimeout(() => window.location.href = '{{ $n->data['url'] ?? '#' }}', 100)"
     class="block">
    <div class="flex items-start gap-2">
      <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
        <i class="fas fa-circle-xmark text-rose-600 dark:text-rose-400 text-sm"></i>
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-sm font-medium">{{ $n->title }}</div>
        <div class="text-xs line-clamp-2">{{ $n->message }}</div>
      </div>
    </div>
  </a>
```

**Íconos**:
- 🔺 **Low Stock**: Triángulo de advertencia ámbar
- ❌ **Out of Stock**: X en círculo rojo

### 4. Listener de Pusher

El componente ya tiene el listener activo (líneas 174-196):

```javascript
document.addEventListener('DOMContentLoaded', function() {
  if (window.Echo) {
    window.Echo.private('user.{{ auth()->id() }}')
      .listen('.notification.new', (data) => {
        console.log('🔔 Nueva notificación:', data);

        // Incrementar contador
        window.dispatchEvent(new CustomEvent('notification-received'));

        // Mostrar notificación del navegador
        if ('Notification' in window && Notification.permission === 'granted') {
          new Notification(data.title, {
            body: data.message,
            icon: '/favicon.ico',
          });
        }

        // Recargar la lista después de 2 segundos
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      });
  }
});
```

## Flujo Completo

1. **Usuario actualiza stock** de un producto (vía cualquier método)
2. **ProductObserver** detecta el cambio en `updated()` o `created()`
3. **Se evalúan condiciones**:
   - ¿El stock cambió?
   - ¿Está por debajo del umbral? (low_stock)
   - ¿Llegó a cero? (out_of_stock)
4. **Se crea UserNotification** en la base de datos
5. **Se dispara evento Pusher** con `broadcast(new NewNotification($n))`
6. **Frontend recibe evento** en el canal `user.{id}`
7. **Contador incrementa** sin recargar la página
8. **Notificación del navegador** aparece (opcional)
9. **Página recarga** después de 2 segundos para mostrar la notificación

## Configuración de Usuario

Cada usuario puede configurar:

### Umbrales de Stock

- **`low_stock_threshold`**: Número de unidades para considerar "stock bajo" (default: 5)
- **`notify_low_stock`**: Boolean - Activar/desactivar notificaciones de stock bajo
- **`notify_out_of_stock`**: Boolean - Activar/desactivar notificaciones sin stock

### Dónde Configurar

Panel de Settings → Notificaciones de Stock

**Campos en tabla `users`**:
```sql
low_stock_threshold INT DEFAULT 5
notify_low_stock BOOLEAN DEFAULT true
notify_out_of_stock BOOLEAN DEFAULT true
```

## Usuarios Notificados

El sistema determina quién debe recibir notificaciones:

1. **Propietario del producto** (`product.user_id`)
2. **Empresa padre** (si el propietario es una sucursal)
3. **Usuario actual** (si no hay propietario definido)
4. **Usuarios con notificaciones activas** (fallback para productos legacy)

## Cómo Probar

### Prueba Manual

1. **Configura tus umbrales**:
   - Ve a Settings → Notificaciones
   - Establece umbral de stock bajo (ej: 5)
   - Activa ambas notificaciones

2. **Edita un producto**:
   - Ve a Stock o Productos
   - Selecciona un producto con stock > 5
   - Reduce el stock a 3 (por debajo del umbral)
   - Guarda

3. **Observa la campana**:
   - **Deberías ver**: Contador incrementa sin recargar
   - **En consola**: `🔔 Nueva notificación: {type: "low_stock", ...}`
   - **Después de 2s**: Página recarga y muestra la notificación

4. **Prueba sin stock**:
   - Edita el mismo producto
   - Cambia stock a 0
   - Guarda

5. **Observa la campana**:
   - **Deberías ver**: Notificación de "Producto sin stock" en rojo

### Prueba con Console

```javascript
// En DevTools Console
Echo.connector.pusher.connection.state
// Esperado: "connected"

// Simular cambio de stock desde backend (Tinker)
php artisan tinker
>>> $product = App\Models\Product::find(1);
>>> $product->stock = 3;
>>> $product->save();
```

## Solución de Problemas

### Las notificaciones NO llegan en tiempo real

**Verificar**:

1. **Pusher conectado**:
   ```javascript
   Echo.connector.pusher.connection.state === 'connected'
   ```

2. **Observer registrado** en `AppServiceProvider`:
   ```php
   Product::observe(ProductObserver::class);
   ```

3. **Evento usa ShouldBroadcastNow**:
   ```php
   class NewNotification implements ShouldBroadcastNow
   ```

4. **BROADCAST_CONNECTION es pusher**:
   ```bash
   grep BROADCAST_CONNECTION .env
   # Output: BROADCAST_CONNECTION=pusher
   ```

### Las notificaciones llegan pero no aparecen en la campana

**Verificar**:

1. **Tipo correcto** en UserNotification:
   ```php
   'type' => 'low_stock'  // o 'out_of_stock'
   ```

2. **Componente tiene caso para el tipo**:
   ```blade
   @elseif($n->type === 'low_stock')
   ```

### Las notificaciones se duplican

**Causa**: El observer se ejecuta múltiples veces

**Solución**: Verificar que solo cambie el stock:
```php
if (!$product->wasChanged('stock')) {
    return;
}
```

## Notificaciones Email (Opcional)

Actualmente desactivadas para evitar errores SMTP. Para activarlas:

**Ver**: `app/Notifications/LowStockAlert.php` y `app/Notifications/OutOfStockAlert.php`

```php
public function via(object $notifiable): array
{
    return ['database', 'mail'];  // Agregar 'mail'
}
```

**Requiere**: Configuración SMTP correcta en `.env`

## Archivos Involucrados

### Backend
- ✅ `app/Events/NewNotification.php` - Evento Pusher
- ✅ `app/Observers/ProductObserver.php` - Lógica de detección
- ✅ `app/Models/UserNotification.php` - Modelo de notificaciones
- ✅ `app/Notifications/LowStockAlert.php` - Notificación Laravel (email)
- ✅ `app/Notifications/OutOfStockAlert.php` - Notificación Laravel (email)

### Frontend
- ✅ `resources/views/components/notifications-bell.blade.php` - Campana + Listener
- ✅ `resources/js/bootstrap.js` - Inicialización de Echo

### Configuración
- ✅ `.env` - BROADCAST_CONNECTION=pusher
- ✅ `routes/channels.php` - Autenticación de canales privados

## Diferencia Clave con el Problema Anterior

**Antes** (no funcionaba):
- Evento usaba `ShouldBroadcast` (encolado)
- Requería queue worker activo
- Hostinger compartido no permite workers persistentes

**Ahora** (funciona):
- Evento usa `ShouldBroadcastNow` (inmediato)
- No requiere queue workers
- Se dispara instantáneamente al guardar el producto

## Resumen

✅ Las notificaciones de stock funcionan **exactamente igual** que las de soporte
✅ Usan el **mismo evento** (`NewNotification`)
✅ Usan el **mismo listener** en la campana
✅ Usan el **mismo canal** (`user.{id}`)
✅ Todo está **listo y funcionando**

**No se requieren cambios adicionales** - El sistema de notificaciones de stock ya está completamente integrado con Pusher.
