# Configuración de Email para Rellenito

## Estado actual

Actualmente, el sistema está configurado con `MAIL_MAILER=log`, lo que significa que los emails se guardan en `storage/logs/laravel.log` en lugar de enviarse realmente.

## Notificaciones por email implementadas

### 1. Sistema de Soporte
- ✅ **Nueva respuesta en reclamo** (`SupportReplied`)
  - Se envía cuando hay una nueva respuesta en un ticket de soporte
  - Incluye: asunto, tipo, estado actual y extracto del mensaje

- ✅ **Cambio de estado de reclamo** (`SupportStatusChanged`)
  - Se envía cuando un administrador cambia el estado de un ticket
  - Estados: nuevo, en_proceso, solucionado
  - Incluye mensaje personalizado según el nuevo estado

### 2. Pedidos Agendados (Próximamente)
- ⏳ **Recordatorio de pedido agendado**
  - Se enviará un día antes del pedido programado
  - Incluirá detalles del encargo y productos

## Configuración para producción

### Opción 1: Gmail (recomendado para pruebas)

Editar `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-contraseña-de-aplicacion
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="Rellenito Alfajores"
```

**Importante:** Debes generar una "Contraseña de aplicación" en Google:
1. Ve a https://myaccount.google.com/security
2. Activa verificación en 2 pasos
3. Genera una contraseña de aplicación
4. Usa esa contraseña en `MAIL_PASSWORD`

### Opción 2: Mailtrap (recomendado para desarrollo)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu-username-de-mailtrap
MAIL_PASSWORD=tu-password-de-mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@rellenito.local
MAIL_FROM_NAME="Rellenito Alfajores"
```

### Opción 3: SendGrid (recomendado para producción)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=tu-api-key-de-sendgrid
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="Rellenito Alfajores"
```

### Opción 4: Amazon SES (producción alta escala)

```env
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="Rellenito Alfajores"

AWS_ACCESS_KEY_ID=tu-access-key
AWS_SECRET_ACCESS_KEY=tu-secret-key
AWS_DEFAULT_REGION=us-east-1
```

## Probar el envío de emails

### 1. Desde tinker
```bash
php artisan tinker
```

```php
// Crear un usuario de prueba
$user = User::first();

// Enviar email de prueba
$user->notify(new \Illuminate\Notifications\Messages\SimpleMessage(
    'Prueba de Email',
    'Este es un email de prueba desde Rellenito'
));
```

### 2. Probar notificación de soporte
```php
// Desde tinker
$ticket = SupportTicket::first();
$message = $ticket->messages()->first();
$ticket->user->notify(new \App\Notifications\SupportReplied($message));
```

### 3. Ver logs de email (si usas MAIL_MAILER=log)
```bash
tail -f storage/logs/laravel.log | grep -A 50 "Mail"
```

## Verificar que los emails se envíen

1. Cambiar la configuración en `.env`
2. Limpiar cache de configuración:
   ```bash
   php artisan config:clear
   ```
3. Crear un reclamo de soporte
4. Responder al reclamo
5. Verificar que llegue el email

## Troubleshooting

### Error: "Failed to authenticate"
- Verifica usuario y contraseña
- Si usas Gmail, asegúrate de usar contraseña de aplicación

### Error: "Connection refused"
- Verifica el puerto (587 para TLS, 465 para SSL)
- Verifica que tu servidor pueda hacer conexiones salientes

### No llegan los emails
1. Revisa `storage/logs/laravel.log` para errores
2. Verifica que la carpeta de spam
3. Prueba con `MAIL_MAILER=log` para ver el contenido

### Emails en cola no se envían
```bash
# Procesar la cola manualmente
php artisan queue:work

# O configurar supervisor en producción
```

## Comandos útiles

```bash
# Limpiar cache de configuración
php artisan config:clear

# Ver la configuración actual de mail
php artisan tinker
>>> config('mail')

# Enviar email de prueba
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

## Personalización de plantillas

Las plantillas de email se pueden personalizar en:
- `resources/views/vendor/notifications/email.blade.php` (si publicas las vistas)

Para publicar las vistas:
```bash
php artisan vendor:publish --tag=laravel-notifications
```

## Seguridad

⚠️ **Importante:**
- Nunca subas `.env` a Git
- Usa variables de entorno en producción
- Para producción, usa un servicio dedicado (SendGrid, SES, Mailgun)
- Configura SPF, DKIM y DMARC en tu dominio

## Monitoreo

Recomendaciones para producción:
- Usa un servicio de email que provea analytics (SendGrid, Mailgun)
- Monitorea la tasa de rebote (bounce rate)
- Configura webhooks para trackear entregas
- Implementa rate limiting para evitar spam
