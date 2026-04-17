<?php

// ini_set('display_errors', 1); error_reporting(E_ALL);

require_once __DIR__ . '/cron_bootstrap.php';
require_once __DIR__ . '/modules/Contacts/cron/MonthlyTransactionCron.php';

$cron = new Contacts_MonthlyTransactionCron();
$cron->process();
