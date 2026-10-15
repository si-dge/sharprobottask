#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"

if [[ "$EUID" -eq 0 && -n "${SUDO_USER:-}" && "$SUDO_USER" != "root" ]]; then
  exec sudo -u "$SUDO_USER" -H "$PWD/$(basename "$0")" "$@"
fi

force_build=false
if [[ "${1-}" == "--build" ]]; then
  force_build=true
  shift
fi

if [[ -S /var/run/docker.sock ]] && ! id -nG | tr ' ' '\n' | grep -qx docker \
    && getent group docker | grep -Eq '(^|:)([^:]*,)?'"$USER"'(,|$)'; then
  printf -v setup_command '%q ' "$0" "$@"
  exec sg docker -c "$setup_command"
fi

if [[ ! -f .env ]]; then cp .env.example .env 2>/dev/null || true; fi
available_kb=$(df -Pk . | awk 'NR == 2 {print $4}')
minimum_available_kb=$((2 * 1024 * 1024))
if [[ "${available_kb:-0}" -lt "$minimum_available_kb" ]]; then
  echo "Not enough disk space for Docker build: $((available_kb / 1024)) MB available, at least 2048 MB required." >&2
  echo 'Free Docker build cache with: docker builder prune -af' >&2
  exit 1
fi
if docker compose version >/dev/null 2>&1; then
  COMPOSE=(docker compose)
else
  echo 'Docker Compose v2 is required. Install the Docker Compose plugin and use "docker compose", not legacy "docker-compose".' >&2
  exit 1
fi
if [[ "$force_build" == true ]]; then
  echo 'Building images without using the cache...'
  "${COMPOSE[@]}" build --no-cache
else
  echo 'Updating images from the current source (Docker cache enabled)...'
  "${COMPOSE[@]}" build
fi

"${COMPOSE[@]}" up -d
echo 'Waiting for API...'
for attempt in {1..30}; do
  if curl -fsS http://localhost:8080/api/health >/dev/null 2>&1; then
    echo 'Ready: http://localhost'
    echo 'API:   http://localhost:8080/api/health'
    exit 0
  fi
  sleep 2
done
echo 'API did not become healthy. Logs:'
"${COMPOSE[@]}" logs --tail=80 app
exit 1
