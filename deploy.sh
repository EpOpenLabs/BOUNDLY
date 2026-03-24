#!/bin/bash
#
# BOUNDLY Deployment Script
# ==========================
#
# Este script es un EJEMPLO base para deployar aplicaciones BOUNDLY.
# Adáptalo a tu infraestructura (Forge, Vapor, SSH directo, etc.)
#
# Uso:
#   bash deploy.sh
#

set -e

echo "🚀 Starting BOUNDLY deployment..."

# 1. Run migrations (production mode)
echo "📦 Running database migrations..."
php artisan core:migrate --force

# 2. Cache metadata for production
echo "⚡ Caching metadata..."
php artisan core:cache

# 3. Cache config (optional, skip if using env vars)
# echo "🔧 Caching config..."
# php artisan config:cache

# 4. Cache routes (optional)
# echo "🛤️ Caching routes..."
# php artisan route:cache

# 5. Clear views cache
# echo "🖼️ Clearing views..."
# php artisan view:clear

echo "✅ Deployment complete!"

#
# NOTAS:
# ======
#
# - Si usas Laravel Forge: agrega estos comandos en "Deploy Script"
# - Si usas Envoyer: crea un deploy script hook
# - Si usas Vapor: configura en vapor.yml
# - Si usas SSH directo: ejecuta este script via SSH
#
# COMANDOS ADICIONALES (descomentar si necesario):
# ------------------------------------------------
#
# Regenerar documentación OpenAPI:
#   php artisan core:docs
#
# Clear all caches:
#   php artisan optimize:clear
#
# Restart queue workers (si usas queues):
#   php artisan queue:restart
#
# Clear OPCache (PHP 8+):
#   php artisan octane:reload
#
