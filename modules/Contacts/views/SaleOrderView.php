<?php

// ini_set('display_errors', 1); error_reporting(E_ALL);    

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
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();
        $companyId = $recordModel->get('company_id');
        // Client type
        $client_type = $recordModel->get('cf_927');

        $companyRecord = null;

        if (!empty($companyId))
            $companyRecord = Vtiger_Record_Model::getInstanceById($companyId, 'GPMCompany');

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('PAGES', 1);
        $viewer->assign('HIDE_BP_INFO', false);
        $viewer->assign('COMPANY', $companyRecord);
        $viewer->assign('CLIENT_TYPE', $client_type);

        // REQUEST VALUES PASSED BY CONTROLLER
        $viewer->assign('DOCNO', $request->get('docNo'));
        $viewer->assign('PDFDownload', $request->get('PDFDownload'));
        $viewer->assign('CLIENT_NAME', $request->get('clientName') ?? '');
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
        $year  = date('Y');

        $fileName = $clientID . '-' . $year . "-SO";
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
