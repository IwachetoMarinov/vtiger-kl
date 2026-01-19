<?php

class Contacts_PurchaseOrderView_View extends Vtiger_Index_View
{

    protected $record = null;

    public function preProcess(Vtiger_Request $request, $display = false)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        if (!$this->record) $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();
        $companyId = $recordModel->get('company_id');
        // Client type
        $client_type = $recordModel->get('cf_927');

        $pricingOption = $request->get('pricing_option');

        $allBankAccounts = [];

        $companyRecord = null;

        if (!empty($companyId)) {
            // ✅ Company record
            $companyRecord = Vtiger_Record_Model::getInstanceById($companyId, 'GPMCompany');
            // ✅ Bank accounts
            $allBankAccounts = BankAccount_Record_Model::getAllInstances();
            $bankAccountId   = $request->get('bank');
        }

        $bankAccountId = $request->get('bank');
        if (empty($bankAccountId) && !empty($allBankAccounts)) {
            $firstAccount  = reset($allBankAccounts);
            $bankAccountId = $firstAccount->getId();
        }

        // ✅ Handle no bank accounts gracefully
        if (empty($bankAccountId)) $bankAccountId = null;

        $selectedBank = null;
        if (!empty($bankAccountId)) $selectedBank = BankAccount_Record_Model::getInstanceById($bankAccountId);

        if (empty($selectedBank)) {
            // fallback dummy object to prevent template fatal
            $selectedBank = new Vtiger_Record_Model();
            $selectedBank->set('beneficiary_name', '');
            $selectedBank->set('account_no', '');
            $selectedBank->set('account_currency', '');
            $selectedBank->set('iban_no', '');
            $selectedBank->set('bank_name', '');
            $selectedBank->set('bank_address', '');
            $selectedBank->set('swift_code', '');
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('PRICING_OPTION', $pricingOption);
        $viewer->assign('CLIENT_TYPE', $client_type);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('PAGES', 1);
        $viewer->assign('ALL_BANK_ACCOUNTS', $allBankAccounts);
        $viewer->assign('SELECTED_BANK', $selectedBank ?? null);
        $viewer->assign('HIDE_BP_INFO', false);
        $viewer->assign('COMPANY', $companyRecord);
        $viewer->assign('DOCNO', $request->get('docNo'));
        $viewer->assign('PDFDownload', $request->get('PDFDownload'));
        $viewer->assign('hideCustomerInfo', $request->get('hideCustomerInfo'));
        $viewer->assign('COUNTRY_OPTION', $request->get('countryOption') ?? null);
        $viewer->assign('ADDRESS_OPTION', $request->get('addressOption') ?? null);

        if ($request->get('PDFDownload')) {
            $html = $viewer->view("PO.tpl", $moduleName, true);
            $this->downloadPDF($html, $request);
        } else {
            $viewer->view("PO.tpl", $moduleName);
        }
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');
        $year  = date('Y');

        $fileName = $clientID . '-' . $year . "-PO";
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

    protected function getAssets()
    {
        $moduleName = 'Assets';
        $currentUser = Users_Record_Model::getCurrentUserModel();

        // Load module and fields
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $fields = $moduleModel->getFields();

        // Build list of all fieldnames dynamically
        $fieldNames = [];
        foreach ($fields as $fieldModel) {
            $fieldName = $fieldModel->getName();
            $fieldNames[] = $fieldName;
        }

        // QueryGenerator to fetch ALL these fields
        $queryGenerator = new QueryGenerator($moduleName, $currentUser);
        $queryGenerator->setFields($fieldNames);

        $query = $queryGenerator->getQuery();

        $db = PearDatabase::getInstance();
        $result = $db->pquery($query, []);

        $assets = [];
        while ($row = $db->fetchByAssoc($result)) {
            $assets[] = $row;
        }

        return $assets;
    }

    protected function processAssetsData($assets)
    {
        $data = [];

        foreach ($assets as $asset) {
            $metal_type = $asset['cf_873'];

            if (isset($data[$metal_type])) {
                // Append to existing metal type
                $data[$metal_type][] = $asset;
            } else {
                $data[$metal_type] = [$asset];
            }
        }

        return $data;
    }
}
