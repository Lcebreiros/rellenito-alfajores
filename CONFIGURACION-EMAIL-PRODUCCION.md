# Configuración de Email en Producción

## Problema Resuelto

El error ocurría porque las notificaciones de soporte intentaban enviar emails a través de un servidor SMTP que no existe o no está accesible:

```
Connection could not be established with host "ssl://mail.gestior.com.ar:465"
```

## Solución Actual (Notificaciones sin Email)

Se han actualizado las notificaciones de soporte para que **solo usen la base de datos** y no intenten enviar emails:

### Archivos Modificados:
1. ✅ `app/Notifications/SupportReplied.php`
2. ✅ `app/Notifications/SupportStatusChanged.php`

**Cambio:**
```php
// ANTES
public function via(object $notifiable): array
{
    return ['database', 'mail'];
}

// AHORA
public function via(object $notifiable): array
{
    return ['database']; // Solo base de datos, sin email
}
```

**Resultado:**
- ✅ Las notificaciones aparecen en la campana 🔔
- ✅ No se intentan enviar emails
- ✅ No hay error 500
- ✅ Los tickets se crean correctamente

## Si Necesitas Emails en el Futuro

### Opción 1: Gmail (Recomendado para desarrollo/testing)

En el `.env` de producción:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tucuenta@gmail.com
MAIL_PASSWORD=tu_app_password  # Ver nota abajo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tucuenta@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Nota Gmail:** Debes generar una "App Password" en:
1. Ir a https://myaccount.google.com/security
2. Activar verificación en 2 pasos
3. Generar "App Password" en "Seguridad"
4. Usar esa contraseña en `MAIL_PASSWORD`

### Opción 2: Hostinger Email

Si tu hosting Hostinger incluye cuentas de email:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=soporte@gestior.com.ar
MAIL_PASSWORD=tu_contraseña_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=soporte@gestior.com.ar
MAIL_FROM_NAME="${APP_NAME}"
```

**Puertos comunes:**
- `587` - TLS (recomendado)
- `465` - SSL
- `25` - Sin encriptación (no recomendado)

### Opción 3: Mailtrap (Testing)

Para probar emails sin enviarlos realmente:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username_mailtrap
MAIL_PASSWORD=tu_password_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=test@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Opción 4: SendGrid (Producción profesional)

Servicio gratuito hasta 100 emails/día:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=tu_api_key_sendgrid
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=verificado@tudominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Activar Emails en las Notificaciones

Cuando hayas configurado correctamente el email, puedes reactivar los emails en las notificaciones:

### 1. Editar `app/Notifications/SupportReplied.php`

```php
public function via(object $notifiable): array
{
    return ['database', 'mail']; // Activar email
}
```

### 2. Editar `app/Notifications/SupportStatusChanged.php`

```php
public function via(object $notifiable): array
{
    return ['database', 'mail']; // Activar email
}
```

### 3. Limpiar configuración

```bash
php artisan config:cache
```

### 4. Probar envío de email

```bash
php artisan tinker
>>> Mail::raw('Email de prueba', function($msg) {
...     $msg->to('tu@email.com')->subject('Test');
... });
```

Si ves `Illuminate\Mail\SentMessage`, funciona correctamente.

## Verificar Configuración Actual

Para ver tu configuración actual de email:

```bash
php artisan tinker
>>> config('mail.mailers.smtp')
```

## Problemas Comunes

### 1. Error: "Connection timeout"
- Verifica que el puerto esté abierto en el firewall
- Prueba con puerto 587 en lugar de 465
- Verifica que el host sea correcto

### 2. Error: "Authentication failed"
- Verifica usuario y contraseña
- Para Gmail, usa App Password, no tu contraseña normal
- Verifica que la cuenta de email esté activa

### 3. Error: "TLS/SSL required"
- Cambia `MAIL_ENCRYPTION` a `tls` o `ssl`
- Verifica que el puerto coincida con la encriptación

### 4. Emails no llegan
- Revisa carpeta de spam
- Verifica que `MAIL_FROM_ADDRESS` esté verificado
- Revisa logs: `storage/logs/laravel.log`

## Logs de Email

Los errores de email se guardan en:
```
storage/logs/laravel.log
```

Para ver solo errores de email:
```bash
grep "Mailer\|SMTP\|Mail" storage/logs/laravel.log | tail -20
```

## Recomendación para Producción

Para un entorno de producción serio, considera:

1. **SendGrid** o **Mailgun** - Servicios profesionales con buena entregabilidad
2. **Verificar dominio SPF/DKIM** - Evita que emails vayan a spam
3. **Queue emails** - Enviar emails en cola para no bloquear requests:
   ```php
   public function via(object $notifiable): array
   {
       return ['database', 'mail'];
   }

   public function shouldQueue(): bool
   {
       return true; // Enviar en cola
   }
   ```

## Estado Actual

✅ **Sistema funcionando SIN emails**
- Notificaciones en base de datos: **Activas**
- Notificaciones por email: **Desactivadas**
- Campana de notificaciones: **Funcionando**
- Sistema de tickets: **Funcionando**

Los emails se pueden activar más adelante siguiendo esta guía sin afectar el funcionamiento actual.
