<?php

/* modules/Contacts/cron/MonthlyTransactionCron.php */
include_once 'ActivitySummaryService.php';

class Contacts_MonthlyTransactionCron
{

    public function __construct()
    {
        // check for monthly transaction table and create if not exists
        $this->checkCreateMonthlyTransactionTable();
    }

    public function process()
    {
        // 0. Check if not first day of the month, if yes then exit (to avoid running on the first day of the month)
        if (date('d') !== '01') return;

        // 1. Build date range for the current month
        $date_range = $this->buildMonthlyDateRange();

        $service = new Contacts_ActivitySummaryService();

        // 2 Get all Party codes (client IDs) to process monthly transactions for each client
        $clint_ids =  $this->fetchClientIds();

        // Loop through each client and process their transactions for the month
        foreach ($clint_ids as $client_id) {
            $service->generateAndStoreForClient($client_id, $date_range);

            // break after first iteration for testing, remove this in production
            // break;
        }
    }

    protected function buildMonthlyDateRange()
    {
        // Get curent month and year        $month = date('m');
        $year = date('Y');
        $month = date('m');
        $startDate = date('Y-m-d', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime($startDate));
        return [$startDate, $endDate];
    }

    protected function checkCreateMonthlyTransactionTable()
    {
        // Check if the monthly transaction table exists and create if not
        $db = PearDatabase::getInstance();
        $tableName = 'vtiger_monthly_transactions';
        $query = "SHOW TABLES LIKE '$tableName'";
        $result = $db->pquery($query, []);

        if ($db->num_rows($result) == 0) {
            // Table does not exist, create it
            $createQuery = "CREATE TABLE $tableName (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_id INT NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->pquery($createQuery, []);
        }
    }

    protected function fetchClientIds()
    {
        $db = PearDatabase::getInstance();

        $query = "
        SELECT DISTINCT cf_898 AS client_id
        FROM vtiger_contactscf
        WHERE cf_898 IS NOT NULL AND cf_898 != ''
    ";

        $result = $db->pquery($query, []);

        $clientIds = [];
        while ($row = $db->fetch_array($result)) {
            $clientIds[] = $row['client_id'];
        }

        return $clientIds;
    }
}
