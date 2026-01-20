#!/bin/bash

DB_USER="root"
DB_NAME="vtiger_gpm"
BACKUP_FILE="vtiger_gpm_$(date +%F).sql.gz"

mysqldump --single-transaction --routines --triggers --events \
  -u "$DB_USER" -p "$DB_NAME" | gzip > "$BACKUP_FILE"

echo "Backup created: $BACKUP_FILE"
