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

CURRENT_BRANCH="$(git rev-parse --abbrev-ref HEAD)"

if git diff --cached --quiet; then
  echo "==> No changes to commit."
  echo "==> Skipping commit/push and deploying current $CURRENT_BRANCH"
else
  COMMIT_MESSAGE="${1:-update snapshot $(date '+%Y-%m-%d %H:%M:%S')}"

  echo "==> Committing with message: $COMMIT_MESSAGE"
  git commit -m "$COMMIT_MESSAGE"

  echo "==> Pushing to branch: $CURRENT_BRANCH"
  git push origin "$CURRENT_BRANCH"
fi

echo "==> Deploying to server..."
ssh cserraco@68.66.224.56 '
  set -e
  cd /home/cserraco/youaredone.org
  git fetch origin
  git checkout master
  git reset --hard origin/master
  git clean -fd
  ./scripts/deploy.sh
'

echo "==> Done."