#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"

if [[ ! -f .env ]]; then cp .env.example .env 2>/dev/null || true; fi
echo 'Starting project containers...'
docker-compose up -d
echo 'Project containers started.'
