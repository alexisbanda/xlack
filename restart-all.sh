#!/bin/bash

echo "🔄 Reiniciando ambiente de Xlack..."
echo ""

# Reiniciar contenedores
echo "🐳 Reiniciando contenedores Docker..."
cd /home/alexis/Sites/xlack/xlack
docker compose restart

# Esperar a que los contenedores estén listos
echo "⏳ Esperando a que los servicios estén listos..."
sleep 5

# Iniciar servicios internos
echo ""
./start-services.sh

echo ""
echo "✅ Ambiente reiniciado completamente"
echo ""
echo "🌐 URLs disponibles:"
echo "   - Aplicación: http://localhost"
echo "   - Reverb WebSocket: http://localhost:8080"
echo "   - Soketi: http://localhost:6001"
echo ""
