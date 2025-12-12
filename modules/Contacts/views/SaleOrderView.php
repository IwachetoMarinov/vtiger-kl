<?php

// ini_set('display_errors', 1); error_reporting(E_ALL);    

include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/HoldingsDB.php';

class Contacts_SaleOrderView_View extends Vtiger_Index_View
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

        $docNo = $request->get('docNo');
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();
        $tableName = $request->get('tableName');
        $companyId = $recordModel->get('company_id');

        // Get all assets 
        // $assets = $this->getAssets();
        // $assets_data = $this->processAssetsData($assets);

        $companyRecord = null;

        if (!empty($companyId))
            $companyRecord = Vtiger_Record_Model::getInstanceById($companyId, 'GPMCompany');

        if ($tableName !== null && $tableName !== '') {
            $activity = new dbo_db\ActivitySummary();
            $erpData = $activity->getDocumentPrintPreviewData($docNo, $tableName);
        } else {
            $erpData = [];
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('PAGES', 1);
        $viewer->assign('HIDE_BP_INFO', false);
        $viewer->assign('COMPANY', $companyRecord);
        $viewer->assign('ERP_DOCUMENT', $erpData);
        // $viewer->assign('ERP_DOCUMENT', $erpData);

        // REQUEST VALUES PASSED BY CONTROLLER
        $viewer->assign('DOCNO', $request->get('docNo'));
        $viewer->assign('PDFDownload', $request->get('PDFDownload'));
        $viewer->assign('hideCustomerInfo', $request->get('hideCustomerInfo'));

        if ($request->get('PDFDownload')) {
            $html = $viewer->view("SO.tpl", $moduleName, true);
            $this->downloadPDF($html, $request);
        } else {
            $viewer->view("SO.tpl", $moduleName);
        }
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');

        $fileName = $clientID . '-' . str_replace('/', '-', $request->get('docNo')) . "-SO";
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

            // Remove CRYPTO from metal type
            // if ($metal_type === 'CRYPTO') continue;

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
