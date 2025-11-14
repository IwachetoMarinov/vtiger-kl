<?php

class Contacts_DocumentPrintPreview_View extends Vtiger_Index_View
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

    public function process(Vtiger_Request $request)
    {
        // include_once 'modules/Settings/OROSoft/api.php';
        $docNo = $request->get('docNo');
        $docType = substr($docNo, 0, 3);
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();
        $comId = $recordModel->get('related_entity');
        // $oroSOftDoc = ($docType == 'STI') ? getOROSoftSTIDoc($docNo, $comId) : getOROSoftDoc($docNo, $comId);
        // HARDCODED TEST DATA (simulate OROSoft return)
        $oroSOftDoc = (object) [
            'docNo' => $docNo,
            'postingDate' => '2022-10-10',
            'voucherType' => 'Sales Invoice',
            'GST' => true,
            'currency' => 'USD',
            'grandTotal' => 25000.55,
            'barItems' => [
                (object)[
                    'quantity' => 1,
                    'serials' => ['ABC123-XYZ789'],
                    'itemCode' => 'GOLD999',
                    'description' => '999.9 Gold Bar',
                    'weight' => 1000,
                    'unitPrice' => 60.00,
                    'amount' => 60000.00,
                    'barNumber' => 'B0001',
                    'purity' => '999.9',
                    'voucherType' => 'SAL',
                ],
                (object)[
                    'quantity' => 2,
                    'serials' => ['SER0001', 'SER0002'],
                    'itemCode' => 'SLV995',
                    'description' => 'Silver Bar',
                    'weight' => 500,
                    'unitPrice' => 20.00,
                    'amount' => 20000.00,
                    'barNumber' => 'B0002',
                    'purity' => '995',
                    'voucherType' => 'SAL',
                ]
            ]
        ];

        //	print_r($this->processDoc($oroSOftDoc));
        $allBankAccounts = BankAccount_Record_Model::getInstancesByCompanyID($comId);
        $bankAccountId = $request->get('bank');
        if (empty($bankAccountId)) {
            // SHOULD HAVE DEFAULT BANK ACCOUNT SETUP IN COMPANY SETTINGS
            // $bankAccountId = $allBankAccounts[0]->getId();

            // HARDCODED TEST DATA
            $bankAccountId = 1;
        }
        //if($docType == 'STI' && !$oroSOftDoc['GST']){
        //	$docType = 'OLD_STI';
        //}
        $intent = false;
        if (!empty($request->get('fromIntent'))) {
            $intent = Vtiger_Record_Model::getInstanceById($request->get('fromIntent'), 'GPMIntent');
        }
        // SHOULD HANDLE SWD/PWD AS SI/PI
        // $selectedBank = BankAccount_Record_Model::getInstanceById($bankAccountId);
        // HARDCODED TEST DATA
        $selectedBank = (object) [
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
        $viewer->assign('OROSOFT_DOCUMENT', $this->processDoc($oroSOftDoc));
        $viewer->assign('HIDE_BP_INFO', $request->get('hideCustomerInfo'));
        $viewer->assign('INTENT', $intent);
        $viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($comId));
        $viewer->assign('PAGES', $this->makeDataPage($oroSOftDoc->barItems, $docType));
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
        //print_r(array_values($newData));exit();
        return $datas;
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_950');
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
