<?php

require_once 'modules/Contacts/cron/CronHelpers.php';
require_once 'modules/Contacts/cron/ActivitySummaryService.php';
require_once 'modules/Contacts/cron/StatementOfHoldingsService.php';

// ini_set('display_errors', 1); error_reporting(E_ALL);

class YTDReportService
{
    public static function generateForClient(string $client_id, array $date_range = [])
    {
        if (empty($date_range)) $date_range = Contacts_CronHelpers::buildMonthlyDateRange();
        
        try {
            $activityService = new Contacts_ActivitySummaryService();
            $activityService->generateAndStoreForClient($client_id, $date_range);
        } catch (Throwable $e) {
            echo "Error processing activity summary for client $client_id: " . $e->getMessage() . "\n";
        }

        try {
            $holdingsService = new Contacts_StatementOfHoldingsService();
            $holdingsService->processClient($client_id, $date_range);
        } catch (Throwable $e) {
            echo "Error processing statement of holdings for client $client_id: " . $e->getMessage() . "\n";
        }

        return true;
    }
}
