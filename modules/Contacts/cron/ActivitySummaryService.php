<?php
/* modules/Contacts/cron/ActivitySummaryService.php */

include_once 'dbo_db/Helper.php';
include_once 'dbo_db/ActivitySummary.php';
// require_once 'libraries/Smarty/libs/Smarty.class.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

class Contacts_ActivitySummaryService
{

    public function __construct() {}

    public function generateAndStoreForClient($client_id, $date_range = [])
    {
        // 1. Init ActivitySummary to fetch transactions for the date range
        $activity = new dbo_db\ActivitySummary();

        // 2 init variables
        $selected_year = !empty($date_range) ? date('Y', strtotime($date_range[0])) : date('Y');
        $start_date = !empty($date_range) ? $date_range[0] : date('Y-m-01');
        $end_date = !empty($date_range) ? $date_range[1] : date('Y-m-t');

        // 3.Get transactions for the client and date range
        $activities =  $activity->getMonthlyTransactions($client_id, $start_date, $end_date);

        // 4. Get contact record for the client ID
        $contactRecord = $this->getContactRecordByClientId($client_id);

        // 5. Get all Currencies
        $currency_list = $activity->getTransactionCurrencies($client_id);
        $selected_currency = !empty($currency_list) ? $currency_list[0] : '';

        // 6. Get full company record for the client
        $company_record = Contacts_DefaultCompany_View::process();

        // 6. Get full company record for the client
        $company_full_address = Helper::getCompanyFullAddress($company_record);

        // 7. Get Opening balance for the client and date range
        $opening_balance = $activity->getActivitySummaryOpeningBalance($client_id, $selected_currency, $start_date);

        // 8. Create pages for the transactions to be used in PDF generation
        $pages = $this->makeDataPage($activities);

        $smarty = new Smarty();

        $smarty->setCompileDir(dirname(__DIR__, 3) . '/test/templates_c/');
        $smarty->setCacheDir(dirname(__DIR__, 3) . '/test/cache/');
        $smarty->setConfigDir(dirname(__DIR__, 3) . '/test/config/');

        $templateRoot = dirname(__DIR__, 3) . '/layouts/v7/modules';
        $smarty->registerPlugin('modifier', 'vtemplate_path', function ($templateName, $moduleName) use ($templateRoot) {
            return $templateRoot . '/' . $moduleName . '/' . $templateName;
        });

        $smarty->assign('RECORD_MODEL', $contactRecord);
        $smarty->assign('TRANSACTIONS', $activities);
        $smarty->assign('COMPANY', $company_record);
        $smarty->assign('PAGES', $pages);
        $smarty->assign('OPENING_BALANCE', $opening_balance);
        $smarty->assign('COMPANY_FULL_ADDRESS', $company_full_address);
        $smarty->assign('ENABLE_DOWNLOAD_BUTTON', false);
        $smarty->assign('EARLIEST_DATE', $start_date ? date('Y-M-d', strtotime($start_date)) : null);
        $smarty->assign('LATEST_DATE', $end_date ? date('Y-M-d', strtotime($end_date)) : null);

        $templatePath = dirname(__DIR__, 3) . '/layouts/v7/modules/Contacts/ActivtySummeryPrintPreview.tpl';
        $html = $smarty->fetch('file:' . $templatePath);


        $pdfPath = $this->generatePdf($html, $client_id);

        echo "<pre>";
        echo "Client ID: $client_id\n";
        echo "PDF Path: $pdfPath\n";
        echo "Exists: " . (file_exists($pdfPath) ? 'YES' : 'NO') . "\n";
        echo "</pre>";
    }

    protected function getContactRecordByClientId($client_id)
    {
        $db = PearDatabase::getInstance();

        $query = "
        SELECT c.contactid
        FROM vtiger_contactscf ccf
        INNER JOIN vtiger_contactdetails c ON c.contactid = ccf.contactid
        INNER JOIN vtiger_crmentity ce ON ce.crmid = c.contactid
        WHERE ccf.cf_898 = ?
        AND ce.deleted = 0
        LIMIT 1
    ";

        $result = $db->pquery($query, [$client_id]);
        $row = $db->fetch_array($result);

        if (!$row) return null;

        return Vtiger_Record_Model::getInstanceById($row['contactid'], 'Contacts');
    }

    protected function makeDataPage($transaction)
    {
        $totalPage = 1;
        if (count($transaction) > 22) {
            $totaldataAfterFirstPage = count($transaction) - 22;
            $totalPage = ceil($totaldataAfterFirstPage / 30) + 1;
        }
        return $totalPage;
    }

    protected function generatePdf($html, $client_id)
    {
        global $root_directory;

        $fileName = $client_id . "_activity-summary";
        $htmlPath = $root_directory . $fileName . '.html';
        $pdfPath = $root_directory . $fileName . '.pdf';

        file_put_contents($htmlPath, $html);

        $command = 'wkhtmltopdf --enable-local-file-access -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking '
            . escapeshellarg($htmlPath) . ' '
            . escapeshellarg($pdfPath) . ' 2>&1';

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        echo '<pre>';
        echo "Command:\n" . $command . "\n\n";
        echo "Return code: " . $returnVar . "\n";
        echo "Output:\n";
        print_r($output);
        echo "PDF exists: " . (file_exists($pdfPath) ? 'YES' : 'NO') . "\n";
        echo '</pre>';

        unlink($htmlPath);

        return $pdfPath;
    }
}
