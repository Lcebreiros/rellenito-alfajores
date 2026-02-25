# Integración Parking + Impresora Térmica

## Descripción General

Esta integración permite que **Gestior** (Laravel) y el **programa Gate** (sistema de barrera) trabajen simultáneamente en la misma PC, compartiendo la impresora térmica USB para imprimir tickets de parking.

## Arquitectura

```
┌─────────────────────────────────────────────────────┐
│                    MISMA PC                         │
│                                                     │
│  ┌──────────────┐         ┌──────────────┐         │
│  │   Gate       │         │  Gestior     │         │
│  │  (Programa   │         │  (Laravel)   │         │
│  │   barrera)   │         │              │         │
│  └──────┬───────┘         └──────┬───────┘         │
│         │                        │                 │
│         │  HTTP POST             │ HTTP POST       │
│         │  /print/ticket         │ /print/ticket   │
│         │                        │                 │
│    ┌────▼────────────────────────▼────┐            │
│    │  Servidor de Impresión           │            │
│    │  Node.js (localhost:9876)        │            │
│    │  - Recibe HTTP requests          │            │
│    │  - Envía comandos ESC/POS        │            │
│    └────────────┬─────────────────────┘            │
│                 │                                  │
│         ┌───────▼─────────┐                        │
│         │ Impresora USB   │                        │
│         │  (Térmica)      │                        │
│         └─────────────────┘                        │
└─────────────────────────────────────────────────────┘
```

## Componentes

### 1. Servidor de Impresión (Node.js)

**Ubicación:** `/thermal-printer-server/`

**Función:** Actúa como intermediario entre las aplicaciones y la impresora USB. Escucha peticiones HTTP y las convierte en comandos ESC/POS para la impresora.

**Ventajas:**
- ✅ Múltiples aplicaciones pueden usar la misma impresora
- ✅ No hay conflictos de acceso USB
- ✅ Instalación y configuración simple
- ✅ Compatible con Linux y Windows

### 2. Servicios Laravel

#### `ParkingTicketService`
**Ubicación:** `app/Services/ParkingTicketService.php`

**Función:** Genera los datos del ticket de parking en formato adecuado para impresión térmica.

**Métodos principales:**
- `generateTicketData()`: Genera datos estructurados del ticket
- `generatePlainText()`: Convierte a texto plano formateado

#### `ThermalPrinterService`
**Ubicación:** `app/Services/ThermalPrinterService.php`

**Función:** Comunica Laravel con el servidor de impresión.

**Métodos principales:**
- `printParkingTicket()`: Imprime un ticket de parking
- `isAvailable()`: Verifica si el servidor está disponible
- `getStatus()`: Obtiene estado de la impresora

### 3. Integración en ParkingStayController

**Ubicación:** `app/Http/Controllers/ParkingStayController.php`

**Modificaciones:**
- Inyección de `ThermalPrinterService` en métodos relevantes
- Impresión automática al crear ingresos (`check`, `openSpace`)
- Impresión automática al registrar egresos (`check`, `closeSpace`)

## Instalación y Configuración

### Paso 1: Instalar el Servidor de Impresión

```bash
cd thermal-printer-server
./install.sh
```

O manualmente:

```bash
cd thermal-printer-server
npm install
```

### Paso 2: Configurar Permisos USB (Solo Linux)

Encontrar VID y PID de la impresora:

```bash
lsusb
```

Ejemplo de salida:
```
Bus 001 Device 005: ID 0416:5011 Winbond Electronics Corp
```

Crear regla udev:

```bash
sudo nano /etc/udev/rules.d/99-escpos.rules
```

Agregar (reemplazar con tus valores):
```
SUBSYSTEM=="usb", ATTRS{idVendor}=="0416", ATTRS{idProduct}=="5011", MODE="0666"
```

Recargar:
```bash
sudo udevadm control --reload-rules
sudo udevadm trigger
```

### Paso 3: Iniciar el Servidor

```bash
cd thermal-printer-server
npm start
```

Verificar que se inició correctamente:
```
========================================
Servidor de impresión térmica iniciado
Puerto: 9876
URL: http://localhost:9876
========================================

✓ Impresora USB detectada y lista
```

### Paso 4: Configurar Laravel

Editar `.env`:

```env
# Habilitar impresión térmica
THERMAL_PRINTER_ENABLED=true

# URL del servidor
THERMAL_PRINTER_SERVER_URL=http://localhost:9876

# Timeout
THERMAL_PRINTER_TIMEOUT=3

# Auto-imprimir tickets
THERMAL_PRINTER_AUTO_ENTRY=true
THERMAL_PRINTER_AUTO_EXIT=true
```

### Paso 5: Probar la Integración

#### Desde terminal (probar servidor):

```bash
# Verificar estado
curl http://localhost:9876

# Imprimir ticket de prueba
curl -X POST http://localhost:9876/test

# Listar impresoras
curl http://localhost:9876/printers
```

#### Desde Gestior:

1. Ir al módulo de Parking
2. Crear un ingreso con patente
3. Verificar que se imprime el ticket automáticamente
4. Registrar salida
5. Verificar que se imprime el ticket con el total

## Uso con el Programa Gate

El programa Gate puede llamar al servidor de impresión de la misma forma que lo hace Gestior:

### Ejemplo en cualquier lenguaje (HTTP POST)

```bash
curl -X POST http://localhost:9876/print/ticket \
  -H "Content-Type: application/json" \
  -d '{
    "ticket_data": {
      "type": "entry",
      "stay_id": 999,
      "license_plate": "XYZ789",
      "vehicle_type": "Auto",
      "space_name": "B3",
      "entry_at": "31/12/2025 18:30",
      "app_name": "Gate"
    }
  }'
```

### Ejemplo en Python (para Gate si usa Python)

```python
import requests
import datetime

def imprimir_ticket_ingreso(patente, cochera):
    data = {
        "ticket_data": {
            "type": "entry",
            "stay_id": 123,
            "license_plate": patente,
            "vehicle_type": "Auto",
            "space_name": cochera,
            "entry_at": datetime.datetime.now().strftime("%d/%m/%Y %H:%M"),
            "app_name": "Gate"
        }
    }

    response = requests.post("http://localhost:9876/print/ticket", json=data)
    return response.status_code == 200
```

## Funcionalidades

### Impresión Automática desde Gestior

Cuando se usa Gestior para registrar ingresos/egresos:

1. **Ingreso:**
   - Se crea el registro en la base de datos
   - Se genera automáticamente el ticket con:
     - Patente
     - Tipo de vehículo
     - Cochera
     - Fecha/hora de ingreso
     - Número de estadía
   - Se envía a imprimir automáticamente

2. **Egreso:**
   - Se calcula el tiempo de estadía
   - Se calcula el monto a cobrar
   - Se aplican descuentos si los hay
   - Se genera el ticket con:
     - Datos del vehículo
     - Hora de ingreso y egreso
     - Duración
     - Desglose de precio
     - Total a pagar
   - Se envía a imprimir automáticamente

### Control de Impresión

Deshabilitar impresión temporal:

```env
THERMAL_PRINTER_ENABLED=false
```

Deshabilitar solo en ingreso o egreso:

```env
THERMAL_PRINTER_AUTO_ENTRY=false  # No imprimir en ingreso
THERMAL_PRINTER_AUTO_EXIT=true    # Sí imprimir en egreso
```

## Iniciar Automáticamente con el Sistema

### Linux (systemd)

Crear servicio:

```bash
sudo nano /etc/systemd/system/thermal-printer.service
```

Contenido:

```ini
[Unit]
Description=Servidor de Impresión Térmica
After=network.target

[Service]
Type=simple
User=tu-usuario
WorkingDirectory=/ruta/completa/a/thermal-printer-server
ExecStart=/usr/bin/node server.js
Restart=on-failure
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Habilitar:

```bash
sudo systemctl daemon-reload
sudo systemctl enable thermal-printer
sudo systemctl start thermal-printer
```

### Windows

1. Crear un `.bat` con:
   ```batch
   @echo off
   cd C:\ruta\a\thermal-printer-server
   node server.js
   ```

2. Colocar acceso directo en carpeta de inicio:
   `C:\Users\Usuario\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup`

## Solución de Problemas

### Impresora no detectada

```bash
# Verificar conexión USB
lsusb  # Linux
# o ver "Dispositivos" en Windows

# Reiniciar servidor
cd thermal-printer-server
npm start
```

### Laravel no imprime

```bash
# 1. Verificar que el servidor esté corriendo
curl http://localhost:9876

# 2. Ver logs de Laravel
tail -f storage/logs/laravel.log | grep -i thermal

# 3. Verificar configuración .env
grep THERMAL .env
```

### Ticket sale en blanco

1. Verificar que la impresora tenga papel
2. Verificar que el papel esté correctamente instalado (lado térmico hacia arriba)
3. Probar imprimir ticket de prueba: `curl -X POST http://localhost:9876/test`

### Caracteres extraños en la impresión

Ajustar encoding en `thermal-printer-server/server.js`:

```javascript
const PRINTER_CONFIG = {
  encoding: 'UTF-8',  // Probar: 'CP850', 'CP437', 'GB18030'
  width: 48,
};
```

## Mantenimiento

### Ver logs del servidor

```bash
# Si corre en terminal
# Los logs aparecen en la consola

# Si corre como servicio systemd
sudo journalctl -u thermal-printer -f
```

### Actualizar servidor

```bash
cd thermal-printer-server
git pull  # Si está en git
npm install
sudo systemctl restart thermal-printer  # Si es servicio
```

### Backup de configuración

Archivos importantes:
- `thermal-printer-server/.env`
- `.env` (Laravel)
- `/etc/systemd/system/thermal-printer.service` (Linux)

## Preguntas Frecuentes

**P: ¿Puedo usar múltiples impresoras?**
R: Actualmente el servidor usa la primera impresora detectada. Para múltiples impresoras, se necesitaría modificar el código.

**P: ¿Funciona con impresoras de red (Ethernet)?**
R: No directamente. Este servidor está diseñado para impresoras USB. Para impresoras de red, usar directamente el protocolo de la impresora.

**P: ¿Qué pasa si el servidor está caído?**
R: Laravel registra el ingreso/egreso normalmente pero no imprime. El error se loguea pero no interrumpe el flujo.

**P: ¿Puedo personalizar el formato del ticket?**
R: Sí, editando `app/Services/ParkingTicketService.php` (para los datos) y `thermal-printer-server/server.js` (para el formato de impresión).

**P: ¿Es seguro exponer el puerto 9876?**
R: Solo debe ser accesible desde localhost (127.0.0.1). NO exponerlo a internet.

## Soporte

Para más información:
- Ver `thermal-printer-server/README.md`
- Revisar logs de Laravel: `storage/logs/laravel.log`
- Revisar logs del servidor de impresión
