#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

echo 'Building project images...'
docker-compose build "$@"
echo 'Project images built.'