<?php

// TEST cron job 
// /usr/bin/php /var/www/html/monthly_sh.php
// /usr/bin/php /var/www/html/monthly_transaction.php

require_once __DIR__ . '/cron_bootstrap.php';
require_once __DIR__ . '/modules/Contacts/cron/MonthlyStatementOfHoldings.php';

$cron = new Contacts_MonthlyStatementOfHoldings();
$cron->process();