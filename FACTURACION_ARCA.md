# Sistema de Facturación Electrónica ARCA

Este sistema permite emitir facturas electrónicas conectándose directamente con ARCA (AFIP) usando los certificados digitales del usuario.

## 🚀 Características

- ✅ Integración directa con ARCA/AFIP vía SOAP
- ✅ Soporte para Facturas A, B, C
- ✅ Soporte para Notas de Crédito y Débito
- ✅ Generación automática de PDF con código QR
- ✅ Encriptación segura de certificados
- ✅ Multi-tenant (cada empresa usa sus propios certificados)
- ✅ Ambientes de Testing y Producción

## 📋 Requisitos Previos

### 1. Obtener Certificado Digital de ARCA

Para usar el sistema, necesitas un certificado digital de ARCA/AFIP:

1. **Ingresar a AFIP con Clave Fiscal**
2. **Ir a: Administración de Certificados Digitales**
3. **Generar nuevo certificado para "Facturación Electrónica"**
4. **Descargar:**
   - Certificado (.crt)
   - Clave privada (.key)
   - Guardar la contraseña del certificado

### 2. Configurar Punto de Venta

1. Ingresar a AFIP
2. Ir a "Comprobantes en línea" o "Facturación Electrónica"
3. Dar de alta un punto de venta
4. Anotar el número de punto de venta

## 🔧 Configuración Inicial

### Paso 1: Acceder a Configuración

1. Hacer clic en el botón "Facturación" en el dashboard
2. Completar el formulario con:
   - CUIT de la empresa
   - Razón Social
   - Condición frente al IVA
   - Ambiente (empezar con "Testing")
   - Punto de venta

### Paso 2: Subir Certificados

1. Subir archivo de certificado (.crt o .pem)
2. Subir archivo de clave privada (.key o .pem)
3. Ingresar contraseña del certificado (si tiene)
4. Guardar configuración

**NOTA:** Los certificados se guardan encriptados en la base de datos usando Laravel Crypt.

### Paso 3: Verificar Configuración

La configuración estará completa cuando veas el mensaje "Configuración activa" en verde.

## 💼 Uso del Sistema

### Crear una Factura

1. **Ir a Facturas** → "Nueva factura"
2. **Seleccionar tipo de comprobante:**
   - FC-A: Factura A (para responsables inscriptos)
   - FC-B: Factura B (para monotributistas/consumidor final)
   - FC-C: Factura C (sin IVA)
3. **Completar datos del cliente:**
   - Nombre/Razón Social
   - CUIT (opcional, pero requerido para Factura A)
   - Condición frente al IVA
4. **Agregar items:**
   - Descripción
   - Cantidad
   - Precio unitario
   - Alícuota IVA (0%, 10.5%, 21%, 27%)
5. **Guardar** (se crea como borrador)

### Enviar a ARCA

1. Abrir la factura creada
2. Revisar que todos los datos sean correctos
3. Hacer clic en "Enviar a ARCA"
4. Confirmar el envío
5. El sistema:
   - Obtiene el próximo número de comprobante
   - Envía la factura a ARCA
   - Recibe el CAE (Código de Autorización Electrónico)
   - Genera el PDF automáticamente

### Descargar PDF

Una vez aprobada, la factura tendrá un botón "Descargar PDF" que incluye:
- Datos completos de la factura
- CAE y fecha de vencimiento
- Código QR para verificación en AFIP

## 🔍 Estados de Factura

- **Borrador:** Recién creada, se puede editar y eliminar
- **Pendiente:** Enviándose a ARCA
- **Aprobada:** Autorizada por ARCA con CAE
- **Rechazada:** ARCA rechazó la factura (revisar datos)
- **Anulada:** Factura anulada

## ⚠️ Consideraciones Importantes

### Ambiente Testing vs Producción

- **Testing (Homologación):** Para pruebas, los comprobantes no son válidos legalmente
- **Producción:** Comprobantes válidos legalmente, usar con cuidado

### Tipos de Comprobante según Cliente

- **Factura A:** Para clientes Responsables Inscriptos (discrimina IVA)
- **Factura B:** Para Monotributistas y Consumidores Finales (incluye IVA)
- **Factura C:** Para operaciones exentas de IVA

### Numeración

El sistema obtiene automáticamente el próximo número de comprobante de ARCA para evitar duplicados.

### Seguridad

- Los certificados se almacenan encriptados
- Cada empresa solo ve sus propias facturas
- Las facturas aprobadas no se pueden eliminar

## 🔧 Troubleshooting

### Error: "Certificado inválido"

- Verificar que el certificado sea para "Facturación Electrónica"
- Verificar que no esté vencido
- Verificar la contraseña del certificado

### Error: "CUIT no autorizado"

- Verificar que el CUIT esté habilitado para facturación electrónica en AFIP
- Verificar que el certificado corresponda al CUIT correcto

### Error: "Punto de venta no habilitado"

- Verificar que el punto de venta esté dado de alta en AFIP
- Verificar el número de punto de venta

### Factura rechazada

- Revisar los mensajes de error de ARCA
- Verificar datos del cliente (CUIT, condición IVA)
- Verificar que los montos sean correctos

## 📞 Soporte

Si tienes problemas con el sistema de facturación:

1. Verificar la configuración de ARCA
2. Revisar los logs en `storage/logs/laravel.log`
3. Verificar que los certificados estén vigentes
4. Contactar a soporte técnico

## 🔗 Enlaces Útiles

- [AFIP - Facturación Electrónica](https://www.afip.gob.ar)
- [Documentación WSFE](https://www.afip.gob.ar/ws/)
- [Consultar Comprobantes](https://www.afip.gob.ar/sitio/externos/default.asp)

---

**Versión:** 1.0
**Última actualización:** Noviembre 2025
**Gestior - Sistema de Gestión Comercial**
