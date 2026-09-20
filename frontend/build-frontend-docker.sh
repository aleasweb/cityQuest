#!/bin/bash
set -e

echo "🔨 Сборка frontend через Docker..."

cd "$(dirname "$0")/web"

# Собрать образ
docker build -f Dockerfile.build -t cityquest-frontend-builder .

# Запустить контейнер и скопировать dist
CONTAINER_ID=$(docker create cityquest-frontend-builder)
docker cp "$CONTAINER_ID:/app/dist" .
docker rm "$CONTAINER_ID"

echo "✅ Frontend собран в frontend/web/dist/"
echo "🔄 Перезапускаю nginx..."

cd ../..
docker compose restart nginx

echo "✅ Готово! Откройте http://cityquest.test"
