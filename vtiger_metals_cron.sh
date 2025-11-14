#!/bin/bash

# Auto-detect vTiger root
VTIGER_ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$VTIGER_ROOT"

# Detect PHP CLI
PHP_BIN=$(command -v php)

# Run cron with file lock
exec /usr/bin/flock -n "$VTIGER_ROOT/vtiger-order-cron.lock" \
    "$PHP_BIN" -f "$VTIGER_ROOT/cron/vtiger_metals_cron.php" \
    >> /var/www/html/logs/order_cron.log 2>&1
