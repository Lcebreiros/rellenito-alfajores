# Servidor de Impresión Térmica para Parking

Este servidor permite imprimir tickets de parking en impresoras térmicas USB conectadas a la PC.

## Requisitos

- **Node.js** versión 14 o superior
- **npm** (viene con Node.js)
- **Impresora térmica USB** compatible con ESC/POS
- **Linux**: Permisos de acceso USB (configurar udev rules)
- **Windows**: Drivers de la impresora instalados

## Instalación

### 1. Instalar Node.js

#### En Ubuntu/Debian:
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

#### En Windows:
Descargar e instalar desde: https://nodejs.org/

### 2. Instalar dependencias

Desde el directorio `thermal-printer-server`:

```bash
cd thermal-printer-server
npm install
```

### 3. Configurar permisos USB (Solo Linux)

Crear archivo de reglas udev para dar acceso a la impresora:

```bash
sudo nano /etc/udev/rules.d/99-escpos.rules
```

Agregar la siguiente línea (ajustar VID y PID según tu impresora):

```
SUBSYSTEM=="usb", ATTRS{idVendor}=="0416", ATTRS{idProduct}=="5011", MODE="0666"
```

Para encontrar el VID y PID de tu impresora:

```bash
lsusb
```

Recargar reglas:

```bash
sudo udevadm control --reload-rules
sudo udevadm trigger
```

## Uso

### Iniciar el servidor

```bash
cd thermal-printer-server
npm start
```

El servidor se iniciará en `http://localhost:9876`

### Modo desarrollo (auto-reload)

```bash
npm run dev
```

### Iniciar automáticamente con el sistema

#### Linux (systemd)

Crear archivo de servicio:

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
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

Habilitar e iniciar:

```bash
sudo systemctl daemon-reload
sudo systemctl enable thermal-printer
sudo systemctl start thermal-printer
```

Ver logs:

```bash
sudo journalctl -u thermal-printer -f
```

#### Windows

Crear un acceso directo en la carpeta de inicio o usar herramientas como NSSM.

## API Endpoints

### GET `/`
Verificar estado del servidor

**Respuesta:**
```json
{
  "status": "running",
  "printer_connected": true,
  "port": 9876
}
```

### GET `/printers`
Listar impresoras USB disponibles

**Respuesta:**
```json
{
  "printers": [
    {
      "index": 0,
      "vendorId": 1046,
      "productId": 20497
    }
  ],
  "count": 1
}
```

### POST `/print/text`
Imprimir texto plano

**Body:**
```json
{
  "text": "Texto a imprimir\nCon saltos de línea\n\n\n"
}
```

### POST `/print/ticket`
Imprimir ticket de parking formateado

**Body:**
```json
{
  "ticket_data": {
    "type": "entry",
    "stay_id": 123,
    "license_plate": "ABC123",
    "vehicle_type": "Auto",
    "space_name": "A1",
    "entry_at": "31/12/2025 15:30",
    "app_name": "Gestior"
  }
}
```

Para tickets de egreso, incluir campos adicionales:
```json
{
  "ticket_data": {
    "type": "exit",
    "stay_id": 123,
    "license_plate": "ABC123",
    "vehicle_type": "Auto",
    "space_name": "A1",
    "entry_at": "31/12/2025 15:30",
    "exit_at": "31/12/2025 17:45",
    "duration_formatted": "2h 15min",
    "total_amount": 350.00,
    "discount_amount": 50.00,
    "discount_name": "Bono cliente",
    "app_name": "Gestior"
  }
}
```

### POST `/test`
Imprimir un ticket de prueba

No requiere body.

## Configuración en Laravel

Editar el archivo `.env` de Laravel:

```env
# Habilitar impresión térmica
THERMAL_PRINTER_ENABLED=true

# URL del servidor (usar localhost si está en la misma PC)
THERMAL_PRINTER_SERVER_URL=http://localhost:9876

# Timeout en segundos
THERMAL_PRINTER_TIMEOUT=3

# Auto-imprimir en ingreso/egreso
THERMAL_PRINTER_AUTO_ENTRY=true
THERMAL_PRINTER_AUTO_EXIT=true
```

## Solución de problemas

### "No se encontraron impresoras USB"

1. Verificar que la impresora esté conectada: `lsusb` (Linux) o "Dispositivos" (Windows)
2. En Linux, verificar permisos USB (ver paso 3 de instalación)
3. Reiniciar el servidor después de conectar la impresora

### "Error al abrir dispositivo"

1. Verificar que no haya otro programa usando la impresora
2. En Linux, verificar permisos del usuario sobre dispositivos USB
3. Reiniciar la impresora (desconectar y reconectar USB)

### Laravel no se conecta al servidor

1. Verificar que el servidor esté corriendo: `curl http://localhost:9876`
2. En `.env` verificar que `THERMAL_PRINTER_SERVER_URL` sea correcta
3. Verificar que no haya firewall bloqueando el puerto 9876

### El ticket no se imprime desde Laravel

1. Verificar logs de Laravel: `tail -f storage/logs/laravel.log`
2. Verificar que `THERMAL_PRINTER_ENABLED=true` en `.env`
3. Probar imprimir directamente al servidor:
   ```bash
   curl -X POST http://localhost:9876/test
   ```

## Logs

El servidor muestra información en la consola:

```
Servidor de impresión térmica iniciado
Puerto: 9876
URL: http://localhost:9876
✓ Impresora USB detectada y lista
```

Cada impresión genera un log:

```
Impresoras encontradas: 1
  [0] VID: 1046, PID: 20497
Impresión completada
Ticket impreso correctamente
```

## Personalización

### Cambiar el puerto

Editar `.env` en el directorio del servidor:

```
PORT=9999
```

O usar variable de entorno:

```bash
PORT=9999 npm start
```

### Ajustar ancho de impresión

Editar `server.js`:

```javascript
const PRINTER_CONFIG = {
  encoding: 'UTF-8',
  width: 48, // Cambiar este valor según tu impresora
};
```

Anchos comunes:
- 32 caracteres: Impresoras de 58mm
- 42-48 caracteres: Impresoras de 80mm

## Soporte

Para más información sobre el formato ESC/POS, consultar:
- https://github.com/song940/node-escpos
- Documentación de tu impresora térmica
