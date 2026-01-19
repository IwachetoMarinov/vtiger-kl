#!/bin/bash
# ----------------------------------------------
# MySQL DB Backup Script (schema only: tables, no data)
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

log "🔄 Creating schema-only dump for database '${DB_NAME}' (no INSERTs)..."

DUMP_ARGS=(
  --no-data                # ✅ no rows / no INSERT statements
  --single-transaction
  --routines
  --triggers
  --events
  --hex-blob
  --default-character-set=utf8mb4
)

if [[ "$COMPRESS" == "true" ]]; then
  mysqldump "${MYSQL_AUTH[@]}" "${DUMP_ARGS[@]}" "$DB_NAME" | gzip -c > "$OUT_GZ"
  log "✅ Schema backup created: $OUT_GZ"
else
  mysqldump "${MYSQL_AUTH[@]}" "${DUMP_ARGS[@]}" "$DB_NAME" > "$OUT_SQL"
  log "✅ Schema backup created: $OUT_SQL"
fi

log "✅ Done."
