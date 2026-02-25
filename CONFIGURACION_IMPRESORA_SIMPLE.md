# Configuración SIMPLE de Impresora Térmica

## 🎯 Método Recomendado: DIRECTO (Sin Servidor)

Este método es **MUCHO MÁS SIMPLE** porque:
- ✅ NO necesitas abrir consola ni iniciar servidor
- ✅ NO necesitas Node.js
- ✅ PHP se comunica directamente con la impresora USB
- ✅ Funciona automáticamente siempre

## 📋 Paso 1: Conectar la Impresora

1. Conecta la impresora térmica al puerto USB
2. Enciéndela

## 📋 Paso 2: Configurar Permisos (Solo Linux - Una Vez)

**En Linux** necesitas dar permisos USB (solo una vez):

```bash
# Ver dispositivos USB conectados
lsusb

# Busca tu impresora, ejemplo:
# Bus 001 Device 005: ID 0416:5011 Winbond Electronics Corp
#                        ^^^^ ^^^^
#                        VID  PID
```

Crea la regla de permisos:

```bash
# Reemplaza VID y PID con los valores de tu impresora
sudo nano /etc/udev/rules.d/99-escpos.rules
```

Agrega esta línea (cambia los valores):
```
SUBSYSTEM=="usb", ATTRS{idVendor}=="0416", ATTRS{idProduct}=="5011", MODE="0666"
```

Guarda (`Ctrl+O`, `Enter`, `Ctrl+X`) y recarga:
```bash
sudo udevadm control --reload-rules
sudo udevadm trigger
```

**En Windows** no necesitas hacer nada especial, solo asegúrate de que la impresora esté instalada.

## 📋 Paso 3: Configurar Laravel

Edita tu archivo `.env` y agrega estas líneas:

```env
# Habilitar método directo (SIN servidor)
THERMAL_PRINTER_DIRECT_ENABLED=true

# Linux: Ruta del dispositivo (si no se detecta automáticamente)
# THERMAL_PRINTER_DIRECT_PATH=/dev/usb/lp0

# Windows: Nombre de la impresora (si no se detecta automáticamente)
# THERMAL_PRINTER_DIRECT_PATH=ThermalPrinter
```

**¡Eso es todo!** No necesitas más configuración.

## 📋 Paso 4: Probar

Para probar que funciona, ejecuta:

```bash
php artisan tinker
```

Luego dentro de tinker:

```php
$service = new App\Services\DirectThermalPrinterService();
$service->printTest();
```

Si imprime un ticket de prueba, ¡funciona! 🎉

## 🔧 Solución de Problemas

### Linux: "Permission denied"

Si ves error de permisos:

```bash
# Verificar que la regla existe
cat /etc/udev/rules.d/99-escpos.rules

# Verificar permisos del dispositivo
ls -l /dev/usb/lp0

# Debería mostrar: crw-rw-rw- (permisos 666)
```

Si no tiene permisos correctos:

```bash
# Dar permisos manualmente (temporal)
sudo chmod 666 /dev/usb/lp0

# O agregar tu usuario al grupo lp (permanente)
sudo usermod -a -G lp $USER
# Luego reinicia sesión
```

### Linux: No encuentra /dev/usb/lp0

Prueba rutas alternativas:

```bash
ls /dev/lp*
ls /dev/usb/lp*

# Usa la que encuentres en .env:
THERMAL_PRINTER_DIRECT_PATH=/dev/lp0
```

### Windows: No encuentra la impresora

1. Ve a "Dispositivos e impresoras"
2. Busca tu impresora térmica
3. Click derecho → Propiedades → Compartir
4. Activa "Compartir esta impresora"
5. Anota el nombre (ej: "POS-80")
6. Usa ese nombre en `.env`:

```env
THERMAL_PRINTER_DIRECT_PATH=POS-80
```

### No imprime nada

1. Verifica que la impresora esté encendida
2. Verifica que tenga papel (lado térmico hacia arriba)
3. Intenta imprimir desde otra aplicación para confirmar que funciona
4. Revisa los logs de Laravel:

```bash
tail -f storage/logs/laravel.log | grep -i thermal
```

## 📊 Comparación de Métodos

| Característica | Método DIRECTO | Método con Servidor |
|---|---|---|
| Simplicidad | ⭐⭐⭐⭐⭐ | ⭐⭐ |
| Necesita Node.js | ❌ No | ✅ Sí |
| Necesita iniciar servidor | ❌ No | ✅ Sí |
| Configuración | Mínima | Compleja |
| Múltiples apps (Gate + Gestior) | ❌ Solo una app | ✅ Ambas |
| Recomendado para | Solo Gestior | Gate + Gestior |

## 💡 Conclusión

**Si solo usas Gestior**: Usa el método DIRECTO (esta guía)

**Si usas Gate + Gestior**: Usa el método con servidor ([INTEGRACION_PARKING_IMPRESORA.md](INTEGRACION_PARKING_IMPRESORA.md))

## 🎯 Próximos Pasos

Una vez configurado:

1. Ve a "Crear Ingreso" en el sidebar
2. Registra un nuevo ingreso de vehículo
3. El ticket se imprimirá automáticamente
4. Al escanear el ticket en el egreso, se cobrará automáticamente

¡Listo! 🚗🖨️
