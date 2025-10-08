#!/bin/bash

# Salir si ocurre un error
set -e

echo "📌 Generando clave de aplicación..."
php artisan key:generate

echo "🔐 Generando JWT secret..."
php artisan jwt:secret --force

echo "🧱 Ejecutando migraciones..."
php artisan migrate

echo "🌱 Ejecutando seeders..."
php artisan db:seed

echo "✅ Proyecto inicializado correctamente."
