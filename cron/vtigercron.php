<?php
chdir(dirname(__FILE__) . '/../');
require_once 'vendor/autoload.php';
require_once 'includes/main/WebUI.php';

global $VTIGER_CRON_CONFIGURATION;

require_once 'cron/modules/Emails/EmailsCron.php';
require_once 'cron/modules/Reports/ReportsCron.php';
require_once 'cron/modules/SMSNotifier/SMSNotifierCron.php';
require_once 'cron/modules/Vtiger/VtigerCron.php';

require_once 'include/utils/utils.php';
require_once 'includes/main/Cron.php';

echo "Cron execution started at " . date('Y-m-d H:i:s') . PHP_EOL;

try {
    $cron = new Vtiger_Cron();
    $cron->run();
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}

echo "Cron execution ended at " . date('Y-m-d H:i:s') . PHP_EOL;
?>
