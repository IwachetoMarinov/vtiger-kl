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
        // 1. Check if not first day of the month, if yes then exit (to avoid running on the first day of the month)
        if (date('d') !== '01') return;

        // 2. Build date range for the current month
        $date_range = Contacts_CronHelpers::buildMonthlyDateRange();

        // 3 Get all Party codes (client IDs) to process monthly transactions for each client
        $clint_ids =  Contacts_CronHelpers::fetchClientIds();

        echo "Processing Statement of Holdings for " . count($clint_ids) . " clients...\n";
        echo "Date Range: " . $date_range[0] . " to " . $date_range[1] . "\n";

        $service = new Contacts_StatementOfHoldingsService();

        foreach ($clint_ids as $client_id) {
            try {
                $service->processClient($client_id, $date_range);
            } catch (Throwable $e) {
                echo "\nERROR processing client {$client_id}\n";
                echo $e->getMessage() . "\n";
                return 0;
            }
        }
    }
}
