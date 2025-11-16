#!/usr/bin/env bash
set -euo pipefail

echo "🔄 Restarting Xlack environment..."
echo ""

# Work from repository root regardless of where it's called
cd "$(dirname "$0")"

echo "🐳 Restarting Docker containers..."
docker compose restart

echo "⏳ Waiting for services to be ready..."
sleep 5

echo ""
./start-services.sh

echo ""
echo "✅ Environment fully restarted"
echo ""
echo "🌐 URLs:"
echo "   - App:              http://localhost"
echo "   - Reverb WebSocket: http://localhost:8080"
echo "   - Soketi:           http://localhost:6001"
echo ""
