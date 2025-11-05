# Guía de Prueba - Notificaciones en Tiempo Real

## ✅ Cambios Desplegados en Producción

1. **BROADCAST_CONNECTION** configurado en `pusher`
2. **Pusher PHP SDK** instalado (v7.2.7)
3. **SupportController** actualizado para disparar eventos Pusher
4. **notifications-bell.blade.php** con soporte para tipo 'support'
5. **Ruta mark-as-read** actualizada para usar `user_notifications`
6. **Ruta de prueba** `/test-notification` agregada

## 🧪 Cómo Probar las Notificaciones

### Método 1: Endpoint de Prueba (Más Rápido)

#### Paso 1: Abrir la consola del navegador
1. Inicia sesión en https://gestior.com.ar
2. Abre DevTools (F12)
3. Ve a la pestaña **Console**

#### Paso 2: Verificar conexión a Pusher
En la consola deberías ver:
```
Pusher: Connecting to pusher
Pusher: Connection established
Pusher: Subscribed to private-user.{tu_user_id}
```

Si ves `Connection established`, Pusher está funcionando correctamente.

#### Paso 3: Ejecutar prueba desde consola
En la consola del navegador, ejecuta:

```javascript
fetch('/test-notification', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
}).then(r => r.json()).then(data => console.log('✅ Respuesta:', data));
```

#### Paso 4: Observar resultados

**Qué debería pasar:**

1. En la consola verás:
   ```
   ✅ Respuesta: {success: true, notification: {...}, message: "..."}
   🔔 Nueva notificación: {id: "...", type: "test", title: "Notificación de prueba", ...}
   ```

2. La campana de notificaciones debería:
   - Incrementar el contador en tiempo real (sin recargar)
   - Mostrar el badge con número de notificaciones no leídas
   - Después de 2 segundos, recargará la página automáticamente

3. Al abrir la campana, verás la notificación de prueba con:
   - Icono púrpura (tipo test)
   - Título: "Notificación de prueba"
   - Mensaje con la hora actual

### Método 2: Crear Ticket de Soporte (Prueba Real)

#### Configuración:
- **Usuario A**: Cuenta normal (no master)
- **Usuario B**: Cuenta master
- **Navegadores**: Chrome (Usuario A) y Firefox/Edge (Usuario B)

#### Paso 1: Usuario A crea ticket
1. Usuario A inicia sesión
2. Va a Soporte
3. Crea un nuevo ticket con:
   - Tipo: Problema
   - Asunto: "Prueba de notificaciones"
   - Mensaje: "Verificando notificaciones en tiempo real"
4. Envía el ticket

#### Paso 2: Verificar notificación en Usuario B
**Usuario B (master) debería ver:**
1. **SIN recargar la página**: El contador de la campana incrementa
2. Al hacer clic en la campana, aparece:
   - Icono ámbar (tipo support)
   - Título: "Nuevo ticket de soporte"
   - Mensaje: "Prueba de notificaciones"
3. Al hacer clic en la notificación, va al ticket

#### Paso 3: Usuario B responde
1. Usuario B (master) responde al ticket
2. **Usuario A** debería ver notificación en tiempo real:
   - Título: "Respuesta en tu ticket"
   - Mensaje: "Han respondido tu ticket: Prueba de notificaciones"

## 🐛 Si No Funciona

### Problema: No aparece nada en la consola sobre Pusher

**Causa**: Echo no está inicializando correctamente

**Solución**:
1. Verifica que `resources/js/app.js` importe `bootstrap.js`:
   ```javascript
   import './bootstrap';
   ```

2. Reconstruye assets:
   ```bash
   npm run build
   ```

3. Sube los assets a producción:
   ```bash
   # En local
   npm run build

   # En servidor
   git pull origin main
   ```

### Problema: Sale "Connection refused" o "Connection failed"

**Causa**: Credenciales de Pusher incorrectas o cluster incorrecto

**Solución**:
1. Verifica en `.env`:
   ```env
   PUSHER_APP_CLUSTER=sa1  # Debe ser sa1 (São Paulo)
   ```

2. Verifica en el código compilado de Vite que las variables estén correctas:
   ```bash
   grep -i pusher public/build/assets/*.js | head -5
   ```

### Problema: Dice "Connected" pero no llegan eventos

**Causa**: Broadcasting no está configurado correctamente

**Solución**:
1. Verifica `.env`:
   ```bash
   grep BROADCAST_CONNECTION .env
   # Debe mostrar: BROADCAST_CONNECTION=pusher
   ```

2. Limpia cache:
   ```bash
   php artisan config:cache
   php artisan route:cache
   ```

3. Verifica que el canal privado esté autorizado en `routes/channels.php`:
   ```php
   Broadcast::channel('user.{userId}', function ($user, $userId) {
       return (int) $user->id === (int) $userId;
   });
   ```

### Problema: La notificación se crea en DB pero no aparece en la campana

**Causa**: El componente lee de una tabla diferente

**Solución**: Verificar que `notifications-bell.blade.php` use `UserNotification`:
```php
$latest = \App\Models\UserNotification::forUser($user?->id)->latest()->take(10)->get();
```

### Problema: Error 403 al suscribirse al canal privado

**Causa**: Autenticación de canal fallando

**Solución**:
1. Verifica que `routes/channels.php` tenga la autorización correcta
2. Verifica que el token CSRF sea válido:
   ```javascript
   console.log(document.querySelector('meta[name="csrf-token"]')?.content);
   ```

## 📊 Verificar en Pusher Dashboard

1. Ve a: https://dashboard.pusher.com/apps/2073269/debug_console
2. Inicia sesión con las credenciales de Pusher
3. Ejecuta una prueba (crear ticket o usar `/test-notification`)
4. En el Debug Console deberías ver:
   ```
   Channel: private-user.123
   Event: notification.new
   Data: {"id": "...", "type": "support", ...}
   ```

Si ves el evento en el dashboard pero no en el frontend, el problema está en la configuración de Echo.

## 📝 Logs Útiles

### Laravel Log (Backend)
```bash
tail -f storage/logs/laravel.log
```

### Pusher Log (Habilitar debug en bootstrap.js)
```javascript
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    encrypted: true,
    enabledTransports: ['ws', 'wss'],
    // Habilitar debug
    logToConsole: true,
});

// O configurar Pusher directamente
Pusher.logToConsole = true;
```

## ✅ Checklist de Verificación

Antes de reportar un problema, verifica:

- [ ] `BROADCAST_CONNECTION=pusher` en `.env`
- [ ] Pusher PHP SDK instalado (`composer.json` tiene `pusher/pusher-php-server`)
- [ ] Credenciales de Pusher correctas en `.env`
- [ ] `php artisan config:cache` ejecutado
- [ ] Navegador muestra "Pusher: Connection established" en consola
- [ ] Endpoint `/test-notification` responde con `success: true`
- [ ] Componente `notifications-bell.blade.php` tiene el script con `window.Echo.private()`
- [ ] Tabla `user_notifications` tiene registros

## 🎯 Resultado Esperado

**Cuando funciona correctamente:**

1. ✅ Usuario crea ticket → Master ve notificación **instantáneamente** (sin recargar)
2. ✅ Master responde → Usuario ve notificación **instantáneamente**
3. ✅ Contador de campana se actualiza en tiempo real
4. ✅ Notificación del navegador aparece (si se dieron permisos)
5. ✅ Después de 2 segundos, la página recarga para mostrar la lista actualizada
6. ✅ Al hacer clic en notificación, marca como leída y redirige al ticket

## 🔗 Recursos

- **Pusher Dashboard**: https://dashboard.pusher.com/apps/2073269
- **Laravel Broadcasting Docs**: https://laravel.com/docs/11.x/broadcasting
- **Pusher Debug Console**: https://dashboard.pusher.com/apps/2073269/debug_console
- **Documentación completa**: Ver `NOTIFICACIONES-TIEMPO-REAL-PUSHER.md`
