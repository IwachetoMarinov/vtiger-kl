<?php

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/HoldingsDB.php';

class Contacts_TCPrintPreview_View extends Vtiger_Index_View
{

    protected $record = null;

    public function preProcess(Vtiger_Request $request, $display = false)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        // Getting model to reuse it in parent
        if (!$this->record)  $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
    }

    public function process(Vtiger_Request $request)
    {
        include_once 'modules/Contacts/models/MetalsAPI.php';
        $docNo = $request->get('docNo');
        $tableName = $request->get('tableName');
        $docType = substr($docNo, 0, 3);
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();
        $comId = $recordModel->get('related_entity');

        $accountId = $recordModel->get('account_id');

        $organizationName = '';

        if (!empty($accountId)) {
            $accountRecord = Vtiger_Record_Model::getInstanceById($accountId, 'Accounts');
            $organizationName = $accountRecord->get('accountname');
        }

        $activity = new dbo_db\ActivitySummary();
        $activity_data = $activity->getDocumentPrintPreviewData($docNo, $tableName);
        // $activity_data = $activity->getTCPrintPreviewData($docNo, $tableName);

        // echo '<pre>';
        // var_dump('ACTIVITY DATA: ');
        // var_dump($activity_data);
        // echo '</pre>';

        // ------------------------------------------------------
        // GET DATA FROM new ERP HOLDINGSDB CLASS
        // ------------------------------------------------------
        // $erpDoc = (object) [
        //     'docNo' => $docNo,
        //     'docType' => $docType,
        //     'documentDate' => $activity_data['document_date'] ?? '',
        //     'postingDate' => $activity_data['posting_date'] ?? '',
        //     'voucherType' => $activity_data['voucher_type'] ?? 'Sales Invoice',
        //     'companyName' => $organizationName,
        //     'currency' => $activity_data['currency'] ?? 'USD',
        //     'grandTotal' => $activity_data['grand_total'] ?? 0.00,
        //     'totalusdVal' => $activity_data['totalusd_val'] ?? 0.00,

        //     // REQUIRED BY TEMPLATE (missing in your version)
        //     // HARDCODED TEST DATA
        //     'balanceAmount' => 0,
        //     'value' => 0,

        //     // THIS IS CRITICAL: template iterates beyond length
        //     // so we add 30 items (enough for page count)
        //     // HARDCODED TEST DATA
        //     'barItems' => []
        // ];

        // // ------------------------------------------
        // // Populate barItems with 30 fully valid items
        // // ------------------------------------------
        // for ($i = 1; $i <= 10; $i++) {
        //     $erpDoc->barItems[] = (object)[
        //         'quantity' => ($i % 3) + 1,
        //         'serials' => ["SERIAL-$i-A", "SERIAL-$i-B"],
        //         'itemCode' => 'GOLD999',
        //         'longDesc' => "Gold Bar $i (999.9 Fine)",
        //         'weight' => 1000,
        //         'pureOz' => 32.151,
        //         'price' => 2020.25,
        //         'otherCharge' => 500.00,
        //         'unitPrice' => 62000.00,
        //         'amount' => 62000.00,
        //         'usdVal' => 62000.00,
        //         'barNumber' => "B$i-GPM",
        //         'purity' => '999.9',
        //         'voucherType' => 'SAL',
        //         'value' => 62000.00,
        //     ];
        // }

        $erpDoc = (object) $activity_data;

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('ERP_DOCUMENT', $erpDoc);
        $viewer->assign('OROSOFT_DOCTYPE', $docType);
        $viewer->assign('HIDE_BP_INFO', $request->get('hideCustomerInfo'));
        $viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($comId));
        $viewer->assign('PAGES', $this->makeDataPage($erpDoc->barItems, $docType));
        if ($request->get('PDFDownload')) {
            $html = $viewer->view("TC.tpl", $moduleName, true);
            $this->downloadPDF($html, $request);
        } else {
            $viewer->view("TC.tpl", $moduleName);
        }
    }

    function makeDataPage($transaction, $docType)
    {
        $totalPage = 1;
        $recordCount = 26;
        if (count($transaction) > $recordCount) {
            $totaldataAfterFirstPage = count($transaction) - $recordCount;
            $totalPage = ceil($totaldataAfterFirstPage / $recordCount) + 1;
        }
        return $totalPage;
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');

        $fileName = $clientID . '-' . str_replace('/', '-', $request->get('docNo')) . "-TC";
        $handle = fopen($root_directory . $fileName . '.html', 'a') or die('Cannot open file:  ');
        fwrite($handle, $html);
        fclose($handle);

        exec("wkhtmltopdf --enable-local-file-access  -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking " . $root_directory . "$fileName.html " . $root_directory . "$fileName.pdf");
        unlink($root_directory . $fileName . '.html');

        header("Content-type: application/pdf");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=$fileName.pdf");
        header("Content-Description: Global Precious Metals CRM Data");
        ob_clean();
        flush();
        readfile($root_directory . "$fileName.pdf");
        unlink($root_directory . "$fileName.pdf");
        exit;
    }
}
