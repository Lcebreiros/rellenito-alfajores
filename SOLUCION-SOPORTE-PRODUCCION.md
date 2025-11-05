# Solución: Error en Sistema de Soporte en Producción

## Problema Detectado

En producción, al crear un ticket de soporte se generaba el error:

```
SQLSTATE[HY000]: General error: 1364 Field 'ticket_id' doesn't have a default value
```

Además:
- El reporte aparecía vacío
- No se disparaban las notificaciones (por el error 500)

## Causa Raíz

Existía una **tabla antigua** `support_messages` en producción con estructura diferente:

### Estructura Antigua (Producción):
- `ticket_id` → Campo requerido
- `body` → Contenido del mensaje
- No tenía: `is_read`, `attachment_path`

### Estructura Nueva (Código):
- `support_chat_id` → Reemplaza `ticket_id`
- `message` → Reemplaza `body`
- Incluye: `is_read`, `attachment_path`

## Solución Implementada

### 1. Migración de Actualización

Creada: `database/migrations/2025_11_05_122923_update_support_tables_structure.php`

Esta migración:
- ✅ Renombra `ticket_id` → `support_chat_id`
- ✅ Renombra `body` → `message`
- ✅ Agrega columnas faltantes: `is_read`, `attachment_path`
- ✅ Agrega índices necesarios
- ✅ Es **segura**: solo ejecuta si las columnas existen/no existen

### 2. Actualización del Controlador

Actualizado: `app/Http/Controllers/SupportController.php`

**Cambios:**
```php
// ANTES (líneas 54-58, 89-93)
SupportMessage::create([
    'ticket_id' => $ticket->id,
    'user_id'   => $user->id,
    'body'      => $data['message'],
]);

// DESPUÉS
SupportMessage::create([
    'support_chat_id' => $ticket->id,
    'user_id'         => $user->id,
    'message'         => $data['message'],
]);
```

### 3. Actualización del Modelo SupportMessage

Actualizado: `app/Models/SupportMessage.php`

**Cambios:**
- ✅ Agregado accessor `getBodyAttribute()` para compatibilidad retroactiva
- ✅ Agregada relación `ticket()` además de `chat()`
- ✅ Agregado `'body'` en `$appends` para que esté disponible automáticamente
- ✅ Mantiene campos nuevos: `message`, `support_chat_id`

**Beneficio:** El código antiguo que usa `$message->body` seguirá funcionando.

### 4. Actualización del Modelo SupportTicket

Actualizado: `app/Models/SupportTicket.php`

**Cambio:**
```php
// ANTES
public function messages(): HasMany {
    return $this->hasMany(SupportMessage::class, 'ticket_id');
}

// DESPUÉS
public function messages(): HasMany {
    return $this->hasMany(SupportMessage::class, 'support_chat_id');
}
```

## Cómo Aplicar en Producción

### Paso 1: Ejecutar la Migración

```bash
# SSH a producción
ssh usuario@servidor

# Ir al directorio
cd /ruta/a/tu/aplicacion

# Ejecutar migración
php artisan migrate --force
```

La migración detectará automáticamente la estructura antigua y la actualizará.

### Paso 2: Verificar la Estructura

```bash
php artisan tinker
>>> Schema::getColumnListing('support_messages')
# Debería mostrar: support_chat_id, message, is_read, attachment_path
```

### Paso 3: Probar Creación de Ticket

1. Crear un nuevo ticket de soporte
2. Verificar que:
   - ✅ Se crea sin error 500
   - ✅ El mensaje aparece correctamente
   - ✅ Se envían las notificaciones
   - ✅ El ticket es visible en la lista

### Paso 4: Limpiar Cachés

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Compatibilidad Retroactiva

El código mantiene **compatibilidad total** con código antiguo:

| Código Antiguo | Funciona | Cómo |
|----------------|----------|------|
| `$message->body` | ✅ Sí | Accessor `getBodyAttribute()` |
| `$message->ticket` | ✅ Sí | Relación `ticket()` agregada |
| `$message->message` | ✅ Sí | Campo real de la BD |
| `$message->support_chat_id` | ✅ Sí | Nombre nuevo de columna |

## Archivos Modificados

1. ✅ `database/migrations/2025_11_05_122923_update_support_tables_structure.php` (nuevo)
2. ✅ `app/Http/Controllers/SupportController.php` (actualizado)
3. ✅ `app/Models/SupportMessage.php` (actualizado)
4. ✅ `app/Models/SupportTicket.php` (actualizado)

## Verificación Post-Despliegue

Checklist:

- [ ] Migración ejecutada sin errores
- [ ] Estructura de tabla actualizada (`support_chat_id`, `message`)
- [ ] Crear ticket nuevo funciona
- [ ] Mensajes se muestran correctamente
- [ ] Notificaciones se envían
- [ ] Tickets antiguos siguen siendo visibles
- [ ] Responder a tickets funciona

## Notas Importantes

1. **Sin pérdida de datos:** La migración renombra columnas, no las elimina
2. **Datos existentes:** Los tickets y mensajes antiguos siguen funcionando
3. **Rollback:** No hay rollback automático (para evitar pérdida de datos)
4. **Backup:** Recomendado hacer backup antes de migrar

## En Caso de Error

Si la migración falla, verificar:

```bash
# Ver estado de la migración
php artisan migrate:status

# Ver estructura actual
php artisan tinker
>>> Schema::getColumnListing('support_messages')
```

Si necesitas revertir manualmente:

```sql
-- Solo si es absolutamente necesario
ALTER TABLE support_messages CHANGE support_chat_id ticket_id BIGINT UNSIGNED;
ALTER TABLE support_messages CHANGE message body TEXT;
ALTER TABLE support_messages DROP COLUMN is_read;
ALTER TABLE support_messages DROP COLUMN attachment_path;
```

## Próximos Pasos Opcionales

Una vez que el sistema esté estable en producción:

1. Considerar migrar `SupportTicket` a `SupportChat` completamente
2. Agregar soporte para archivos adjuntos
3. Implementar chat en tiempo real con Pusher (ya configurado)
4. Agregar indicadores de "mensaje leído"
