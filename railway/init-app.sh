#!/bin/bash
set -e

echo "=== INICIANDO CONFIGURACIÓN LARAVEL ==="
echo "Fecha: $(date)"
echo "======================================"

# 1. Ejecutar migraciones
echo "[1/4] 🗃️ Ejecutando migraciones..."
php artisan migrate --force

# 2. Ejecutar seeders (roles, permisos y usuario admin)
echo "[2/4] 👤 Creando roles, permisos y usuario admin..."
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=DatabaseSeeder --force

# 3. Crear enlaces y cache
echo "[3/4] ⚙️ Configurando sistema..."
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Completado
echo "[4/4] ✅ Configuración completada!"
echo ""

echo ""
echo "🌐 URL: https://sgcap-production.up.railway.app"
echo "======================================"