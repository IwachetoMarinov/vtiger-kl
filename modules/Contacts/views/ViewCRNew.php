<?php

include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/HoldingsDB.php';

// ini_set('display_errors', 1);error_reporting(E_ALL);

use TCPDF;

class Contacts_ViewCRNew_View extends Vtiger_Index_View
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

        $companyRecord = Contacts_DefaultCompany_View::process();

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
        $viewer->assign('ID_OPTION', $request->get('idOption') ?? null);
        $viewer->assign('COMPANY_OPTION', $request->get('companyOption') ?? null);
        $viewer->assign('DOCNO', $request->get('docNo'));
        $viewer->assign('PDFDownload', $request->get('PDFDownload'));
        $viewer->assign('hideCustomerInfo', $request->get('hideCustomerInfo'));

        if ($request->get('PDFDownload')) {
            $html = $viewer->view("NCR.tpl", $moduleName, true);
            $this->downloadPDF($html, $request);
        } else {
            $viewer->view("NCR.tpl", $moduleName);
        }
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;

        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');

        $year = date('Y');

        // Get last part of docNo after last '/'
        $docNoParts = explode('/', (string)$request->get('docNo'));
        $docNoLastPart = end($docNoParts);

        $template_name = "CR";
        $fileName = $clientID . '-' . $template_name . '-' . $year . '-' . $docNoLastPart . '-' . $template_name;

        // IMPORTANT: write temp files into a writable folder (vTiger storage)
        $tmpDir = $root_directory . 'storage/'; // or 'cache/'
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $htmlPath = $tmpDir . $fileName . '.html';
        $pdfPath  = $tmpDir . $fileName . '.pdf';

        // Write HTML
        if (file_put_contents($htmlPath, $html) === false) {
            die('Cannot write HTML file: ' . $htmlPath);
        }

        // Generate PDF
        $cmd = "wkhtmltopdf --enable-local-file-access -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking "
            . escapeshellarg($htmlPath) . " " . escapeshellarg($pdfPath) . " 2>&1";

        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        // Remove temp HTML
        @unlink($htmlPath);

        if ($exitCode !== 0 || !file_exists($pdfPath)) {
            die("wkhtmltopdf failed (exit=$exitCode):\n" . implode("\n", $output));
        }

        // Download
        header("Content-Type: application/pdf");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=\"$fileName.pdf\"");
        header("Content-Description: Global Precious Metals CRM Data");

        if (ob_get_length()) {
            ob_clean();
        }
        flush();

        readfile($pdfPath);

        // Cleanup
        @unlink($pdfPath);
        exit;
    }

    // function downloadPDF($html, Vtiger_Request $request)
    // {
    //     global $root_directory;
    //     $recordModel = $this->record->getRecord();
    //     $clientID = $recordModel->get('cf_898');

    //     $year = date('Y');
    //     // Get last part of docNo after last '/'
    //     $docNoParts = explode('/', $request->get('docNo'));
    //     $docNoLastPart = end($docNoParts);
    //     $template_name = "CR";

    //     $fileName = $clientID . '-' . $template_name . '-' . $year . '-' . $docNoLastPart . '-' . $template_name;

    //     $handle = fopen($root_directory . $fileName . '.html', 'a') or die('Cannot open file:  ');
    //     fwrite($handle, $html);
    //     fclose($handle);

    //     exec("wkhtmltopdf --enable-local-file-access  -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking " . $root_directory . "$fileName.html " . $root_directory . "$fileName.pdf");
    //     unlink($root_directory . $fileName . '.html');

    //     header("Content-type: application/pdf");
    //     header("Cache-Control: private");
    //     header("Content-Disposition: attachment; filename=$fileName.pdf");
    //     header("Content-Description: Global Precious Metals CRM Data");
    //     ob_clean();
    //     flush();
    //     readfile($root_directory . "$fileName.pdf");
    //     unlink($root_directory . "$fileName.pdf");
    //     exit;
    // }
}
