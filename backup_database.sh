#!/bin/bash

#######################################
# vTiger Complete Database Backup
# Creates full database dump in db_backups/
#######################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DB_NAME="vtiger_db"
DB_USER="root"
DB_HOST="localhost"
BACKUP_DIR="db_backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="vtiger_complete_${TIMESTAMP}.sql"
COMPRESSED_FILE="${BACKUP_FILE}.gz"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  vTiger Database Backup Script${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Create backup directory if it doesn't exist
if [ ! -d "$BACKUP_DIR" ]; then
    echo -e "${YELLOW}Creating backup directory: $BACKUP_DIR${NC}"
    mkdir -p "$BACKUP_DIR"
fi

echo -e "${YELLOW}Database:${NC} $DB_NAME"
echo -e "${YELLOW}Backup location:${NC} $BACKUP_DIR/$COMPRESSED_FILE"
echo ""

# Prompt for password
echo -e "${YELLOW}Enter MySQL password for user '$DB_USER':${NC}"
read -s DB_PASS
echo ""

# Test database connection
echo -e "${YELLOW}Testing database connection...${NC}"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME" 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Connection successful${NC}"
else
    echo -e "${RED}✗ Connection failed! Check your credentials.${NC}"
    exit 1
fi

# Get table count
TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "SHOW TABLES" 2>/dev/null | wc -l)
TABLE_COUNT=$((TABLE_COUNT - 1)) # Subtract header line
echo -e "${YELLOW}Tables to backup:${NC} $TABLE_COUNT"
echo ""

# Create backup
echo -e "${YELLOW}Starting backup...${NC}"
echo -e "${BLUE}This may take a few minutes depending on database size...${NC}"

mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    --routines \
    --triggers \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --complete-insert \
    --hex-blob \
    2>/dev/null > "$BACKUP_DIR/$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database dump created${NC}"
else
    echo -e "${RED}✗ Backup failed!${NC}"
    exit 1
fi

# Get uncompressed size
UNCOMPRESSED_SIZE=$(du -h "$BACKUP_DIR/$BACKUP_FILE" | cut -f1)
echo -e "${YELLOW}Uncompressed size:${NC} $UNCOMPRESSED_SIZE"

# Compress backup
echo -e "${YELLOW}Compressing backup...${NC}"
gzip "$BACKUP_DIR/$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backup compressed${NC}"
else
    echo -e "${RED}✗ Compression failed!${NC}"
    exit 1
fi

# Get compressed size
COMPRESSED_SIZE=$(du -h "$BACKUP_DIR/$COMPRESSED_FILE" | cut -f1)
echo -e "${YELLOW}Compressed size:${NC} $COMPRESSED_SIZE"

# Verify backup
echo -e "${YELLOW}Verifying backup integrity...${NC}"
gunzip -t "$BACKUP_DIR/$COMPRESSED_FILE" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backup integrity verified${NC}"
else
    echo -e "${RED}✗ Backup verification failed!${NC}"
    exit 1
fi

# Cleanup old backups (keep last 5)
echo -e "${YELLOW}Cleaning up old backups (keeping last 5)...${NC}"
cd "$BACKUP_DIR"
ls -t vtiger_complete_*.sql.gz | tail -n +6 | xargs -r rm --
cd ..

REMAINING_BACKUPS=$(ls -1 "$BACKUP_DIR"/vtiger_complete_*.sql.gz 2>/dev/null | wc -l)
echo -e "${YELLOW}Remaining backups:${NC} $REMAINING_BACKUPS"

# Summary
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Backup Completed Successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "${YELLOW}File:${NC} $BACKUP_DIR/$COMPRESSED_FILE"
echo -e "${YELLOW}Size:${NC} $COMPRESSED_SIZE"
echo -e "${YELLOW}Tables:${NC} $TABLE_COUNT"
echo ""
echo -e "${BLUE}To restore this backup on production:${NC}"
echo -e "gunzip < $BACKUP_DIR/$COMPRESSED_FILE | mysql -u root -p vtiger_production"
echo ""
echo -e "${BLUE}To view backup content without restoring:${NC}"
echo -e "gunzip < $BACKUP_DIR/$COMPRESSED_FILE | less"
echo ""