#!/bin/bash
set -euo pipefail

OUTPUT_FILE="docs/current-data.sql"
MYSQLDUMP_BIN="/opt/homebrew/opt/mysql-client/bin/mysqldump"
ENV_FILE=".env"

SSH_KEY="$HOME/.ssh/id_rsa"
SSH_HOST="cserraco@68.66.224.56"

LOCAL_PORT=3310
REMOTE_PORT=3306

if [ ! -x "$MYSQLDUMP_BIN" ]; then
  echo "mysqldump not found at $MYSQLDUMP_BIN" >&2
  exit 1
fi

if [ ! -f "$ENV_FILE" ]; then
  echo ".env file not found at repo root" >&2
  exit 1
fi

set -a
source "$ENV_FILE"
set +a

DB_NAME="${DB_NAME:-}"
DUMP_USER="${EXPORT_DB_USER:-${DB_USER:-}}"
DUMP_PASS="${EXPORT_DB_PASS:-${DB_PASS:-}}"

if [ -z "$DB_NAME" ]; then
  echo "DB_NAME is missing in .env" >&2
  exit 1
fi

if [ -z "$DUMP_USER" ]; then
  echo "No dump user found. Set EXPORT_DB_USER or DB_USER in .env" >&2
  exit 1
fi

if lsof -i TCP:${LOCAL_PORT} >/dev/null 2>&1; then
  echo "SSH tunnel already active on port ${LOCAL_PORT}, reusing it..."
else
  echo "Opening SSH tunnel..."
  ssh -i "$SSH_KEY" -f -N -L ${LOCAL_PORT}:127.0.0.1:${REMOTE_PORT} "$SSH_HOST"
fi

echo "Exporting database snapshot..."

MYSQL_PWD="$DUMP_PASS" "$MYSQLDUMP_BIN" \
  --host=127.0.0.1 \
  --port=$LOCAL_PORT \
  --user="$DUMP_USER" \
  --protocol=TCP \
  --no-create-info \
  --skip-triggers \
  --compact \
  --single-transaction \
  --complete-insert \
  "$DB_NAME" \
  offices \
  election_types \
  flags \
  races \
  elections \
  candidates \
  election_candidates \
  candidate_flags \
> "$OUTPUT_FILE"

echo "Exported current data to $OUTPUT_FILE"