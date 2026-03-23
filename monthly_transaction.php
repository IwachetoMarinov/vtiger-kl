<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$root = __DIR__;
chdir($root);
require_once $root . '/vendor/autoload.php';

require_once $root . '/config.inc.php';
require_once $root . '/include/utils/utils.php';
require_once $root . '/vtlib/Vtiger/Module.php';

// In many vTiger builds this is what pulls in the runtime globals/helpers.
require_once $root . '/includes/main/WebUI.php';

// Extra safety for some builds
if (!function_exists('vglobal') && file_exists($root . '/includes/runtime/Globals.php')) {
    require_once $root . '/includes/runtime/Globals.php';
}

require_once $root . '/modules/Users/Users.php';
require_once $root . '/modules/Contacts/cron/MonthlyTransactionCron.php';

global $current_user;
$current_user = Users::getActiveAdminUser();

if (!$current_user || empty($current_user->id)) {
    throw new Exception('Failed to initialize execution user');
}

echo "Starting Monthly Transaction Cron...\n";

$cron = new Contacts_MonthlyTransactionCron();
$cron->process();