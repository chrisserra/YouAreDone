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

DEPLOY_SSH_HOST="${DEPLOY_SSH_HOST:-68.66.224.56}"
DEPLOY_SSH_USER="${DEPLOY_SSH_USER:-cserraco}"
DEPLOY_APP_DIR="${DEPLOY_APP_DIR:-/home/cserraco/youaredone.org}"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-master}"

echo "==> Pulling latest code on web server..."
ssh "${DEPLOY_SSH_USER}@${DEPLOY_SSH_HOST}" "
  set -e
  cd '${DEPLOY_APP_DIR}'
  echo '==> Server repo:' \$(pwd)
  git fetch origin
  git checkout '${DEPLOY_BRANCH}'
  git pull --ff-only origin '${DEPLOY_BRANCH}'
"

echo "==> Done."