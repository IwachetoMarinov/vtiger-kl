<?php

// ini_set('display_errors', 1); error_reporting(E_ALL);

include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/HoldingsDB.php';

class Contacts_DocumentPrintPreview_View extends Vtiger_Index_View
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
        $docNo = $request->get('docNo');
        $tableName = $request->get('tableName');
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();
        $comId = $recordModel->get('related_entity');
        $companyId = $recordModel->get('company_id');

        $companyRecord = null;

        if (!empty($companyId))
            $companyRecord = Vtiger_Record_Model::getInstanceById($companyId, 'GPMCompany');

        // $accountId = $recordModel->get('account_id');

        $activity = new dbo_db\ActivitySummary();
        $activity_data = $activity->getDocumentPrintPreviewData($docNo, $tableName);

        // echo '<pre>';
        // echo 'Activity Data: ';
        // var_dump($activity_data);
        // echo '</pre>';

        $docType = $activity_data['voucherType'] ?? "";
        // $exchange_rates = MASForex_Record_Model::getExchangeRate($activity_data['documentDate'] ?? '', 'usd_sgd');
        $erpDoc = (object) $activity_data;

        $allBankAccounts = BankAccount_Record_Model::getInstancesByCompanyID($comId);
        $bankAccountId = $request->get('bank');
        if (empty($bankAccountId)) {
            // SHOULD HAVE DEFAULT BANK ACCOUNT SETUP IN COMPANY SETTINGS
            // $bankAccountId = $allBankAccounts[0]->getId();

            // HARDCODED TEST DATA
            $bankAccountId = 1;
        }

        $intent = false;
        if (!empty($request->get('fromIntent'))) {
            $intent = Vtiger_Record_Model::getInstanceById($request->get('fromIntent'), 'GPMIntent');
        }
        // SHOULD get real data for the Bank
        // $selectedBank = BankAccount_Record_Model::getInstanceById($bankAccountId);
        // HARDCODED TEST DATA
        $selectedBank = (object) [
            'id' => "123",
            'accountNumber' => '123-456-789',
            'accountName' => 'GPM Main Account',
            'bankName' => 'Global Bank',
            'branchCode' => '001',
            'swiftCode' => 'GBLBB22',
            'bankAddress' => '123 Global St, Metropolis, Country'
        ];

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('ALL_BANK_ACCOUNTS', $allBankAccounts);
        $viewer->assign('SELECTED_BANK', $selectedBank);
        $viewer->assign('ERP_DOCUMENT', $this->processDoc($erpDoc));
        $viewer->assign('HIDE_BP_INFO', $request->get('hideCustomerInfo'));
        $viewer->assign('INTENT', $intent);
        $viewer->assign('COMPANY', $companyRecord);
        $viewer->assign('PAGES', $this->makeDataPage($erpDoc->barItems, $docType));
        if ($request->get('PDFDownload')) {
            $html = $viewer->view("$docType.tpl", $moduleName, true);
            $this->downloadPDF($html, $request);
        } else {
            $viewer->view("$docType.tpl", $moduleName);
        }
    }

    function makeDataPage($transaction, $docType)
    {
        $totalPage = 1;
        $recordCount = ($docType == 'SAL') ? 6 : 14;
        if (count($transaction) > $recordCount) {
            $totaldataAfterFirstPage = count($transaction) - $recordCount;
            $totalPage = ceil($totaldataAfterFirstPage / $recordCount) + 1;
        }
        return $totalPage;
    }

    function processDoc($datas)
    {
        foreach ($datas->barItems as $key => $item) {
            if ($item->quantity == 1) {
                $serials = explode('-', $item->serials[0]);
                $datas->barItems[$key]->serials[0] = $serials[0];
            }
        }
        return $datas;
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');
        $docType = substr($request->get('docNo'), 0, 3);
        if ($docType == 'SWD' || $docType == 'SAL') {
            $docType = 'SI';
        } elseif ($docType == 'PWD' || $docType == 'PUR') {
            $docType = 'PI';
        }
        $fileName = $clientID . '-' . str_replace('/', '-', $request->get('docNo')) . '-' . $docType;
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
