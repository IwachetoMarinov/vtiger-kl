<?php

require_once __DIR__ . '/cron_bootstrap.php';
require_once __DIR__ . '/modules/YTDReports/cron/Monthly.php';

$cron = new YTDReports_Monthly_Cron();
$cron->process();