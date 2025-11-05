# Seeder de Métodos de Pago

Este seeder crea automáticamente los métodos de pago predeterminados para todos los usuarios con rol `seller` o `admin`.

## Métodos de Pago Incluidos

1. **Efectivo** (cash)
2. **Transferencia** (transferencia)
3. **MercadoPago** (mercadopago) - requiere gateway
4. **Visa** (visa)
5. **Visa Débito** (visa-debito)
6. **Mastercard** (mastercard)
7. **PayPal** (paypal) - requiere gateway
8. **Cuenta DNI** (cuenta-dni)

## Cómo Ejecutar en Producción

### Opción 1: SSH directo (recomendado)

```bash
# Conectar por SSH a tu servidor
ssh usuario@tuservidor.com

# Ir al directorio de tu aplicación
cd /ruta/a/tu/aplicacion

# Ejecutar el seeder
php artisan db:seed --class=PaymentMethodsSeeder
```

### Opción 2: Usar panel de Hostinger

Si tu hosting tiene acceso a terminal:

1. Acceder al panel de control de Hostinger
2. Buscar "Terminal" o "SSH Access"
3. Abrir terminal
4. Navegar a tu directorio: `cd public_html` (o donde esté tu app)
5. Ejecutar: `php artisan db:seed --class=PaymentMethodsSeeder`

### Opción 3: Crear ruta temporal (menos seguro)

Si no tienes acceso SSH, puedes crear una ruta temporal:

**⚠️ IMPORTANTE: Eliminar esta ruta después de usarla**

En `routes/web.php` agregar temporalmente:

```php
Route::get('/seed-payment-methods-secret-123', function () {
    Artisan::call('db:seed', ['--class' => 'PaymentMethodsSeeder']);
    return 'Seeder ejecutado! ELIMINAR ESTA RUTA AHORA.';
})->middleware('auth'); // Requiere estar autenticado
```

Luego visitar: `https://tudominio.com/seed-payment-methods-secret-123`

**¡Eliminar esta ruta inmediatamente después de usarla!**

## Características del Seeder

- ✅ **Evita duplicados**: Verifica si el método ya existe antes de crear
- ✅ **Múltiples usuarios**: Crea métodos para todos los sellers/admins
- ✅ **Seguro**: No sobreescribe métodos existentes
- ✅ **Informativo**: Muestra qué se creó y qué ya existía

## Verificar Resultados

Después de ejecutar el seeder, puedes verificar:

```bash
php artisan tinker
>>> App\Models\PaymentMethod::count()
>>> App\Models\PaymentMethod::where('user_id', 1)->get()
```

O desde tu aplicación, ir a la sección de configuración de métodos de pago.

## Notas

- El seeder solo afecta a usuarios con rol `seller` o `admin`
- Si necesitas agregar métodos para usuarios específicos después de crearlos, el seeder se puede ejecutar nuevamente sin problemas
- Los métodos con `requires_gateway = true` (MercadoPago, PayPal) necesitarán configuración adicional de API keys
