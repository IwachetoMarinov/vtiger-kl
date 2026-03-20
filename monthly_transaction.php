<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/modules/Contacts/cron/MonthlyTransactionCron.php';

echo "Starting Monthly Transaction Cron...\n";

$cron = new Contacts_MonthlyTransactionCron();
$cron->process();
