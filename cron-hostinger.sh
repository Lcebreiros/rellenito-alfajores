#!/bin/bash

# Ruta al proyecto
cd /home/u590843796/domains/gestior.com.ar/public_html/panel

# Ejecutar tareas programadas (backups, limpieza)
/usr/bin/php -d disable_functions="" artisan schedule:run >> /dev/null 2>&1

# Procesar jobs en cola (emails, Google Calendar, notificaciones)
/usr/bin/php -d disable_functions="" artisan queue:work --stop-when-empty >> /dev/null 2>&1
