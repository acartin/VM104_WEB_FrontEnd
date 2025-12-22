#!/bin/bash
set -e
echo "🛠️  Preparando Laravel Admin Console..."
# Crear el enlace simbólico de www a repo/public si no existe
if [ ! -L ./www ]; then
    rm -rf ./www
    ln -s ./repo/public ./www
fi
cd repo
# Aquí irían tus comandos de optimización
# composer install --no-dev
# php artisan config:cache
echo "✅ Estructura Laravel vinculada a ./www"
