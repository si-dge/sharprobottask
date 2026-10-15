#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if docker compose version >/dev/null 2>&1; then
  COMPOSE=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE=(docker-compose)
else
  echo 'Docker Compose is required (docker compose or docker-compose).' >&2
  exit 1
fi

echo 'Stopping project containers...'
"${COMPOSE[@]}" down --remove-orphans
echo 'All project containers are stopped. Docker volumes were preserved.'
