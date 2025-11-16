#!/bin/bash

echo "🚀 Iniciando servicios de Xlack..."

# Detener procesos existentes si los hay
echo "🛑 Deteniendo procesos existentes..."
docker exec xlack-laravel.test-1 pkill -f "reverb:start" 2>/dev/null || true
docker exec xlack-laravel.test-1 pkill -f "queue:work" 2>/dev/null || true

# Corregir permisos
echo "🔧 Corrigiendo permisos..."
docker exec xlack-laravel.test-1 chown -R sail:sail /var/www/html/storage /var/www/html/bootstrap/cache
docker exec xlack-laravel.test-1 chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Iniciar Reverb como usuario sail
echo "▶️  Iniciando Reverb WebSocket Server..."
docker exec -u sail -d xlack-laravel.test-1 php artisan reverb:start --host=0.0.0.0 --port=8080

# Iniciar Queue Worker como usuario sail
echo "▶️  Iniciando Queue Worker..."
docker exec -u sail -d xlack-laravel.test-1 php artisan queue:work --queue=default --tries=1

sleep 2

echo ""
echo "✅ Servicios iniciados correctamente"
echo ""
echo "📋 Estado de los servicios:"
docker ps --format "table {{.Names}}\t{{.Status}}"
echo ""
echo "🔄 Procesos en ejecución:"
docker exec xlack-laravel.test-1 ps aux | grep -E "reverb|queue" | grep -v grep
echo ""
echo "🌐 Aplicación disponible en: http://localhost"
echo "🔌 Reverb WebSocket: http://localhost:8080"
echo "📡 Soketi: http://localhost:6001"
