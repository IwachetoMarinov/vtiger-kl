<?php

// Display errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/main/WebUI.php';
include_once 'modules/Assets/MetalsCron.php';
include_once 'modules/Contacts/models/MetalsAPI.php';
include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/DatabaseSchema.php';
include_once 'dbo_db/HoldingsDB.php';

try {
    echo "File for geting db data " . date('Y-m-d H:i:s') . PHP_EOL;
    // TODO logic to hit ERP and fetch metal prices
    // $job = new MetalsCron();
    // $job->createUpdateDailyMetal();

    // $metalsAPI = new MetalsAPI();
    // $rates = $metalsAPI->getLatestExchangeRate();

    // $table = new dbo_db\DatabaseSchema();
    // $tables = $table->getTables();

    $customer_id = 'D2013';
    $activity = new dbo_db\ActivitySummary();
    $data = $activity->getActivitySummary();

    // $holdings = new dbo_db\HoldingsDB();
    // $customer_id = 'M2001';
    // $data = $holdings->getHoldingsData($customer_id);

    echo '<pre>';
    echo "\n Data fetched from ActivitySummary: " . date('Y-m-d H:i:s') . PHP_EOL;
    // echo "\n Data fetched from HoldingsDB: " . date('Y-m-d H:i:s') . PHP_EOL;
    // var_dump($tables);   
    var_dump($data);
    echo '</pre>';

    echo "\n End of file" . date('Y-m-d H:i:s') . PHP_EOL;
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
