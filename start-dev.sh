#!/bin/bash

# Script para iniciar Laravel + ngrok en desarrollo

echo "🚀 Iniciando servidor de desarrollo..."

# Iniciar Laravel en background
php artisan serve --host=0.0.0.0 --port=8000 &
LARAVEL_PID=$!

echo "✅ Laravel iniciado en http://0.0.0.0:8000 (PID: $LARAVEL_PID)"

# Esperar 2 segundos para que Laravel inicie
sleep 2

# Iniciar ngrok
echo "🌐 Iniciando ngrok..."
ngrok http 8000

# Cleanup al salir
trap "kill $LARAVEL_PID" EXIT
