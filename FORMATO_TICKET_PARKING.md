# Formato del Ticket de Parking

## ⚠️ IMPORTANTE: Ticket Único

El ticket **SOLO se imprime al INGRESO**. Al egreso, se **escanea** el código de barras del ticket para calcular la tarifa.

## Formato del Ticket

```
==========================================
    Estacionamiento Moreno S.R.L.
==========================================

Patente: ABC123
Vehículo: Auto
Cochera: A1

Fecha: 31/12/2025 15:30

    ||||| ||||| ||||| |||||
    0000000123
    (Código de barras escaneable)

==========================================
       Conserve este ticket
      Gracias por su visita
```

## Configuración

Para cambiar el nombre del negocio, editar el archivo `.env`:

```env
PARKING_BUSINESS_NAME="Estacionamiento Moreno S.R.L."
```

Si necesitas cambiar a otro nombre:

```env
PARKING_BUSINESS_NAME="Mi Estacionamiento"
```

## Datos que se muestran

El ticket contiene:
- ✅ **Nombre del negocio** (configurable: "Estacionamiento Moreno S.R.L.")
- ✅ **Patente** del vehículo
- ✅ **Tipo de vehículo** (Auto, Camioneta, etc.)
- ✅ **Cochera** asignada
- ✅ **Fecha y hora** de ingreso
- ✅ **Código de barras** escaneable (ID de la estadía con formato CODE39)

## Flujo de Operación

### Al INGRESO:
1. Se registra el vehículo en el sistema
2. Se imprime automáticamente el ticket
3. El cliente recibe y conserva el ticket

### Al EGRESO:
1. Se escanea el código de barras del ticket
2. El sistema calcula automáticamente el tiempo transcurrido
3. Se aplica la tarifa correspondiente
4. Se procesa el pago
5. **NO se imprime otro ticket**

## Código de Barras

El código de barras permite escanear el ticket al egreso para:
- ✅ Identificar automáticamente la estadía
- ✅ Calcular el tiempo transcurrido
- ✅ Aplicar la tarifa correcta
- ✅ Agilizar el proceso de egreso

**Formato:** CODE39 (compatible con la mayoría de scanners)
**Contenido:** ID de la estadía con padding de 10 dígitos (ej: 0000000123)

## Scanners Recomendados

Cualquier scanner de código de barras USB compatible con CODE39:
- Scanners de mano USB
- Scanners de mostrador
- Lectores 1D/2D genéricos

El scanner se comporta como un teclado, escribiendo el código directamente en el campo activo.

## Personalización adicional

Si necesitas agregar más información al ticket (como dirección, teléfono, etc.):

1. **Para texto adicional fijo:**
   - Editar [ParkingTicketService.php:42](app/Services/ParkingTicketService.php#L42)
   - Agregar líneas en la sección de encabezado o pie

2. **Para información dinámica:**
   - Modificar [ParkingTicketService.php:14](app/Services/ParkingTicketService.php#L14) (método `generateTicketData`)
   - Actualizar [server.js:110](thermal-printer-server/server.js#L110) (función `printTicket`)

3. **Para cambiar el formato del código de barras:**
   - Editar [server.js:136](thermal-printer-server/server.js#L136)
   - Opciones: CODE39, CODE128, EAN13, etc.
