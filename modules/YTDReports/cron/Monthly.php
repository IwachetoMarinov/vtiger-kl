<?php

include_once 'modules/Contacts/cron/CronHelpers.php';
require_once 'modules/YTDReports/services/YTDReportService.php';

class YTDReports_Monthly_Cron
{
    public function process()
    {
        $date_range = Contacts_CronHelpers::buildMonthlyDateRange();

        $client_ids = Contacts_CronHelpers::fetchClientIds();

        echo "Starting YTD Reports Cron for date range: " . $date_range[0] . " to " . $date_range[1] . "\n";
        echo "Found " . count($client_ids) . " clients to process\n";

        foreach ($client_ids as $client_id) {
            echo "Processing YTD report for client ID: $client_id\n";
            YTDReportService::generateForClient($client_id, $date_range);
        }

        return true;
    }
}
