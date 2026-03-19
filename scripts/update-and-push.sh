#!/bin/bash
set -euo pipefail

REPO_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO_DIR"

echo "==> Working in: $REPO_DIR"

if [ -x "./scripts/export-schema.sh" ]; then
  echo "==> Exporting schema snapshot..."
  ./scripts/export-schema.sh
else
  echo "==> Skipping export-schema.sh (missing or not executable)"
fi

if [ -x "./scripts/export-current-data.sh" ]; then
  echo "==> Exporting current data snapshot..."
  ./scripts/export-current-data.sh
else
  echo "==> Skipping export-current-data.sh (missing or not executable)"
fi

echo "==> Git status before add:"
git status --short

echo "==> Staging changes..."
git add .

if git diff --cached --quiet; then
  echo "==> No changes to commit."
  exit 0
fi

COMMIT_MESSAGE="${1:-update snapshot $(date '+%Y-%m-%d %H:%M:%S')}"

echo "==> Committing with message: $COMMIT_MESSAGE"
git commit -m "$COMMIT_MESSAGE"

CURRENT_BRANCH="$(git rev-parse --abbrev-ref HEAD)"
echo "==> Pushing to branch: $CURRENT_BRANCH"
git push origin "$CURRENT_BRANCH"

echo "==> Deploying to server..."
ssh cserraco@68.66.224.56 '
  set -e
  cd /home/cserraco/youaredone.org
  git fetch origin
  git reset --hard origin/master
  git clean -fd
  ./scripts/deploy.sh
'

echo "==> Done."