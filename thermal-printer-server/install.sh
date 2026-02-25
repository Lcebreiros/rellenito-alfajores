#!/bin/bash

echo "==========================================="
echo "Instalación Servidor de Impresión Térmica"
echo "==========================================="
echo ""

# Verificar Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js no está instalado"
    echo "   Instalando Node.js..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt-get install -y nodejs
else
    echo "✓ Node.js instalado: $(node --version)"
fi

# Verificar npm
if ! command -v npm &> /dev/null; then
    echo "❌ npm no está instalado"
    exit 1
else
    echo "✓ npm instalado: $(npm --version)"
fi

echo ""
echo "Instalando dependencias..."
npm install

if [ $? -eq 0 ]; then
    echo "✓ Dependencias instaladas correctamente"
else
    echo "❌ Error al instalar dependencias"
    exit 1
fi

# Configurar permisos USB en Linux
if [ "$(uname)" == "Linux" ]; then
    echo ""
    echo "Configurando permisos USB..."
    echo ""
    echo "Para dar acceso a la impresora USB, necesitas:"
    echo "1. Conectar la impresora USB"
    echo "2. Ejecutar 'lsusb' para ver el VID y PID"
    echo "3. Crear regla udev con esos valores"
    echo ""
    echo "Ejemplo:"
    echo "  sudo nano /etc/udev/rules.d/99-escpos.rules"
    echo "  SUBSYSTEM==\"usb\", ATTRS{idVendor}=\"0416\", ATTRS{idProduct}=\"5011\", MODE=\"0666\""
    echo "  sudo udevadm control --reload-rules"
    echo "  sudo udevadm trigger"
    echo ""
fi

# Crear .env si no existe
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✓ Archivo .env creado"
fi

echo ""
echo "==========================================="
echo "Instalación completada"
echo "==========================================="
echo ""
echo "Para iniciar el servidor:"
echo "  npm start"
echo ""
echo "Para probar la conexión:"
echo "  curl http://localhost:9876"
echo ""
