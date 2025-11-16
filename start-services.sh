#!/usr/bin/env bash
set -euo pipefail

echo "🚀 Starting Xlack background services..."

# Always run from repository root (directory containing this script)
cd "$(dirname "$0")"

# Ensure containers are up
./vendor/bin/sail up -d

# Resolve the container ID for the laravel service (robust across project names)
CID=$(docker compose ps -q laravel.test)
if [[ -z "${CID}" ]]; then
	echo "❌ Could not resolve laravel.test container. Is Docker running?" >&2
	exit 1
fi

echo "🛑 Stopping existing Reverb/Queue processes (if any)..."
docker exec "${CID}" bash -lc "pkill -f 'reverb:start' || true; pkill -f 'queue:work' || true"

echo "🔧 Fixing permissions..."
docker exec "${CID}" bash -lc "chown -R sail:sail /var/www/html/storage /var/www/html/bootstrap/cache && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache"

echo "▶️  Starting Reverb WebSocket server (detached)..."
docker exec -u sail -d "${CID}" php artisan reverb:start --host=0.0.0.0 --port=8080

echo "▶️  Starting Queue worker (detached)..."
docker exec -u sail -d "${CID}" php artisan queue:work --queue=default --tries=1

sleep 2

echo ""
echo "✅ Services started"
echo ""
echo "📋 Containers:"
docker compose ps
echo ""
echo "🔄 Running processes inside laravel container:"
docker exec "${CID}" bash -lc "ps aux | grep -E 'reverb|queue' | grep -v grep || true"
echo ""
echo "🌐 App:              http://localhost"
echo "🔌 Reverb WebSocket: http://localhost:8080"
echo "📡 Soketi:           http://localhost:6001"
