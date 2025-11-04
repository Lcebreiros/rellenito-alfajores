# Solución al Error 419 (Sesión Expirada)

## ¿Qué hicimos?

Se implementaron las siguientes mejoras para manejar el error 419 de forma amigable:

### 1. Vista personalizada de sesión expirada
- **Archivo**: `resources/views/errors/419.blade.php`
- Muestra un mensaje claro: "Su sesión ha expirado"
- Botón directo para volver a iniciar sesión
- Diseño consistente con el resto de la aplicación

### 2. Manejador de excepciones
- **Archivo**: `bootstrap/app.php`
- Detecta errores 419 automáticamente
- Para peticiones normales: muestra la vista personalizada
- Para peticiones AJAX/Livewire: devuelve JSON con mensaje

### 3. JavaScript para peticiones asíncronas
- **Archivo**: `resources/views/layouts/app.blade.php`
- Intercepta errores 419 en Livewire
- Intercepta errores 419 en AJAX/Fetch
- Muestra un diálogo de confirmación y redirige al login

## Configuración en Producción

Para evitar el error 419 en producción, configura estas variables en tu `.env`:

```env
# URL de tu aplicación (importante!)
APP_URL=https://tudominio.com

# Configuración de sesión
SESSION_DRIVER=database
SESSION_LIFETIME=720          # 12 horas (ajusta según necesites)
SESSION_DOMAIN=.tudominio.com # Con punto inicial para subdominios
SESSION_SECURE_COOKIE=true    # true si usas HTTPS
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Si usas Livewire con CDN
LIVEWIRE_ASSET_URL=https://tudominio.com
```

### Pasos en el servidor:

1. **Actualiza tu archivo `.env`** con los valores correctos
   ```bash
   nano /ruta/a/tu/proyecto/.env
   ```

2. **Verifica que existe la tabla de sesiones** (si usas SESSION_DRIVER=database)
   ```bash
   php artisan session:table
   php artisan migrate
   ```

3. **Limpia y regenera el cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   php artisan config:cache
   ```

4. **Verifica permisos** de la carpeta storage
   ```bash
   chmod -R 775 storage
   chown -R www-data:www-data storage
   ```

5. **Reinicia servicios** (opcional pero recomendado)
   ```bash
   sudo systemctl restart php8.3-fpm  # Ajusta la versión de PHP
   sudo systemctl reload nginx        # O apache2
   ```

## Causas comunes del error 419

1. **Sesión expirada por tiempo**: El usuario dejó la página abierta más de `SESSION_LIFETIME` minutos
2. **Dominio incorrecto**: `SESSION_DOMAIN` no coincide con tu dominio real
3. **HTTPS mal configurado**: `SESSION_SECURE_COOKIE=true` pero sin HTTPS
4. **Cache desactualizado**: Cambios en `.env` sin ejecutar `php artisan config:cache`
5. **Proxy/CDN**: Cloudflare o Nginx no configurado correctamente

## Soluciones adicionales

### Si usas Cloudflare:
En tu `.env`:
```env
TRUSTED_PROXIES=*
```

### Si el problema persiste:
Puedes aumentar el tiempo de sesión temporalmente para debugging:
```env
SESSION_LIFETIME=1440  # 24 horas
```

### Para debugging:
En desarrollo, puedes ver más detalles con:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

## Verificar que funciona

1. Abre tu aplicación en producción
2. Deja la página abierta durante `SESSION_LIFETIME` minutos
3. Intenta hacer una acción (submit form, click botón, etc.)
4. Deberías ver la página personalizada "Su sesión ha expirado" en lugar del error 419

## Soporte

Si el problema persiste después de aplicar estas soluciones:
- Verifica los logs: `storage/logs/laravel.log`
- Revisa la configuración del servidor web (Nginx/Apache)
- Asegúrate de que las cookies se estén guardando correctamente en el navegador
