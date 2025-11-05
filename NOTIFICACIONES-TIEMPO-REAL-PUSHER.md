# Notificaciones en Tiempo Real con Pusher - SOLUCIONADO

## Problema Resuelto

Las notificaciones de soporte no aparecían en tiempo real en la campana del header.

### Causas Identificadas:

1. **❌ BROADCAST_CONNECTION estaba en `log`**: Los eventos no se enviaban a Pusher, solo se escribían en logs
2. **❌ Pusher PHP SDK no estaba instalado**: Faltaba el paquete `pusher/pusher-php-server`
3. **❌ Mismatch de tablas**: `SupportController` usaba `$user->notify()` que guarda en tabla `notifications`, pero el componente campana lee de tabla `user_notifications`
4. **❌ Faltaba manejo de tipo 'support'**: El componente campana no tenía código para mostrar notificaciones de tipo 'support'

## Solución Implementada

### 1. Instalado Pusher PHP SDK
```bash
composer require pusher/pusher-php-server
```

### 2. Configurado Broadcasting en .env
```env
BROADCAST_CONNECTION=pusher  # ← Cambiado de 'log' a 'pusher'

PUSHER_APP_ID=2073269
PUSHER_APP_KEY=a58d27031ee6993506cc
PUSHER_APP_SECRET=63eb97437147699cfd4c
PUSHER_APP_CLUSTER=sa1
PUSHER_SCHEME=https
```

### 3. Actualizado SupportController.php

**ANTES** (usaba sistema de notificaciones de Laravel):
```php
// Guardaba en tabla 'notifications'
$m->notify(new SupportReplied($message));
```

**AHORA** (usa tabla user_notifications + Pusher):
```php
// 1. Crear notificación en user_notifications
$notification = \App\Models\UserNotification::create([
    'user_id' => $m->id,
    'type' => 'support',
    'title' => 'Nuevo ticket de soporte',
    'message' => $data['subject'] ?? 'Sin asunto',
    'data' => [
        'ticket_id' => $ticket->id,
        'url' => route('support.show', $ticket),
    ],
]);

// 2. Disparar evento de Pusher
broadcast(new \App\Events\NewNotification($notification))->toOthers();
```

**Aplicado en**:
- `store()` método (líneas 60-79): Cuando se crea un nuevo ticket
- `reply()` método (líneas 110-149): Cuando master o usuario responde

### 4. Actualizado notifications-bell.blade.php

Agregado manejo para tipo `'support'` (líneas 99-120):

```php
@elseif($n->type === 'support')
  <a href="{{ $n->data['url'] ?? route('support.show', $n->data['ticket_id'] ?? 0) }}"
     @click.prevent="markAsRead('{{ $n->id }}'); setTimeout(() => window.location.href = '{{ $n->data['url'] ?? '#' }}', 100)"
     class="block">
    <div class="flex items-start gap-2">
      <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" ...>
          <!-- Icono de soporte -->
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-sm font-medium">{{ $n->title }}</div>
        <div class="text-xs line-clamp-2">{{ $n->message }}</div>
      </div>
    </div>
  </a>
```

## Arquitectura de Notificaciones

### Flujo Completo:

```
1. Usuario crea ticket de soporte
   ↓
2. SupportController::store()
   ↓
3. UserNotification::create() → Guarda en DB
   ↓
4. broadcast(new NewNotification($notification))
   ↓
5. Pusher recibe evento
   ↓
6. Frontend Echo escucha en canal 'user.{id}'
   ↓
7. Event listener en notifications-bell.blade.php
   ↓
8. Incrementa contador y refresca lista
```

### Canales Privados:

Cada usuario tiene su canal privado:
```javascript
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

    // Recargar lista
    setTimeout(() => {
      window.location.reload();
    }, 2000);
  });
```

## Tipos de Notificaciones Soportados

El componente `notifications-bell.blade.php` ahora maneja:

1. **`order`** - Notificaciones de pedidos (azul, icono shopping-bag)
2. **`chat`** - Mensajes de chat (verde, icono message)
3. **`support`** - Tickets de soporte (ámbar, icono support) ← **NUEVO**
4. **`test`** - Notificaciones de prueba (púrpura, icono flask)
5. **genérico** - Cualquier otro tipo (sin icono especial)

## Cómo Probar

### 1. Abrir Consola del Navegador

En el frontend, abre DevTools (F12) y ve a la pestaña Console.

### 2. Verificar Conexión a Pusher

Deberías ver mensajes como:
```
Pusher: Connecting to pusher
Pusher: Connection established
Pusher: Subscribed to private-user.123
```

### 3. Crear un Ticket de Soporte

Como usuario no-master:
1. Ve a la sección de Soporte
2. Crea un nuevo ticket
3. Observa la campana de notificaciones del master

### 4. Responder a un Ticket

Como master:
1. Abre un ticket existente
2. Responde al ticket
3. El usuario original debería ver la notificación en tiempo real

### 5. Verificar en Consola

Al recibir una notificación, deberías ver:
```
🔔 Nueva notificación: {
  id: "...",
  type: "support",
  title: "Nuevo ticket de soporte",
  message: "...",
  data: { ticket_id: 123, url: "..." }
}
```

## Debug de Pusher

### Ver eventos en Pusher Dashboard:

1. Ve a https://dashboard.pusher.com/
2. Selecciona tu app (ID: 2073269)
3. Ve a "Debug Console"
4. Crea un ticket y verás los eventos en tiempo real

### Verificar canales activos:

En la consola del navegador:
```javascript
// Ver canales suscritos
Object.keys(Echo.connector.channels)

// Ver estado de conexión
Echo.connector.pusher.connection.state
```

### Ver logs de Laravel:

```bash
tail -f storage/logs/laravel.log | grep -i pusher
```

## Archivos Modificados

### Backend:
- ✅ `app/Http/Controllers/SupportController.php` - Cambios en `store()` y `reply()`
- ✅ `app/Events/NewNotification.php` - Ya existía, correctamente configurado
- ✅ `.env` - BROADCAST_CONNECTION cambiado a 'pusher'

### Frontend:
- ✅ `resources/views/components/notifications-bell.blade.php` - Agregado tipo 'support'
- ✅ `resources/js/bootstrap.js` - Ya tenía configuración de Echo (no modificado)

### Dependencias:
- ✅ `composer.json` - Agregado `pusher/pusher-php-server: ^7.2`

## Comandos Ejecutados en Producción

```bash
# 1. Instalar Pusher PHP SDK
composer require pusher/pusher-php-server

# 2. Cambiar BROADCAST_CONNECTION
sed -i 's/BROADCAST_CONNECTION=log/BROADCAST_CONNECTION=pusher/' .env

# 3. Limpiar cache
php artisan config:cache
php artisan view:clear
php artisan cache:clear

# 4. Subir archivos modificados
scp -P 65002 app/Http/Controllers/SupportController.php u590843796@89.116.115.91:...
scp -P 65002 resources/views/components/notifications-bell.blade.php u590843796@89.116.115.91:...
```

## Estado Final

✅ **Pusher PHP SDK**: Instalado (v7.2.7)
✅ **BROADCAST_CONNECTION**: Configurado en 'pusher'
✅ **Eventos de broadcasting**: Funcionando
✅ **UserNotification**: Creándose correctamente en DB
✅ **Campana de notificaciones**: Con soporte para tipo 'support'
✅ **Echo listener**: Ya configurado en el componente

## Próximos Pasos (Opcional)

### 1. Implementar Chat en Vivo

Actualmente las notificaciones funcionan, pero la vista de chat no se actualiza en vivo. Para implementarlo:

Crear componente Livewire `SupportChat`:
```php
class SupportChat extends Component
{
    public SupportTicket $ticket;

    protected $listeners = ['echo-private:support-chat.{ticket},MessageSent' => 'messageReceived'];

    public function messageReceived($data)
    {
        $this->ticket->refresh();
        $this->dispatch('scroll-to-bottom');
    }
}
```

### 2. Marcar Notificaciones como Leídas vía AJAX

Actualmente al hacer clic en una notificación, recarga la página. Se podría mejorar para que sea más fluido:

```javascript
markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/mark-as-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
            'Content-Type': 'application/json',
        }
    }).then(response => response.json())
      .then(data => {
          this.unreadCount--;
          // No recargar, solo actualizar UI
      });
}
```

### 3. Solicitar Permiso de Notificaciones del Navegador

Agregar en el layout principal:
```javascript
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
```

## Recursos

- **Pusher Dashboard**: https://dashboard.pusher.com/apps/2073269
- **Laravel Broadcasting Docs**: https://laravel.com/docs/11.x/broadcasting
- **Laravel Echo Docs**: https://laravel.com/docs/11.x/broadcasting#client-side-installation
- **Pusher PHP Server SDK**: https://github.com/pusher/pusher-http-php

## Solución de Problemas

### "Class Pusher\Pusher not found"
```bash
composer require pusher/pusher-php-server
```

### Notificaciones no aparecen en tiempo real
```bash
# Verificar .env
grep BROADCAST_CONNECTION .env  # Debe ser 'pusher'

# Limpiar cache
php artisan config:cache
```

### Eventos no se disparan
```bash
# Ver logs
tail -f storage/logs/laravel.log

# Verificar en Pusher Dashboard
# https://dashboard.pusher.com/apps/2073269/debug_console
```

### Frontend no se conecta a Pusher
```javascript
// En DevTools Console
Echo.connector.pusher.connection.state  // Debe ser 'connected'
```
