#!/bin/bash
# ----------------------------------------------
# MySQL DB Backup Script (SCHEMA ONLY - EMPTY TABLES)
# Author: Ivaylo Marinov
# ----------------------------------------------

set -euo pipefail

DB_NAME="vtiger_gpm"
DB_USER="root"
DB_PASS=""
BACKUP_DIR="./db_backups"
COMPRESS=true

STAMP="$(date +"%Y_%m_%d_%H%M%S")"
OUT_SQL="${BACKUP_DIR}/${DB_NAME}_schema_${STAMP}.sql"
OUT_GZ="${OUT_SQL}.gz"

log() {
  echo "[$(date +"%Y-%m-%d %H:%M:%S")] $1"
}

mkdir -p "$BACKUP_DIR"

MYSQL_AUTH=(-u"$DB_USER")
if [[ -n "${DB_PASS}" ]]; then
  MYSQL_AUTH+=(-p"$DB_PASS")
fi

log "Creating SCHEMA-ONLY (empty tables) vTiger DB dump..."

DUMP_ARGS=(
  --no-data
  --single-transaction
  --routines
  --triggers
  --events
  --add-drop-table
  --hex-blob
  --default-character-set=utf8mb4
)

if [[ "$COMPRESS" == "true" ]]; then
  mysqldump "${MYSQL_AUTH[@]}" "${DUMP_ARGS[@]}" "$DB_NAME" | gzip -c > "$OUT_GZ"
  log "Backup created: $OUT_GZ"
else
  mysqldump "${MYSQL_AUTH[@]}" "${DUMP_ARGS[@]}" "$DB_NAME" > "$OUT_SQL"
  log "Backup created: $OUT_SQL"
fi

log "DONE"
