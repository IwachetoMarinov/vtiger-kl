<?php

require_once 'includes/main/WebUI.php';

try {
    echo "Cron execution in MetalsCron started at " . date('Y-m-d H:i:s') . PHP_EOL;

    // TODO logic to hit ERP and fetch metal prices


    echo "\n Cron execution ended at " . date('Y-m-d H:i:s') . PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
