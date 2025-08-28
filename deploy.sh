#!/usr/bin/env bash
set -euo pipefail

### ====== CONFIG LOCAL ======
HOST="54.232.62.137"                      # <-- PONÉ TU IP o dominio
KEY_PATH="$HOME/rellenito-alfajores-key.pem"   # <-- ruta a tu PEM
SSH_OPTS="-i $KEY_PATH -o StrictHostKeyChecking=no"

### ====== CONFIG REMOTO ======
USER="ec2-user"
APP_DIR="/var/www/alfajores"
RELEASES_DIR="$APP_DIR/releases"
CURRENT_LINK="$APP_DIR/current"
RELEASE="$(date +%Y%m%d%H%M%S)"
REMOTE_RELEASE_DIR="$RELEASES_DIR/$RELEASE"

### ====== BUILD LOCAL (Vite + Composer) ======
echo "==> Build de assets (Vite) y Composer (local)"
npm ci
npm run build
composer install --no-dev --optimize-autoloader

ZIP_FILE="../release-$RELEASE.zip"
echo "==> Empaquetando $ZIP_FILE"
zip -rq "$ZIP_FILE" . \
  -x "node_modules/*" ".git/*" ".env" "*.zip"

### ====== SUBIR A EC2 ======
echo "==> Subiendo ZIP a $HOST"
scp $SSH_OPTS "$ZIP_FILE" "$USER@$HOST:/home/$USER/"

### ====== PREPARAR Y PUBLICAR EN EL SERVER ======
echo "==> Publicando release en el server"
ssh $SSH_OPTS "$USER@$HOST" bash -s <<EOF
  set -euo pipefail

  # Crear carpetas base
  sudo mkdir -p "$RELEASES_DIR"
  sudo chown -R $USER:$USER "$APP_DIR" "$RELEASES_DIR"

  # Descomprimir nuevo release
  mkdir -p "$REMOTE_RELEASE_DIR"
  unzip -q "/home/$USER/$(basename $ZIP_FILE)" -d "$REMOTE_RELEASE_DIR"
  rm -f "/home/$USER/$(basename $ZIP_FILE)"

  # Mantener el .env existente (si ya lo tenías en el server)
  if [ -f "$CURRENT_LINK/.env" ]; then
    cp "$CURRENT_LINK/.env" "$REMOTE_RELEASE_DIR/.env"
  fi

  # Permisos para Laravel
  sudo chown -R nginx:nginx "$REMOTE_RELEASE_DIR/storage" "$REMOTE_RELEASE_DIR/bootstrap/cache" || true
  sudo find "$REMOTE_RELEASE_DIR/storage" -type d -exec chmod 775 {} \\;
  sudo chmod -R 775 "$REMOTE_RELEASE_DIR/bootstrap/cache"

  # Symlink atómico a 'current'
  ln -sfn "$REMOTE_RELEASE_DIR" "$CURRENT_LINK"

  # Symlink de storage (por si faltaba)
  sudo -u nginx php "$CURRENT_LINK/artisan" storage:link || true

  # Cachear config/rutas/vistas (opcional, seguro)
  sudo -u nginx php "$CURRENT_LINK/artisan" config:cache || true
  sudo -u nginx php "$CURRENT_LINK/artisan" route:cache || true
  sudo -u nginx php "$CURRENT_LINK/artisan" view:cache || true

  # Migraciones (forzado en prod)
  if ! sudo -u nginx php "$CURRENT_LINK/artisan" migrate --force; then
    echo "[WARN] migrate falló; la app igualmente quedó publicada" >&2
  fi

  # Recargar servicios web
  sudo nginx -t && sudo systemctl reload nginx
  sudo systemctl restart php-fpm

  # Limpiar releases viejos (conservar 5)
  ls -1dt "$RELEASES_DIR"/* | tail -n +6 | xargs -r sudo rm -rf
EOF

echo "✅ Deploy OK. Abrí: http://$HOST"
echo "ℹ️  Si necesitás rollback: cambiá el symlink 'current' a un release anterior en $RELEASES_DIR"
