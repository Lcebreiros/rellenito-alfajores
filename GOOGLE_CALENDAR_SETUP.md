# Configuración de Google Calendar para Gestior

Esta guía te ayudará a configurar la integración de Google Calendar con Gestior para que tus clientes puedan ver sus pedidos agendados directamente en su Google Calendar.

## Características

- ✅ Sincronización automática de pedidos agendados con Google Calendar
- ✅ Actualización automática cuando cambian las fechas
- ✅ Eliminación automática al cancelar o eliminar pedidos
- ✅ Notificaciones automáticas de Google Calendar
- ✅ Cada usuario controla su propia conexión

## Paso 1: Crear un Proyecto en Google Cloud Console

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Asegúrate de que el proyecto esté seleccionado en la parte superior

## Paso 2: Habilitar la API de Google Calendar

1. En el menú lateral, ve a **APIs y servicios** > **Biblioteca**
2. Busca "Google Calendar API"
3. Haz clic en "Google Calendar API"
4. Haz clic en el botón **HABILITAR**

## Paso 3: Configurar la Pantalla de Consentimiento OAuth

1. En el menú lateral, ve a **APIs y servicios** > **Pantalla de consentimiento de OAuth**
2. Selecciona **Externo** como tipo de usuario
3. Haz clic en **CREAR**
4. Completa la información requerida:
   - **Nombre de la aplicación**: Gestior
   - **Correo electrónico de asistencia**: tu correo
   - **Logotipo de la aplicación** (opcional)
   - **Dominios autorizados**: agrega tu dominio (ej: `tudominio.com`)
   - **Correo electrónico del desarrollador**: tu correo
5. Haz clic en **GUARDAR Y CONTINUAR**
6. En **Scopes**, haz clic en **AÑADIR O QUITAR SCOPES**
7. Busca y selecciona:
   - `https://www.googleapis.com/auth/calendar`
   - `https://www.googleapis.com/auth/calendar.events`
8. Haz clic en **ACTUALIZAR** y luego en **GUARDAR Y CONTINUAR**
9. En **Usuarios de prueba** (si estás en modo desarrollo), agrega los correos de los usuarios que podrán probar la integración
10. Haz clic en **GUARDAR Y CONTINUAR**
11. Revisa y haz clic en **VOLVER AL PANEL**

## Paso 4: Crear Credenciales OAuth 2.0

1. En el menú lateral, ve a **APIs y servicios** > **Credenciales**
2. Haz clic en **+ CREAR CREDENCIALES** en la parte superior
3. Selecciona **ID de cliente de OAuth 2.0**
4. En "Tipo de aplicación", selecciona **Aplicación web**
5. Dale un nombre (ej: "Gestior Web")
6. En **URIs de redireccionamiento autorizados**, haz clic en **+ AÑADIR URI**
7. Agrega las siguientes URIs (reemplaza con tu dominio real):
   - Para desarrollo: `http://localhost:8000/google/callback`
   - Para producción: `https://tudominio.com/google/callback`
8. Haz clic en **CREAR**
9. Se mostrará un modal con tu **Client ID** y **Client Secret**
10. **¡IMPORTANTE!** Copia estos valores, los necesitarás en el siguiente paso

## Paso 5: Configurar las Variables de Entorno

1. Abre el archivo `.env` en la raíz de tu proyecto
2. Agrega las siguientes variables (reemplaza con tus valores reales):

```env
GOOGLE_CLIENT_ID=tu_client_id_aqui.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=tu_client_secret_aqui
GOOGLE_REDIRECT_URI=https://tudominio.com/google/callback
```

3. Guarda el archivo

## Paso 6: Configurar el dominio de redirección

Si estás en producción, asegúrate de que:

1. Tu dominio esté correctamente configurado en el archivo `.env`:
   ```env
   APP_URL=https://tudominio.com
   ```

2. Si cambias el `APP_URL`, Laravel automáticamente actualizará la URI de redirección

## Paso 7: Publicar la Aplicación (Producción)

Para que cualquier usuario pueda conectarse (no solo usuarios de prueba):

1. Ve a **Pantalla de consentimiento de OAuth**
2. En la parte superior, verás un botón **PUBLICAR APLICACIÓN**
3. Haz clic en **PUBLICAR APLICACIÓN**
4. Confirma que deseas publicar

**Nota**: Si tu aplicación solicita scopes sensibles o restringidos, es posible que Google requiera una verificación adicional. Para scopes de Calendar como los que estamos usando, generalmente no se requiere verificación.

## Cómo Usar la Integración

### Para los Usuarios

1. Inicia sesión en Gestior
2. Ve al Dashboard
3. En el widget de Calendario, verás un botón **"Conectar"** con el logo de Google
4. Haz clic en el botón
5. Se abrirá una ventana de Google pidiendo permiso para acceder a tu calendario
6. Acepta los permisos
7. Serás redirigido de vuelta a Gestior
8. Ahora verás un badge verde que dice **"Google"** indicando que estás conectado

### Sincronización Automática

Una vez conectado:

- ✅ **Crear pedido agendado**: Se creará automáticamente un evento en Google Calendar
- ✅ **Actualizar fecha**: Se actualizará el evento en Google Calendar
- ✅ **Cancelar pedido**: Se eliminará el evento de Google Calendar
- ✅ **Eliminar pedido**: Se eliminará el evento de Google Calendar

### Desconectar

Para desconectar tu cuenta de Google:

1. Abre el calendario completo (botón con icono de calendario)
2. En el header del modal, verás un botón **"Desconectar Google"**
3. Haz clic y confirma
4. Tu cuenta será desconectada y los eventos futuros no se sincronizarán

## Personalización

### Colores de Eventos

Los eventos se crean con colores específicos según el tipo:

- 🔵 **Pedidos agendados**: Azul (Color ID: 9)
- 🔴 **Pagos**: Rojo (Color ID: 11)
- 🟢 **Compras**: Verde (Color ID: 10)

Puedes cambiar estos colores en `config/google-calendar.php`:

```php
'colors' => [
    'order' => '9',    // Azul
    'payment' => '11', // Rojo
    'purchase' => '10', // Verde
],
```

### Recordatorios

Por defecto, los eventos tienen recordatorios configurados para 60 minutos antes:

- Popup en el navegador
- Email de recordatorio

Puedes cambiar esto en `config/google-calendar.php`:

```php
'default_reminder_minutes' => 60, // Cambiar a los minutos que desees
```

## Solución de Problemas

### Error: "redirect_uri_mismatch"

**Causa**: La URI de redirección no coincide con las configuradas en Google Cloud Console.

**Solución**:
1. Verifica que el `GOOGLE_REDIRECT_URI` en `.env` coincida exactamente con una de las URIs autorizadas en Google Cloud Console
2. Asegúrate de incluir `http://` o `https://` según corresponda
3. No incluyas barras `/` al final de la URI

### Error: "invalid_client"

**Causa**: El Client ID o Client Secret son incorrectos.

**Solución**:
1. Verifica que hayas copiado correctamente el Client ID y Client Secret
2. Asegúrate de no tener espacios al inicio o final de los valores
3. Si regeneraste las credenciales, actualiza los valores en `.env`

### No se sincronizan los eventos

**Posibles causas**:
1. El usuario no ha conectado su cuenta de Google
2. El token ha expirado (debería renovarse automáticamente)
3. El pedido no tiene el flag `is_scheduled` en `true`
4. El pedido no tiene una fecha en `scheduled_for`

**Solución**:
1. Verifica los logs en `storage/logs/laravel.log`
2. Intenta desconectar y volver a conectar la cuenta de Google
3. Verifica que el pedido esté marcado como agendado

### Error: "Access blocked: This app's request is invalid"

**Causa**: La aplicación está en modo de prueba y el usuario no está en la lista de usuarios de prueba.

**Solución**:
1. Ve a **Pantalla de consentimiento de OAuth** en Google Cloud Console
2. En **Usuarios de prueba**, agrega el correo del usuario
3. O publica la aplicación siguiendo el **Paso 7**

## Preguntas Frecuentes

### ¿Los eventos aparecen en el calendario principal?

Sí, todos los eventos se crean en el calendario principal ("primary") del usuario.

### ¿Puedo elegir en qué calendario se crean los eventos?

Actualmente no, pero puedes modificar el código en `app/Services/GoogleCalendarService.php` cambiando `'primary'` por el ID del calendario deseado.

### ¿Los eventos se eliminan si desconecto mi cuenta?

No, los eventos que ya existen en tu Google Calendar permanecerán allí. Solo se detendrá la sincronización futura.

### ¿Puedo ver los eventos de otros usuarios?

No, cada usuario solo puede ver y sincronizar sus propios eventos con su propia cuenta de Google.

### ¿Qué pasa si cambio la fecha de un pedido?

El evento en Google Calendar se actualizará automáticamente con la nueva fecha.

## Seguridad

- ✅ Los tokens de acceso se almacenan encriptados en la base de datos
- ✅ Los tokens de actualización permiten renovar el acceso sin que el usuario tenga que volver a autenticarse
- ✅ Cada usuario controla su propia conexión y puede desconectarse en cualquier momento
- ✅ Los permisos se limitan solo a lectura/escritura del calendario, sin acceso a otros servicios de Google

## Soporte

Si tienes problemas con la configuración, revisa:

1. Los logs de Laravel: `storage/logs/laravel.log`
2. La consola del navegador para ver errores de JavaScript
3. Los logs de Google Cloud Console en **APIs y servicios** > **Credenciales**

Para más ayuda, contacta al equipo de soporte de Gestior.
