#!/usr/bin/env bash
set -euo pipefail

# --------- CONFIG (edit or export env vars) ----------
MYSQL_HOST="${MYSQL_HOST:-localhost}"
MYSQL_PORT="${MYSQL_PORT:-3306}"
MYSQL_USER="${MYSQL_USER:-vtigeruser}"
MYSQL_PASS="${MYSQL_PASS:-StrongPassword123!}"
FRESH_DB="${FRESH_DB:-vtiger_gpm}"   # your fresh installation DB name
OLD_DUMP="${1:-db_backups/vtiger_gpm_schema_2026_01_20_110515.sql}"

TMP_FRESH="__tmp_fresh_schema_$$"
TMP_OLD="__tmp_old_schema_$$"

OUT_DIR="${OUT_DIR:-db_diff_out}"
mkdir -p "$OUT_DIR"

MYSQL=(mysql -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_USER")
MYSQLDUMP=(mysqldump -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_USER" --skip-comments --compact)

if [[ -n "$MYSQL_PASS" ]]; then
  MYSQL+=(-p"$MYSQL_PASS")
  MYSQLDUMP+=(-p"$MYSQL_PASS")
fi

# --------- HELPERS ----------
cleanup() {
  echo "Cleaning up temp databases..."
  "${MYSQL[@]}" -e "DROP DATABASE IF EXISTS \`$TMP_FRESH\`; DROP DATABASE IF EXISTS \`$TMP_OLD\`;" >/dev/null 2>&1 || true
}
trap cleanup EXIT

echo "Using fresh DB: $FRESH_DB"
echo "Using old dump: $OLD_DUMP"
test -f "$OLD_DUMP"

echo "Creating temp databases..."
"${MYSQL[@]}" -e "DROP DATABASE IF EXISTS \`$TMP_FRESH\`; CREATE DATABASE \`$TMP_FRESH\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
"${MYSQL[@]}" -e "DROP DATABASE IF EXISTS \`$TMP_OLD\`;   CREATE DATABASE \`$TMP_OLD\`   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Importing fresh schema into $TMP_FRESH..."
"${MYSQLDUMP[@]}" --no-data "$FRESH_DB" | "${MYSQL[@]}" "$TMP_FRESH"

echo "Extracting schema-only from old dump into $OUT_DIR/old_schema_only.sql ..."
# Remove data + lock statements; keep CREATE/ALTER and similar schema parts.
# (This is intentionally conservative; adjust filters if your dump has special constructs.)
sed -E \
  -e '/^INSERT /Id' \
  -e '/^REPLACE /Id' \
  -e '/^LOCK TABLES /Id' \
  -e '/^UNLOCK TABLES;$/Id' \
  -e '/^\/\*!40000 ALTER TABLE .* DISABLE KEYS \*\/;$/Id' \
  -e '/^\/\*!40000 ALTER TABLE .* ENABLE KEYS \*\/;$/Id' \
  -e '/^\/\*!40101 SET @OLD_CHARACTER_SET_CLIENT=/Id' \
  -e '/^\/\*!40101 SET @OLD_CHARACTER_SET_RESULTS=/Id' \
  -e '/^\/\*!40101 SET @OLD_COLLATION_CONNECTION=/Id' \
  -e '/^\/\*!40101 SET NAMES /Id' \
  -e '/^\/\*!40103 SET @OLD_TIME_ZONE=/Id' \
  -e '/^\/\*!40103 SET TIME_ZONE=/Id' \
  -e '/^\/\*!40014 SET @OLD_UNIQUE_CHECKS=/Id' \
  -e '/^\/\*!40014 SET UNIQUE_CHECKS=/Id' \
  -e '/^\/\*!40014 SET @OLD_FOREIGN_KEY_CHECKS=/Id' \
  -e '/^\/\*!40014 SET FOREIGN_KEY_CHECKS=/Id' \
  -e '/^\/\*!40101 SET @OLD_SQL_MODE=/Id' \
  -e '/^\/\*!40101 SET SQL_MODE=/Id' \
  -e '/^\/\*!40111 SET @OLD_SQL_NOTES=/Id' \
  -e '/^\/\*!40111 SET SQL_NOTES=/Id' \
  "$OLD_DUMP" > "$OUT_DIR/old_schema_only.sql"

echo "Importing old schema into $TMP_OLD..."
# In case dump contains CREATE DATABASE/USE statements, force DB with --database and ignore USE:
# mysql doesn't have a "ignore USE" switch, so we strip it.
sed -E \
  -e 's/^USE `[^`]+`;/-- stripped USE statement;/' \
  -e 's/^CREATE DATABASE .*;/-- stripped CREATE DATABASE;/' \
  "$OUT_DIR/old_schema_only.sql" | "${MYSQL[@]}" "$TMP_OLD" || {
    echo "ERROR: Importing old schema failed. Check $OUT_DIR/old_schema_only.sql for unsupported SQL."
    exit 1
  }

echo "Dumping normalized schema snapshots..."
"${MYSQLDUMP[@]}" --no-data "$TMP_FRESH" > "$OUT_DIR/fresh_schema.sql"
"${MYSQLDUMP[@]}" --no-data "$TMP_OLD"   > "$OUT_DIR/old_schema.sql"

echo "Creating diff (for manual review)..."
diff -u "$OUT_DIR/fresh_schema.sql" "$OUT_DIR/old_schema.sql" > "$OUT_DIR/schema.diff" || true

echo "Generating migration draft: $OUT_DIR/migration.sql"
: > "$OUT_DIR/migration.sql"
echo "-- Migration draft generated on $(date -u)" >> "$OUT_DIR/migration.sql"
echo "-- Source: $OLD_DUMP" >> "$OUT_DIR/migration.sql"
echo "-- Fresh DB: $FRESH_DB" >> "$OUT_DIR/migration.sql"
echo "" >> "$OUT_DIR/migration.sql"

# --------- 1) Missing tables (in old, not in fresh) ----------
echo "Finding missing tables..."
MISSING_TABLES=$(
  "${MYSQL[@]}" -N -e "
    SELECT o.table_name
    FROM information_schema.tables o
    LEFT JOIN information_schema.tables f
      ON f.table_schema='$TMP_FRESH' AND f.table_name=o.table_name
    WHERE o.table_schema='$TMP_OLD'
      AND o.table_type='BASE TABLE'
      AND f.table_name IS NULL
    ORDER BY o.table_name;"
)

if [[ -n "$MISSING_TABLES" ]]; then
  echo "-- Tables present in OLD but missing in FRESH" >> "$OUT_DIR/migration.sql"
  while IFS= read -r t; do
    [[ -z "$t" ]] && continue
    echo "" >> "$OUT_DIR/migration.sql"
    echo "-- CREATE TABLE for missing table: $t" >> "$OUT_DIR/migration.sql"
    "${MYSQLDUMP[@]}" --no-data "$TMP_OLD" "$t" >> "$OUT_DIR/migration.sql"
  done <<< "$MISSING_TABLES"
else
  echo "-- No missing tables found." >> "$OUT_DIR/migration.sql"
fi

# --------- 2) Missing columns (in old, not in fresh) ----------
echo "Finding missing columns..."
# We generate ADD COLUMN statements with type/null/default/extra/comment.
"${MYSQL[@]}" -N -e "
SELECT CONCAT(
  'ALTER TABLE `', o.table_name, '` ADD COLUMN `', o.column_name, '` ',
  o.column_type,
  IF(o.is_nullable='NO',' NOT NULL',' NULL'),
  IF(o.column_default IS NULL,'', CONCAT(' DEFAULT ', QUOTE(o.column_default))),
  IF(o.extra='', '', CONCAT(' ', o.extra)),
  IF(o.column_comment='', '', CONCAT(' COMMENT ', QUOTE(o.column_comment))),
  ';'
) AS stmt
FROM information_schema.columns o
JOIN information_schema.tables ot
  ON ot.table_schema=o.table_schema AND ot.table_name=o.table_name AND ot.table_type='BASE TABLE'
LEFT JOIN information_schema.columns f
  ON f.table_schema='$TMP_FRESH' AND f.table_name=o.table_name AND f.column_name=o.column_name
WHERE o.table_schema='$TMP_OLD'
  AND f.column_name IS NULL
  AND o.table_name IN (
    SELECT o2.table_name
    FROM information_schema.tables o2
    JOIN information_schema.tables f2
      ON f2.table_schema='$TMP_FRESH' AND f2.table_name=o2.table_name
    WHERE o2.table_schema='$TMP_OLD'
      AND o2.table_type='BASE TABLE'
      AND f2.table_type='BASE TABLE'
  )
ORDER BY o.table_name, o.ordinal_position;
" > "$OUT_DIR/missing_columns.sql"

if [[ -s "$OUT_DIR/missing_columns.sql" ]]; then
  echo "" >> "$OUT_DIR/migration.sql"
  echo "-- Columns present in OLD but missing in FRESH (common tables)" >> "$OUT_DIR/migration.sql"
  cat "$OUT_DIR/missing_columns.sql" >> "$OUT_DIR/migration.sql"
else
  echo "" >> "$OUT_DIR/migration.sql"
  echo "-- No missing columns found." >> "$OUT_DIR/migration.sql"
fi

echo "Done."
echo "Artifacts:"
echo "  - $OUT_DIR/schema.diff           (full schema diff for review)"
echo "  - $OUT_DIR/migration.sql         (draft migration: create missing tables + add missing columns)"
echo "  - $OUT_DIR/missing_columns.sql   (column-only statements)"
