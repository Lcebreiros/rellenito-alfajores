# 🚀 Setup Rápido de Pusher - 5 minutos

## ✅ Ya está todo instalado y configurado!

Solo necesitas configurar Pusher y empezar a usar:

## Paso 1: Crear cuenta en Pusher (2 minutos)

1. Ve a https://pusher.com/
2. Regístrate gratis
3. Crea una app nueva
4. Selecciona: **Cluster: us-east-1** (Miami - mejor para Argentina)
5. Copia las credenciales

## Paso 2: Configurar .env (1 minuto)

Agrega esto en tu `.env`:

```env
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=tu_app_id
PUSHER_APP_KEY=tu_key
PUSHER_APP_SECRET=tu_secret
PUSHER_APP_CLUSTER=us-east-1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

## Paso 3: Limpiar cache (30 segundos)

```bash
php artisan config:clear
php artisan config:cache
```

## ¡Listo! 🎉

Ahora puedes:

### 1. Agregar notificaciones en cualquier parte:

```php
use App\Helpers\NotificationHelper;

// Cuando creas un pedido:
NotificationHelper::notifyNewOrder($userId, $order);

// Notificación personalizada:
NotificationHelper::send(
    userId: $userId,
    type: 'order',
    title: 'Pedido creado',
    message: 'Tu pedido #123 ha sido creado',
    data: ['order_id' => 123]
);
```

### 2. Usar la campana de notificaciones:

Agrega en tu navbar (resources/views/layouts/app.blade.php):

```blade
<livewire:notification-bell />
```

### 3. Ver la guía completa:

Lee `CHAT-SOPORTE-PUSHER-GUIA-COMPLETA.md` para:
- Implementar el chat completo
- Personalizar notificaciones
- Agregar más funcionalidades

## Plan gratuito de Pusher:

- ✅ 200,000 mensajes/día
- ✅ 100 conexiones simultáneas
- ✅ Perfecto para empezar

## Monitorear:

Ve a https://dashboard.pusher.com/ para ver:
- Conexiones activas
- Mensajes en tiempo real
- Debug logs

---

**¿Problemas?** Lee la sección Troubleshooting en `CHAT-SOPORTE-PUSHER-GUIA-COMPLETA.md`
