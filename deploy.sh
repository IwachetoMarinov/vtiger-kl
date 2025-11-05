#!/bin/bash
# ----------------------------------------------
# vTiger Solo Dev Auto Deploy Script (Schema diff only + Logging)
# Author: Ivaylo Marinov
# ----------------------------------------------

DB_NAME="vtiger_gpm"
DB_USER="root"
DB_PASS=""
BACKUP_DIR="./db_backups"
DATE=$(date +"%Y-%m-%d %H:%M:%S")
STAMP=$(date +"%Y_%m_%d_%H%M")

TEMP_SCHEMA="$BACKUP_DIR/tmp_schema.sql"
LAST_SCHEMA=$(ls -t "$BACKUP_DIR"/*_schema_*.sql 2>/dev/null | head -n 1)
DIFF_FILE="$BACKUP_DIR/${DB_NAME}_changes_${STAMP}.sql"
GIT_BRANCH="develop"

mkdir -p "$BACKUP_DIR"


# 1️⃣ Dump current DB schema (no data, no comments)
log "🔄 Dumping current schema for '$DB_NAME' (no data)..."
mysqldump -u"$DB_USER" -p"$DB_PASS" --no-data --skip-comments "$DB_NAME" > "$TEMP_SCHEMA"
if [ $? -ne 0 ]; then
  log "❌ Schema dump failed. Aborting!"
  exit 1
fi

# 2️⃣ Compare to last schema (if exists)
if [ -f "$LAST_SCHEMA" ]; then
  log "🔍 Comparing with last schema: $LAST_SCHEMA"

  diff -u "$LAST_SCHEMA" "$TEMP_SCHEMA" | grep -v "^--- Dump completed" > "$DIFF_FILE"

  if [ -s "$DIFF_FILE" ]; then
    log "✅ Schema changes detected — saving new schema and diff."
    mv "$TEMP_SCHEMA" "$BACKUP_DIR/${DB_NAME}_schema_${STAMP}.sql"
  else
    log "ℹ️ No schema differences detected — cleaning up temp files."
    rm -f "$TEMP_SCHEMA" "$DIFF_FILE"
  fi
else
  log "⚠️ No previous schema found — saving first reference schema."
  mv "$TEMP_SCHEMA" "$BACKUP_DIR/${DB_NAME}_schema_${STAMP}.sql"
fi

# 3️⃣ Git operations
log "📦 Staging Git changes..."
git add "$BACKUP_DIR"

echo "✍️  Enter commit message (or press Enter for default):"
read COMMIT_MSG
if [ -z "$COMMIT_MSG" ]; then
  COMMIT_MSG="Auto-deploy (schema changes only) on $STAMP"
fi

git commit -m "$COMMIT_MSG" >/dev/null 2>&1
git push origin "$GIT_BRANCH" >/dev/null 2>&1
log "🚀 Git push completed (branch: $GIT_BRANCH)."

log "✅ Deploy finished successfully."
echo "------------------------------------------"
