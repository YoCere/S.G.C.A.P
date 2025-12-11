#!/bin/bash
set -e

echo "=== Iniciando configuración de Laravel ==="

# Ejecutar migraciones
php artisan migrate --force

# Ejecutar seeders
php artisan db:seed --force

# Crear enlace de storage
php artisan storage:link || true

echo "=== Configuración completada ==="