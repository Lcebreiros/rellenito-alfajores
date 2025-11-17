# Sistema de Backups Automáticos - Rellenito Alfajores

Documentación completa del sistema de respaldos automáticos implementado con `spatie/laravel-backup`.

---

## 🔒 ¿Por qué son importantes los backups?

Los backups automáticos protegen los datos de tus clientes contra:

- ✅ **Fallos de hardware** - Disco duro dañado, servidor caído
- ✅ **Errores humanos** - Borrado accidental de datos, comandos incorrectos
- ✅ **Problemas de software** - Bugs, migraciones fallidas, código corrupto
- ✅ **Ataques maliciosos** - Ransomware, intrusiones, sabotaje
- ✅ **Desastres naturales** - Incendios, inundaciones, cortes de energía

**Con backups diarios, nunca perderás más de 24 horas de datos.**

---

## 📋 ¿Qué incluyen los backups?

### Base de Datos (Completa)
- Todos los pedidos, clientes, productos
- Usuarios, permisos, configuraciones
- Historial de transacciones y cambios
- Relaciones entre datos

### Archivos Importantes
- Imágenes de productos (`storage/app/public`)
- Archivos subidos por usuarios
- Configuraciones del sistema
- Variables de entorno (`.env`)

### Archivos Excluidos (no necesarios)
- Código de terceros (`vendor/`, `node_modules/`)
- Cache y sesiones temporales
- Logs del sistema
- Archivos de desarrollo (`.git`, `.idea`, `.vscode`)

---

## ⚙️ Configuración del Sistema

### 1. Backups Automáticos

Los backups se ejecutan **automáticamente** todos los días:

| Tarea | Horario | Descripción |
|-------|---------|-------------|
| **Backup completo** | 2:00 AM | Crea copia de base de datos + archivos |
| **Limpieza** | 3:00 AM | Elimina backups antiguos según política |
| **Monitoreo** | Cada hora | Verifica salud de backups existentes |

### 2. Política de Retención

El sistema mantiene backups de forma inteligente:

```
📅 Últimos 7 días    → Todos los backups (uno por día)
📅 Últimos 16 días   → Un backup por día
📅 Últimas 8 semanas → Un backup por semana
📅 Últimos 4 meses   → Un backup por mes
📅 Últimos 2 años    → Un backup por año
```

**Ejemplo práctico:**
- Si hoy es 15 de marzo de 2025, tendrás:
  - Backups del 8 al 15 de marzo (todos los días)
  - Un backup del 1 de marzo, otro del 1 de febrero, etc.
  - Backups semanales de enero, diciembre, noviembre...
  - Y así hasta 2 años atrás

### 3. Límites de Almacenamiento

- **Tamaño máximo total:** 5 GB
- **Cuando se excede:** Se eliminan los backups más antiguos automáticamente
- **Backup más reciente:** Nunca se elimina, sin importar el tamaño

---

## 🚀 Comandos Manuales

### Crear Backup Inmediato
```bash
php artisan backup:run
```
Útil antes de:
- Migraciones importantes
- Actualizaciones del sistema
- Cambios masivos de datos

### Ver Estado de Backups
```bash
php artisan backup:list
```
Muestra todos los backups disponibles con sus tamaños y fechas.

### Limpiar Backups Antiguos
```bash
php artisan backup:clean
```
Aplica la política de retención manualmente.

### Verificar Salud
```bash
php artisan backup:monitor
```
Verifica que los backups sean recientes y no estén corruptos.

---

## 📧 Notificaciones por Email

El sistema enviará emails automáticamente cuando:

### ✅ Eventos Exitosos
- Backup completado correctamente
- Limpieza realizada con éxito
- Backups en buen estado

### ⚠️ Alertas de Problemas
- Backup falló al ejecutarse
- Backup muy antiguo (más de 24 horas)
- Limpieza falló
- Backups corruptos o inválidos

**Configurar email de notificaciones:**
```env
BACKUP_NOTIFICATION_EMAIL=admin@tudominio.com
```

---

## 🔐 Seguridad de Backups

### Compresión Automática
Los backups se comprimen con **Gzip** para ahorrar espacio:
- Base de datos comprimida → Ahorra 60-80% de espacio
- Archivo ZIP comprimido → Ahorra 40-60% adicional

### Encriptación (Opcional)
Puedes encriptar los backups con contraseña:

```env
BACKUP_ARCHIVE_PASSWORD=mi-contraseña-super-segura-123
```

⚠️ **IMPORTANTE:** Guarda esta contraseña en lugar seguro. Sin ella, no podrás restaurar los backups.

### Ubicación de Backups
Por defecto: `storage/app/backups/`

**Recomendación:** Configurar backup remoto en AWS S3, Google Cloud, o Dropbox.

---

## ☁️ Configurar Backup Remoto (Opcional pero Recomendado)

### Paso 1: Elegir Servicio en la Nube

**AWS S3** (Recomendado para producción)
```env
AWS_ACCESS_KEY_ID=tu-access-key
AWS_SECRET_ACCESS_KEY=tu-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=rellenito-backups
```

### Paso 2: Actualizar Configuración

En `config/backup.php`, línea 161:

```php
'disks' => [
    'backups',  // Local
    's3',       // Remoto (agregar esta línea)
],
```

**Ventajas del backup remoto:**
- ✅ Protección contra desastres físicos
- ✅ Datos en múltiples ubicaciones
- ✅ Recuperación desde cualquier lugar
- ✅ Escalable y seguro

---

## 🔄 Restaurar Backups

### Restaurar Base de Datos

1. **Ubicar el backup:**
```bash
ls -lh storage/app/backups/
```

2. **Extraer el archivo ZIP:**
```bash
cd storage/app/backups/
unzip Rellenito-Alfajores-backup-2025-03-15-020000.zip
```

3. **Restaurar la base de datos:**

**Para MySQL:**
```bash
mysql -u usuario -p nombre_base_datos < db-dumps/mysql-database.sql.gz
# o si está comprimido:
gunzip -c db-dumps/mysql-database.sql.gz | mysql -u usuario -p nombre_base_datos
```

**Para SQLite:**
```bash
cp db-dumps/sqlite-database.sqlite database/database.sqlite
```

4. **Restaurar archivos:**
```bash
# Extraer archivos del backup
cp -r storage/app/public/* ../../../storage/app/public/
```

### Restaurar Todo el Sistema

Si perdiste todo el servidor:

1. Instalar Laravel nuevo
2. Configurar `.env` con datos correctos
3. Descargar backup más reciente
4. Extraer y restaurar DB + archivos
5. Ejecutar `php artisan migrate` para verificar
6. Ejecutar `php artisan storage:link`

---

## 🛠️ Configurar Cron Job en el Servidor

Para que los backups automáticos funcionen, necesitas configurar el cron job del servidor.

### En Linux/Ubuntu

1. **Editar crontab:**
```bash
crontab -e
```

2. **Agregar esta línea:**
```bash
* * * * * cd /ruta/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

3. **Verificar que está activo:**
```bash
crontab -l
```

### Verificar que Funciona

```bash
# Ver logs del scheduler
tail -f storage/logs/laravel.log

# O ejecutar manualmente
php artisan schedule:run
```

---

## 📊 Monitoreo y Mantenimiento

### Revisar Espacio en Disco
```bash
du -sh storage/app/backups/
```

### Ver Logs de Backups
```bash
tail -f storage/logs/laravel.log | grep backup
```

### Probar Restauración
**Recomendación:** Prueba restaurar un backup cada 3 meses para asegurarte que funciona.

```bash
# En servidor de prueba:
php artisan backup:run
# ... esperar ...
# Restaurar y verificar que todo funcione
```

---

## 🚨 Solución de Problemas

### Error: "mysqldump not found"
```bash
# Instalar mysql-client
sudo apt-get install mysql-client
```

### Error: "Insufficient permissions"
```bash
# Dar permisos a carpeta de backups
chmod -R 755 storage/app/backups
chown -R www-data:www-data storage/app/backups
```

### Backups demasiado grandes
Ajustar en `config/backup.php`:
```php
'delete_oldest_backups_when_using_more_megabytes_than' => 2000, // 2GB
```

### No recibo emails
Verificar configuración SMTP en `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-contraseña
```

---

## 📝 Checklist de Implementación

- [x] Paquete `spatie/laravel-backup` instalado
- [x] Configuración publicada en `config/backup.php`
- [x] Disco de backups creado en `config/filesystems.php`
- [x] Variables de entorno agregadas a `.env`
- [x] Scheduler configurado en `bootstrap/app.php`
- [ ] **Cron job configurado en el servidor** ⚠️
- [ ] **Email de notificaciones configurado**
- [ ] **Probar backup manual con `php artisan backup:run`**
- [ ] **Probar restauración en servidor de prueba**
- [ ] (Opcional) Configurar backup remoto en S3/Cloud

---

## 📚 Recursos Adicionales

- **Documentación oficial:** https://spatie.be/docs/laravel-backup
- **Soporte:** https://github.com/spatie/laravel-backup/issues
- **Laravel Scheduling:** https://laravel.com/docs/11.x/scheduling

---

## 🎯 Resumen Ejecutivo

**Sistema implementado y configurado. Los backups automáticos:**

✅ Se ejecutan diariamente a las 2:00 AM
✅ Incluyen base de datos completa + archivos importantes
✅ Se comprimen automáticamente (ahorro de 60-80% espacio)
✅ Se limpian automáticamente según política de retención
✅ Envían notificaciones por email de éxito/errores
✅ Mantienen hasta 2 años de historial
✅ Protegen contra pérdida de datos, errores y desastres

**Próximo paso crítico:** Configurar el cron job en el servidor de producción.

---

**Generado para Rellenito Alfajores - Sistema de Gestión**
*Última actualización: Marzo 2025*
