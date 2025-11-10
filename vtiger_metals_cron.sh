#!/bin/bash
# Auto-detect vTiger root (works on localhost and VM)
VTIGER_ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$VTIGER_ROOT"

# Use PHP CLI from system path
PHP_BIN=$(command -v php)

# Run vTiger order cron with file lock to avoid overlapping runs
exec /usr/bin/flock -n "$VTIGER_ROOT/vtiger-order-cron.lock" "$PHP_BIN" -f "$VTIGER_ROOT/cron/vtiger_metals_cron.php" >> "$VTIGER_ROOT/logs/order_cron.log" 2>&1



