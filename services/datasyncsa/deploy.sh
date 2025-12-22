#!/bin/bash
set -e
echo "🚀 Desplegando Estático: datasyncsa..."
rsync -av --delete --exclude='.git/' ./repo/ ./www/
echo "✅ Hecho."
