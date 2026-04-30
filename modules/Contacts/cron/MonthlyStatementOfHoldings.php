<?php

/* modules/Contacts/cron/MonthlyStatementOfHoldings.php */
include_once 'CronHelpers.php';
include_once 'StatementOfHoldingsService.php';

// TEST cron job 
// /usr/bin/php /var/www/html/gpm-zu/monthly_sh.php
// /usr/bin/php /var/www/html/gpm-zu/monthly_transaction.php

class Contacts_MonthlyStatementOfHoldings
{
    public function process()
    {
        echo "Starting Monthly Statement of Holdings Cron...\n";

        // 1. Check if not first day of the month, if yes then exit (to avoid running on the first day of the month)
        // if (date('d') !== '01') return;

        // 2. Build date range for the current month
        $date_range = Contacts_CronHelpers::buildMonthlyDateRange();

        // 3 Get all Party codes (client IDs) to process monthly transactions for each client
        $clint_ids =  Contacts_CronHelpers::fetchClientIds();

        $service = new Contacts_StatementOfHoldingsService();

        foreach ($clint_ids as $client_id) {
            $service->processClient($client_id, $date_range);
        }
    }
}
