#!/bin/bash
set -euo pipefail

REPO_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO_DIR"

echo "==> Working in: $REPO_DIR"

if command -v php >/dev/null 2>&1; then
  echo "==> Clearing PHP opcache..."
  php -r 'function_exists("opcache_reset") && opcache_reset();' || true
else
  echo "==> PHP not found in PATH, skipping opcache reset."
fi

echo "==> Done."