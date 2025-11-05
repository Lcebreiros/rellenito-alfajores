# Guía: Ejecutar Migraciones en Producción

## Problema Común

Cuando subes código nuevo con migraciones a producción, puede que algunas tablas ya existan (creadas manualmente o por sesiones anteriores). Esto causa errores como:

```
SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'support_chats' already exists
```

## Solución Aplicada

Las migraciones de chat (`2025_11_04_194620_create_support_chats_table.php`) han sido actualizadas para verificar si las tablas existen antes de crearlas:

```php
if (!Schema::hasTable('support_chats')) {
    Schema::create('support_chats', function (Blueprint $table) {
        // ...
    });
}
```

Esto hace que las migraciones sean **idempotentes** - se pueden ejecutar múltiples veces sin errores.

## Cómo Ejecutar Migraciones en Producción

### Opción 1: SSH (Recomendado)

```bash
# Conectar al servidor
ssh usuario@tuservidor.com

# Ir al directorio de la aplicación
cd /ruta/a/tu/aplicacion

# Ver qué migraciones faltan
php artisan migrate:status

# Ejecutar migraciones pendientes
php artisan migrate --force

# El flag --force es necesario en producción
```

### Opción 2: Modo "pretend" (Ver sin ejecutar)

Para ver qué queries se ejecutarían sin hacer cambios reales:

```bash
php artisan migrate --pretend
```

### Opción 3: Rollback y Re-migrar (Solo si es necesario)

```bash
# Ver el estado actual
php artisan migrate:status

# Revertir el último batch de migraciones
php artisan migrate:rollback --step=1

# Re-ejecutar migraciones
php artisan migrate --force
```

## Verificar el Estado de las Migraciones

```bash
# Ver todas las migraciones y su estado
php artisan migrate:status

# Salida esperada:
# Migration name .................................. Ran?
# 2025_11_04_194620_create_support_chats_table ... Yes
```

## En Caso de Error "Table already exists"

Si ya tienes las tablas creadas manualmente en producción:

### Opción A: Marcar la migración como ejecutada (Recomendado)

```bash
# Insertar el registro de migración manualmente
php artisan tinker
>>> DB::table('migrations')->insert([
...     'migration' => '2025_11_04_194620_create_support_chats_table',
...     'batch' => DB::table('migrations')->max('batch') + 1
... ]);
>>> exit
```

### Opción B: Re-ejecutar con la migración actualizada

Dado que la migración ahora verifica si las tablas existen, puedes simplemente:

```bash
php artisan migrate --force
```

La migración detectará que las tablas ya existen y las saltará sin error.

## Después de las Migraciones

Recuerda limpiar la caché:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Comandos Útiles

```bash
# Ver estructura de una tabla
php artisan db:show support_chats

# Ver todas las tablas
php artisan db:show

# Revisar logs de migraciones
php artisan tinker
>>> DB::table('migrations')->latest()->get()
```

## Checklist para Deployment

- [ ] Hacer backup de la base de datos
- [ ] Probar migraciones en staging/local primero
- [ ] Revisar `php artisan migrate:status`
- [ ] Ejecutar `php artisan migrate --force`
- [ ] Verificar que las tablas tienen los índices correctos
- [ ] Limpiar cachés de configuración
- [ ] Probar funcionalidad en producción

## Notas de Seguridad

- Siempre haz backup antes de migrar en producción
- El flag `--force` es requerido en entorno production
- Si usas transacciones, algunas BD no soportan DDL transaccional (ej: MySQL con ciertos tipos de ALTER)
- Considera usar downtime maintenance mode si hay cambios grandes:
  ```bash
  php artisan down
  php artisan migrate --force
  php artisan up
  ```
