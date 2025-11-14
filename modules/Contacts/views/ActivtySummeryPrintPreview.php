<?php

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

class Contacts_ActivtySummeryPrintPreview_View extends Vtiger_Index_View
{

    public function preProcess(Vtiger_Request $request, $display = false)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        // Getting model to reuse it in parent
        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
        }
    }

    // function fetchOROSOft(Vtiger_Request $request)
    // {
    //     include_once 'modules/Settings/OROSoft/api.php';
    //     $recordModel = $this->record->getRecord();
    //     $clientID = $recordModel->get('cf_950');
    //     $year = $request->get('ActivtySummeryDate');
    //     $comId = $recordModel->get('related_entity');
    //     return array(
    //         'Transactions' => getOROSoftTransaction($clientID, $year, $comId)
    //     );
    // }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        // $oroSOftData = $this->fetchOROSOft($request);

        // Temporary hardcoded data for testing
        $oroSOftData = [
            'Transactions' => [
                [
                    'voucher_no' => 'SAL-00123',
                    'doctype' => 'Sales Invoice',
                    'posting_date' => '2024-01-15',
                    'voucher_type' => 'Sale of Gold 1kg',
                    'amount_in_account_currency' => 15000.00,
                    'amount' => 15000.00,
                    'usdVal' => 15000.00,
                ],
                [
                    'voucher_no' => 'PUR-00077',
                    'doctype' => 'Purchase Invoice',
                    'posting_date' => '2024-01-05',
                    'voucher_type' => 'Purchase of Silver 5kg',
                    'amount_in_account_currency' => -4500.00,
                    'amount' => -4500.00,
                    'usdVal' => -4500.00,
                ],
                [
                    'voucher_no' => 'REC-00210',
                    'doctype' => 'Receipt',
                    'posting_date' => '2024-01-02',
                    'voucher_type' => 'Deposit Payment',
                    'amount_in_account_currency' => 8000.00,
                    'amount' => 8000.00,
                    'usdVal' => 8000.00,
                ],
                // Example TOTAL row (if template expects it)
                [
                    'voucher_no' => 'Total',
                    'doctype' => '',
                    'posting_date' => '',
                    'voucher_type' => '',
                    'amount_in_account_currency' => 18500.00,
                    'amount' => 18500.00,
                    'usdVal' => 18500.00,
                ]
            ]
        ];


        $recordModel = $this->record->getRecord();
        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('PAGES', $this->makeDataPage($oroSOftData['Transactions']));
        $viewer->assign('OROSOFT_TRANSACTION', $oroSOftData['Transactions']);
        $viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($recordModel->get('related_entity')));
        if ($request->get('PDFDownload')) {
            $viewer->assign('ENABLE_DOWNLOAD_BUTTON', false);
            $html = $viewer->view('ActivtySummeryPrintPreview.tpl', $moduleName, true);
            $this->downloadPDF($html);
        } else {
            $viewer->assign('ENABLE_DOWNLOAD_BUTTON', true);
            $viewer->view('ActivtySummeryPrintPreview.tpl', $moduleName);
        }
    }

    function makeDataPage($transaction)
    {
        $totalPage = 1;
        if (count($transaction) > 25) {
            $totaldataAfterFirstPage = count($transaction) - 25;
            $totalPage = ceil($totaldataAfterFirstPage / 30) + 1;
        }
        return $totalPage;
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_950');

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
