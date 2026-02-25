#!/bin/bash

echo "=========================================="
echo "Configuración Automática - Impresora Térmica"
echo "=========================================="
echo ""

# Detectar el sistema operativo
if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    echo "✓ Sistema detectado: Linux"
    OS="linux"
elif [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "win32" ]]; then
    echo "✓ Sistema detectado: Windows"
    OS="windows"
else
    echo "✗ Sistema operativo no soportado: $OSTYPE"
    exit 1
fi

# Obtener ruta absoluta del proyecto
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "✓ Directorio: $SCRIPT_DIR"
echo ""

if [ "$OS" == "linux" ]; then
    # ============ LINUX ============
    echo "Configurando para Linux (systemd)..."
    echo ""

    # Crear archivo de servicio
    SERVICE_FILE="/tmp/thermal-printer.service"
    cat > "$SERVICE_FILE" << EOF
[Unit]
Description=Servidor de Impresión Térmica - Parking
After=network.target

[Service]
Type=simple
User=$USER
WorkingDirectory=$SCRIPT_DIR
ExecStart=$(which node) $SCRIPT_DIR/server.js
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF

    echo "1. Copiando servicio a systemd..."
    sudo cp "$SERVICE_FILE" /etc/systemd/system/thermal-printer.service

    echo "2. Recargando systemd..."
    sudo systemctl daemon-reload

    echo "3. Habilitando inicio automático..."
    sudo systemctl enable thermal-printer

    echo "4. Iniciando servicio ahora..."
    sudo systemctl start thermal-printer

    echo ""
    echo "✓ Servicio instalado correctamente!"
    echo ""
    echo "Comandos útiles:"
    echo "  Ver estado:  sudo systemctl status thermal-printer"
    echo "  Ver logs:    sudo journalctl -u thermal-printer -f"
    echo "  Reiniciar:   sudo systemctl restart thermal-printer"
    echo "  Detener:     sudo systemctl stop thermal-printer"
    echo ""

    # Mostrar estado
    echo "Estado actual:"
    sudo systemctl status thermal-printer --no-pager

elif [ "$OS" == "windows" ]; then
    # ============ WINDOWS ============
    echo "Configurando para Windows..."
    echo ""

    # Crear script batch para iniciar
    BAT_FILE="$SCRIPT_DIR/start-printer-server.bat"
    cat > "$BAT_FILE" << 'EOF'
@echo off
cd /d "%~dp0"
start /min node server.js
EOF

    echo "✓ Script de inicio creado: $BAT_FILE"
    echo ""
    echo "Para configurar inicio automático en Windows:"
    echo ""
    echo "OPCIÓN 1 - Usar Task Scheduler (Recomendado):"
    echo "  1. Presiona Win+R y escribe: taskschd.msc"
    echo "  2. Click en 'Crear tarea básica...'"
    echo "  3. Nombre: Servidor Impresora Térmica"
    echo "  4. Desencadenador: Al iniciar el equipo"
    echo "  5. Acción: Iniciar un programa"
    echo "  6. Programa: $BAT_FILE"
    echo "  7. Finalizar"
    echo ""
    echo "OPCIÓN 2 - Carpeta de inicio:"
    echo "  1. Presiona Win+R y escribe: shell:startup"
    echo "  2. Copia el archivo: $BAT_FILE"
    echo "  3. Pega un acceso directo en la carpeta que se abre"
    echo ""
    echo "Para iniciar manualmente ahora, ejecuta:"
    echo "  $BAT_FILE"
fi

echo ""
echo "=========================================="
echo "Configuración completada"
echo "=========================================="
