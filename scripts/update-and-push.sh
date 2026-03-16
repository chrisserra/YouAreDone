#!/bin/bash
set -euo pipefail

REPO_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO_DIR"

echo "==> Working in: $REPO_DIR"

# Export latest DB snapshot if your helper script exists
if [ -f "./scripts/export-current-data.sh" ]; then
  echo "==> Exporting current data snapshot..."
  ./scripts/export-current-data.sh
else
  echo "==> Skipping export-current-data.sh (not found)"
fi

# Show changed files
echo "==> Git status before add:"
git status --short

# Add modified files
echo "==> Staging changes..."
git add .

# Stop if nothing changed
if git diff --cached --quiet; then
  echo "==> No changes to commit."
  exit 0
fi

# Commit message: use first argument if provided, otherwise timestamp
COMMIT_MESSAGE="${1:-update repo $(date '+%Y-%m-%d %H:%M:%S')}"

echo "==> Committing with message: $COMMIT_MESSAGE"
git commit -m "$COMMIT_MESSAGE"

echo "==> Pushing to current branch..."
git push

echo "==> Done."