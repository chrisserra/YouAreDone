#!/bin/bash

DB_NAME="cserraco_youaredone"
OUTPUT_FILE="docs/current-data.sql"

mysqldump \
  --no-create-info \
  --skip-triggers \
  --compact \
  $DB_NAME \
  races \
  elections \
  candidates \
  election_candidates \
  candidate_flags \
> $OUTPUT_FILE

echo "Exported current data to $OUTPUT_FILE"