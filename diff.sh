#!/usr/bin/env bash
set -euo pipefail

MYSQL_HOST="${MYSQL_HOST:-127.0.0.1}"
MYSQL_PORT="${MYSQL_PORT:-3306}"
MYSQL_USER="${MYSQL_USER:-root}"
MYSQL_PASS="${MYSQL_PASS:-}"
FRESH_DB="${FRESH_DB:-vtiger_fresh}"
OLD_DUMP="${1:-db_backups/vtiger_gpm_schema_2026_01_20_110515.sql}"

OUT_DIR="${OUT_DIR:-/tmp/db_diff_out}"
mkdir -p "$OUT_DIR"

# Prefix for imported "old schema" tables inside the same DB
OLD_PREFIX="__oldschema__"

MYSQL=(mysql -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_USER" --protocol=tcp)
MYSQLDUMP=(mysqldump -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"$MYSQL_USER" --skip-comments --compact)

if [[ -n "$MYSQL_PASS" ]]; then
  MYSQL+=(-p"$MYSQL_PASS")
  MYSQLDUMP+=(-p"$MYSQL_PASS")
fi

if [[ ! -f "$OLD_DUMP" ]]; then
  echo "ERROR: Old dump not found: $OLD_DUMP" >&2
  exit 1
fi

echo "Using fresh DB: $FRESH_DB"
echo "Using old dump: $OLD_DUMP"
echo "Output dir: $OUT_DIR"

cleanup() {
  echo "Cleaning up prefixed old-schema tables..."
  "${MYSQL[@]}" -N -e "
    SELECT CONCAT('DROP TABLE IF EXISTS \`', table_schema, '\`.\`', table_name, '\`;')
    FROM information_schema.tables
    WHERE table_schema='${FRESH_DB}'
      AND table_name LIKE '${OLD_PREFIX}%';
  " | "${MYSQL[@]}" "$FRESH_DB" >/dev/null 2>&1 || true
}
trap cleanup EXIT

echo "Checking CREATE TABLE privilege in $FRESH_DB..."
# Quick privilege test: create & drop a dummy table
"${MYSQL[@]}" "$FRESH_DB" -e "CREATE TABLE IF NOT EXISTS ${OLD_PREFIX}__perm_test (id INT); DROP TABLE ${OLD_PREFIX}__perm_test;" \
  || { echo "ERROR: Your MySQL user cannot CREATE/DROP tables in $FRESH_DB." >&2; exit 1; }

echo "Extracting schema-only from old dump..."
sed -E \
  -e '/^INSERT /Id' \
  -e '/^REPLACE /Id' \
  -e '/^LOCK TABLES /Id' \
  -e '/^UNLOCK TABLES;$/Id' \
  "$OLD_DUMP" > "$OUT_DIR/old_schema_only.sql"

echo "Rewriting table names in old schema with prefix '$OLD_PREFIX'..."
# This renames CREATE TABLE `x` -> CREATE TABLE `__oldschema__x`
# and ALTER TABLE `x` -> ALTER TABLE `__oldschema__x`
# It assumes backticked table names (common in mysqldump). If your dump differs, we can adjust.
sed -E \
  -e "s/^(CREATE TABLE \`)([^`]+)(\`)/\1${OLD_PREFIX}\2\3/I" \
  -e "s/^(ALTER TABLE \`)([^`]+)(\`)/\1${OLD_PREFIX}\2\3/I" \
  -e "s/^(DROP TABLE IF EXISTS \`)([^`]+)(\`)/\1${OLD_PREFIX}\2\3/I" \
  -e "s/^USE `[^`]+`;/-- stripped USE;/" \
  -e "s/^CREATE DATABASE .*;/-- stripped CREATE DATABASE;/" \
  "$OUT_DIR/old_schema_only.sql" > "$OUT_DIR/old_schema_prefixed.sql"

echo "Importing prefixed old schema into $FRESH_DB..."
"${MYSQL[@]}" "$FRESH_DB" < "$OUT_DIR/old_schema_prefixed.sql" || {
  echo "ERROR: Importing prefixed schema failed. Inspect $OUT_DIR/old_schema_prefixed.sql" >&2
  exit 1
}

echo "Dumping normalized schema snapshots for review..."
"${MYSQLDUMP[@]}" --no-data "$FRESH_DB" > "$OUT_DIR/fresh_schema.sql"
"${MYSQLDUMP[@]}" --no-data "$FRESH_DB" $( "${MYSQL[@]}" -N -e "
  SELECT table_name FROM information_schema.tables
  WHERE table_schema='$FRESH_DB' AND table_name LIKE '${OLD_PREFIX}%'
" ) > "$OUT_DIR/old_prefixed_schema.sql" 2>/dev/null || true

diff -u "$OUT_DIR/fresh_schema.sql" "$OUT_DIR/old_prefixed_schema.sql" > "$OUT_DIR/schema.diff" || true

echo "Generating migration draft: $OUT_DIR/migration.sql"
: > "$OUT_DIR/migration.sql"
echo "-- Migration draft generated on $(date -u)" >> "$OUT_DIR/migration.sql"
echo "-- Fresh DB: $FRESH_DB" >> "$OUT_DIR/migration.sql"
echo "-- Old dump: $OLD_DUMP" >> "$OUT_DIR/migration.sql"
echo "" >> "$OUT_DIR/migration.sql"

echo "Finding missing tables (present in OLD dump, absent in fresh)..."
"${MYSQL[@]}" -N -e "
SELECT REPLACE(o.table_name, '${OLD_PREFIX}', '') AS base_table
FROM information_schema.tables o
LEFT JOIN information_schema.tables f
  ON f.table_schema='${FRESH_DB}' AND f.table_name=REPLACE(o.table_name,'${OLD_PREFIX}','')
WHERE o.table_schema='${FRESH_DB}'
  AND o.table_name LIKE '${OLD_PREFIX}%'
  AND o.table_type='BASE TABLE'
  AND f.table_name IS NULL
ORDER BY base_table;
" > "$OUT_DIR/missing_tables.txt"

if [[ -s "$OUT_DIR/missing_tables.txt" ]]; then
  echo "-- Missing tables (create them)" >> "$OUT_DIR/migration.sql"
  while IFS= read -r t; do
    [[ -z "$t" ]] && continue
    echo "" >> "$OUT_DIR/migration.sql"
    echo "-- CREATE TABLE for: $t" >> "$OUT_DIR/migration.sql"
    "${MYSQLDUMP[@]}" --no-data "$FRESH_DB" "${OLD_PREFIX}${t}" \
      | sed -E "s/\`${OLD_PREFIX}${t}\`/\`${t}\`/g" >> "$OUT_DIR/migration.sql"
  done < "$OUT_DIR/missing_tables.txt"
else
  echo "-- No missing tables found." >> "$OUT_DIR/migration.sql"
fi

echo "Finding missing columns (in OLD table but not in fresh table)..."
"${MYSQL[@]}" -N -e "
SELECT CONCAT(
  'ALTER TABLE `', base.table_name, '` ADD COLUMN `', oldc.column_name, '` ',
  oldc.column_type,
  IF(oldc.is_nullable='NO',' NOT NULL',' NULL'),
  IF(oldc.column_default IS NULL,'', CONCAT(' DEFAULT ', QUOTE(oldc.column_default))),
  IF(oldc.extra='', '', CONCAT(' ', oldc.extra)),
  IF(oldc.column_comment='', '', CONCAT(' COMMENT ', QUOTE(oldc.column_comment))),
  ';'
) AS stmt
FROM information_schema.tables base
JOIN information_schema.tables oldt
  ON oldt.table_schema=base.table_schema
  AND oldt.table_name=CONCAT('${OLD_PREFIX}', base.table_name)
JOIN information_schema.columns oldc
  ON oldc.table_schema=oldt.table_schema AND oldc.table_name=oldt.table_name
LEFT JOIN information_schema.columns freshc
  ON freshc.table_schema=base.table_schema
  AND freshc.table_name=base.table_name
  AND freshc.column_name=oldc.column_name
WHERE base.table_schema='${FRESH_DB}'
  AND base.table_type='BASE TABLE'
  AND base.table_name NOT LIKE '${OLD_PREFIX}%'
  AND freshc.column_name IS NULL
ORDER BY base.table_name, oldc.ordinal_position;
" > "$OUT_DIR/missing_columns.sql"

if [[ -s "$OUT_DIR/missing_columns.sql" ]]; then
  echo "" >> "$OUT_DIR/migration.sql"
  echo "-- Missing columns (add them)" >> "$OUT_DIR/migration.sql"
  cat "$OUT_DIR/missing_columns.sql" >> "$OUT_DIR/migration.sql"
else
  echo "" >> "$OUT_DIR/migration.sql"
  echo "-- No missing columns found." >> "$OUT_DIR/migration.sql"
fi

echo "Done. Outputs:"
echo "  - $OUT_DIR/migration.sql"
echo "  - $OUT_DIR/missing_tables.txt"
echo "  - $OUT_DIR/missing_columns.sql"
echo "  - $OUT_DIR/schema.diff"
