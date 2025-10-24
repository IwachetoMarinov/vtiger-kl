<?php

require_once 'includes/main/WebUI.php';
include_once 'modules/Metals/OrderCron.php';
// include_once 'modules/Metals/MetalsCron.php';

echo "Testing password_verify:" . PHP_EOL;
var_dump(password_verify('supersecret', '$2y$10$VpNyRQabaT441vAaTsNGT.1f37G3VfC5ZA4cSjZrhXwpE/Xad2xbe'));
echo PHP_EOL;

try {
    echo "Cron execution in MetalsCron started at " . date('Y-m-d H:i:s') . PHP_EOL;
    // TODO logic to hit ERP and fetch metal prices
    // $job = new MetalsCron();
    // $job->createDailyMetal();

    // Todo logic to create orders
    $job = new OrdersCron();
    $job->createDailyOrders();
    echo "\n Cron execution ended at " . date('Y-m-d H:i:s') . PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
