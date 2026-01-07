#!/bin/bash
set -e
echo "🚀 Desplegando Chat Client..."
# Aquí podrías añadir un comando de 'npm run build' si luego usas React
rsync -av --delete --exclude='.git/' ./repo/ ./www/
echo "✅ Hecho."
