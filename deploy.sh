#!/bin/bash
# ----------------------------------------------
# vTiger Solo Dev Auto Deploy Script
# Author: Ivaylo Marinov
# ----------------------------------------------

# === CONFIGURATION ===
DB_NAME="vtiger_gpm"
DB_USER="root"
DB_PASS=""
BACKUP_DIR="./db_backups"
DATE=$(date +"%Y_%m_%d_%H%M")
SQL_FILE="$BACKUP_DIR/${DB_NAME}_$DATE.sql"
GIT_BRANCH="main"

# === CHECKS ===
if [ ! -d "$BACKUP_DIR" ]; then
  mkdir -p "$BACKUP_DIR"
fi

# === 1. BACKUP DATABASE ===
echo "🔄 Backing up database '$DB_NAME'..."
mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$SQL_FILE"
if [ $? -ne 0 ]; then
  echo "❌ Database backup failed. Aborting!"
  exit 1
fi
echo "✅ Database backup created: $SQL_FILE"

# === 2. GIT ADD & COMMIT ===
echo "📦 Staging Git changes..."
git add .

echo "✍️  Enter commit message (or press Enter for default):"
read COMMIT_MSG
if [ -z "$COMMIT_MSG" ]; then
  COMMIT_MSG="Auto-deploy on $DATE"
fi

git commit -m "$COMMIT_MSG"

# === 3. PUSH TO GITHUB ===
echo "🚀 Pushing to GitHub ($GIT_BRANCH)..."
git push origin "$GIT_BRANCH"

# === 4. OPTIONAL: KEEP ONLY LAST 5 BACKUPS ===
echo "🧹 Cleaning old backups (keeping last 5)..."
ls -t "$BACKUP_DIR"/*.sql | tail -n +6 | xargs -r rm --

echo "✅ Deploy complete!"
