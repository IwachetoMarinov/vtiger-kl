<?php


require_once 'includes/main/WebUI.php';



try {
    echo "Cron execution in EmailsCron started at " . date('Y-m-d H:i:s') . PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}

// Empty row
echo "\n";

echo "Cron execution ended at " . date('Y-m-d H:i:s') . PHP_EOL;
