# Panel de Operador - Parking

## 📍 Acceso

```
URL: /parking/board (Vista "Crear Ingreso" del sidebar)
Ruta: Route::get('parking/board', [ParkingStayController::class, 'board'])
Componente: @livewire('parking.operator-panel')
```

El panel de operador está integrado directamente en la vista "Crear ingreso" que se muestra en el sidebar, encima del mapa de cocheras.

## 🎯 Características

### ✨ Vista Unificada

Un solo panel para manejar todo el flujo del parking:
- **Campo scanner siempre activo** en la parte superior
- **Formulario de nuevo ingreso** a la izquierda
- **Movimientos recientes** a la derecha
- **Modal de cobro automático** al escanear

### 🔍 Scanner 3nstar Integrado

El campo de scanner está **siempre activo** y detecta automáticamente:

**Si escaneas un código de barras (10 dígitos):**
```
0000000123  →  Abre modal de egreso automáticamente
```

**Si escribes texto:**
```
ABC123  →  Se copia a "Patente" en el formulario de ingreso
```

### 📥 Flujo de Ingreso

1. El scanner está activo (o puedes escribir manualmente)
2. Escribe la patente → presiona Enter
3. Selecciona tipo de vehículo
4. Selecciona cochera
5. Click en "Registrar Ingreso e Imprimir Ticket"
6. ✅ Se imprime automáticamente el ticket térmico
7. El cliente recibe y conserva el ticket

### 📤 Flujo de Egreso

1. Cliente llega y entrega ticket
2. **Escaneas el código de barras** con el scanner 3nstar
3. Se abre automáticamente el modal de cobro mostrando:
   - ✅ Patente
   - ✅ Tipo de vehículo
   - ✅ Cochera
   - ✅ Hora de ingreso
   - ✅ Hora de egreso
   - ✅ Duración (automática)
   - ✅ Total a cobrar (calculado automáticamente)
4. Seleccionar **bonificación** si corresponde (restaurante)
5. Marcar checkbox **Mercado Pago** si paga con MP
6. Click en "Cobrar y Cerrar"
7. ✅ Se registra el egreso y se libera la cochera

## 💡 Funcionalidades Especiales

### 🍽️ Bonificaciones de Restaurantes

En el modal de egreso puedes seleccionar bonificaciones:

**Tipos de bonificaciones:**
- **Primera hora gratis** - Descuenta 60 minutos del tiempo
- **Porcentaje** - Descuenta % del total
- **Monto fijo** - Descuenta $ fijo del total

**Ejemplo:**
```
Restaurante "La Parrilla" → Primera hora gratis (60 min)
Cliente estuvo 2h 30min
Se cobra solo 1h 30min
```

### 💳 Mercado Pago

Checkbox para registrar si el pago fue con MP:
- ✅ Marcado → Se registra como pago con Mercado Pago
- ❌ Sin marcar → Se asume pago en efectivo (predeterminado)

## 📋 Movimientos Recientes

La tabla muestra los últimos 15 movimientos en tiempo real:

| Patente | Tipo | Cochera | Estado | Hora | Total |
|---------|------|---------|--------|------|-------|
| ABC123 | Auto | A1 | Abierto | 15:30 | - |
| XYZ789 | Camioneta | B2 | Cerrado | 14:00 - 16:30 | $450 |

**Se actualiza automáticamente** cuando:
- Registras un nuevo ingreso
- Procesas un egreso
- Sin necesidad de recargar la página

## 🔧 Configuración del Scanner

### Scanner 3nstar

1. **Conectar por USB** a la PC
2. **Configurar en modo teclado (HID)**
3. El scanner escribe automáticamente en el campo activo
4. Configurar para agregar **Enter** al final del escaneo

### Código de Barras

El ticket impreso incluye un código de barras:
- **Formato:** CODE39
- **Contenido:** ID de la estadía (10 dígitos con padding)
- **Ejemplo:** `0000000123` para estadía ID 123

Al escanear, el sistema:
1. Detecta que son 10 dígitos numéricos
2. Quita los ceros al inicio
3. Busca la estadía por ID
4. Abre el modal de cobro automáticamente

## 🎨 Interfaz

### Diseño Responsivo

- **Desktop:** Dos columnas (formulario + movimientos)
- **Tablet/Mobile:** Una columna apilada

### Colores y Estados

**Scanner:**
- Fondo azul → Indica que está activo
- Borde azul → Muestra el focus

**Estados de cochera:**
- 🟢 Verde "Abierto" → Estadía activa
- ⚫ Gris "Cerrado" → Estadía finalizada

**Botones:**
- Azul → Registrar ingreso
- Verde → Cobrar y cerrar
- Gris → Cancelar

## 📱 Uso Diario

### Inicio del Turno

1. Hacer click en "Crear Ingreso" en el sidebar (o ir a `/parking/board`)
2. Tener el scanner 3nstar conectado
3. El campo scanner estará activo automáticamente en la parte superior
4. Debajo verás el mapa de cocheras organizadas por categoría

### Operación Normal

**Para ingresos:**
- Escribe patente en el scanner o formulario
- Selecciona tipo y cochera
- Click en registrar
- Se imprime ticket automáticamente

**Para egresos:**
- Simplemente escanea el ticket del cliente
- El modal se abre solo
- Selecciona bonificación/MP si aplica
- Click en cobrar

### Tips de Productividad

1. **El scanner siempre está activo** - No necesitas hacer click en ningún lado
2. **Enter automático** - Configura el scanner para agregar Enter al final
3. **Cochera auto-seleccionada** - Después de un ingreso, selecciona la siguiente cochera disponible
4. **Movimientos en tiempo real** - No necesitas recargar para ver actualizaciones

## 🐛 Solución de Problemas

### El scanner no funciona

1. Verificar conexión USB
2. Probar escribir en un Notepad para confirmar que funciona
3. Recargar la página `/parking/operator`
4. El campo scanner debe tener el focus (borde azul)

### El modal no se abre al escanear

1. Verificar que el código tenga exactamente 10 dígitos
2. El ticket debe estar bien impreso (código legible)
3. Probar escanear en un campo de texto para ver qué lee
4. Verificar que la estadía esté "abierta" (no cerrada previamente)

### No encuentra el medio de pago Mercado Pago

1. Crear un método de pago llamado "Mercado Pago" o "MP"
2. Activarlo para el usuario actual
3. El checkbox busca automáticamente por nombre que contenga "Mercado Pago" o "MP"

## 📊 Reportes y Turnos

El panel de operador se integra con:
- ✅ Turnos de caja (shifts)
- ✅ Reportes de ingresos
- ✅ Estadísticas por cochera
- ✅ Historial de movimientos

Ver documentación de `/parking/board` para más detalles sobre gestión de turnos.

## 🔐 Seguridad y Permisos

El panel requiere:
- ✅ Usuario autenticado
- ✅ Módulo "parking" activo en el usuario
- ✅ Permisos sobre la compañía actual

Si un usuario sin permisos intenta acceder → Error 404

## 🚀 Próximas Mejoras

Posibles mejoras futuras:
- [ ] Notificaciones sonoras al escanear
- [ ] Soporte para múltiples scanners
- [ ] Vista de estadísticas en tiempo real
- [ ] Integración con cámara para fotos de vehículos
- [ ] Búsqueda rápida de estadías por patente

## 📞 Soporte

Para más información sobre:
- **Tickets térmicos:** Ver `FORMATO_TICKET_PARKING.md`
- **Integración impresora:** Ver `INTEGRACION_PARKING_IMPRESORA.md`
- **Configuración general:** Ver `API_DOCUMENTATION.md`
