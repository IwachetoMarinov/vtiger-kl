<?php

include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/Helper.php';

class Contacts_ActivtySummeryPrintPreview_View extends Vtiger_Index_View
{

    protected $record = null;

    public function preProcess(Vtiger_Request $request, $display = false)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        // Getting model to reuse it in parent
        if (!$this->record) $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $selected_currency = $request->get('ActivtySummeryCurrency');
        $selected_year = $request->get('ActivtySummeryDate');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $dateField = 'document_date'; // or 'posting_date'

        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');

        $companyRecord = Contacts_DefaultCompany_View::process();

        $activity = new dbo_db\ActivitySummary();
        // Get all transactions for the client
        $transactions = $activity->getActivitySummary($clientID);

        $selected_currency = trim($selected_currency);

        if (!empty($selected_currency)) {
            $transactions = array_filter($transactions, function ($txn) use ($selected_currency) {
                return isset($txn['currency']) && trim($txn['currency']) === $selected_currency;
            });
            $transactions = array_values($transactions);
        }

        if (!empty($start_date)) {
            $startTs = strtotime($start_date . ' 00:00:00');
            $transactions = array_values(array_filter($transactions, function ($txn) use ($startTs, $dateField) {
                if (empty($txn[$dateField])) return false;
                return strtotime($txn[$dateField] . ' 00:00:00') >= $startTs;
            }));
        }

        if (!empty($end_date)) {
            $endTs = strtotime($end_date . ' 23:59:59');
            $transactions = array_values(array_filter($transactions, function ($txn) use ($endTs, $dateField) {
                if (empty($txn[$dateField])) return false;
                return strtotime($txn[$dateField] . ' 23:59:59') <= $endTs;
            }));
        }

        // Year filter
        if (!empty($selected_year)) {
            $transactions = array_values(array_filter($transactions, function ($item) use ($selected_year) {
                if (empty($item['document_date'])) return false;
                $itemYear = date('Y', strtotime($item['document_date']));
                return $itemYear === (string) $selected_year;
            }));
        }

        // Order transactions by date ascending
        usort($transactions, function ($a, $b) use ($dateField) {
            $dateA = !empty($a[$dateField]) ? strtotime($a[$dateField] . ' 00:00:00') : 0;
            $dateB = !empty($b[$dateField]) ? strtotime($b[$dateField] . ' 00:00:00') : 0;
            return $dateA <=> $dateB;
        });

        // Order transactions by amount_in_account_currency ascending
        // usort($transactions, function ($a, $b) {
        //     $amtA = isset($a['amount_in_account_currency']) ? floatval($a['amount_in_account_currency']) : 0;
        //     $amtB = isset($b['amount_in_account_currency']) ? floatval($b['amount_in_account_currency']) : 0;
        //     return $amtA <=> $amtB;
        // });

        $earliestDate = null;
        $latestDate = null;

        // New logic for finding earliest and latest dates from the filtered transactions
        if (isset($start_date) && !empty($start_date)  && isset($end_date) && !empty($end_date)) {
            $earliestDate = strtotime($start_date . ' 00:00:00');
            $latestDate = strtotime($end_date . ' 23:59:59');
        } else {
            // use selected_year to determine start and end dates and start will be Jan 1st of the year and end will be Dec 31st of the year
            if (isset($selected_year) && !empty($selected_year)) {
                $earliestDate = strtotime($selected_year . '-01-01 00:00:00');
                $latestDate = strtotime($selected_year . '-12-31 23:59:59');
            }
        }

        $company_full_address = Helper::getCompanyFullAddress($companyRecord);

        // Get opening balance for the activity summary
        // $start_opening_balance_date = $transactions[0][$dateField] ?? null;
        $start_opening_balance_date = $earliestDate ? date('Y-m-d', $earliestDate) : null;
        // Extract 1 day before the earliest transaction date for opening balance calculation
        // if ($start_opening_balance_date) 
        //     $start_opening_balance_date = date('Y-m-d', strtotime($start_opening_balance_date . ' -1 day'));

        // var_dump($start_opening_balance_date); // Debugging line to check the calculated opening balance date
        $opening_balance = $activity->getActivitySummaryOpeningBalance($clientID, $selected_currency, $start_opening_balance_date);
        
        $recordModel = $this->record->getRecord();
        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('PAGES', $this->makeDataPage($transactions));
        $viewer->assign('TRANSACTIONS', $transactions);
        $viewer->assign('OPENING_BALANCE', $opening_balance);
        $viewer->assign('EARLIEST_DATE', $earliestDate ? date('Y-M-d', $earliestDate) : null);
        $viewer->assign('LATEST_DATE', $latestDate ? date('Y-M-d', $latestDate) : null);
        $viewer->assign('COMPANY', $companyRecord);
        $viewer->assign('COMPANY_FULL_ADDRESS', $company_full_address);
        if ($request->get('PDFDownload')) {
            $viewer->assign('ENABLE_DOWNLOAD_BUTTON', false);
            $html = $viewer->view('ActivtySummeryPrintPreview.tpl', $moduleName, true);
            $this->downloadPDF($html);
        } else {
            $viewer->assign('ENABLE_DOWNLOAD_BUTTON', true);
            $viewer->view('ActivtySummeryPrintPreview.tpl', $moduleName);
        }
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

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');

        $fileName = $clientID . "_activity-summary";
        $handle = fopen($root_directory . $fileName . '.html', 'a') or die('Cannot open file:  ');
        fwrite($handle, $html);
        fclose($handle);

        exec("wkhtmltopdf --enable-local-file-access  -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking " . $root_directory . "$fileName.html " . $root_directory . "$fileName.pdf");
        unlink($root_directory . $fileName . '.html');

        header("Content-type: application/pdf");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=" . $clientID . " - AS as of " . date('d M Y') . ".pdf");
        header("Content-Description: PHP Generated Data");
        ob_clean();
        flush();
        readfile($root_directory . "$fileName.pdf");
        unlink($root_directory . "$fileName.pdf");
        exit;
    }
}
