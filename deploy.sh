#!/bin/bash
# ----------------------------------------------
# Simple MySQL DB Backup Script (full dump)
# Author: Ivaylo Marinov
# ----------------------------------------------

set -euo pipefail

DB_NAME="vtiger_gpm"
DB_USER="root"
DB_PASS=""           
BACKUP_DIR="./db_backups"
COMPRESS=true       

STAMP="$(date +"%Y_%m_%d_%H%M%S")"
OUT_SQL="${BACKUP_DIR}/${DB_NAME}_backup_${STAMP}.sql"
OUT_GZ="${OUT_SQL}.gz"

log() {
  echo "[$(date +"%Y-%m-%d %H:%M:%S")] $1"
}

# Ensure backup directory exists
mkdir -p "$BACKUP_DIR"

# Build mysql auth args safely
MYSQL_AUTH=(-u"$DB_USER")
if [[ -n "${DB_PASS}" ]]; then
  MYSQL_AUTH+=(-p"$DB_PASS")
fi

log "🔄 Creating full dump for database '${DB_NAME}'..."

if [[ "$COMPRESS" == "true" ]]; then
  mysqldump "${MYSQL_AUTH[@]}" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --hex-blob \
    --default-character-set=utf8mb4 \
    "$DB_NAME" | gzip -c > "$OUT_GZ"
  log "✅ Backup created: $OUT_GZ"
else
  mysqldump "${MYSQL_AUTH[@]}" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --hex-blob \
    --default-character-set=utf8mb4 \
    "$DB_NAME" > "$OUT_SQL"
  log "✅ Backup created: $OUT_SQL"
fi

log "✅ Done."
