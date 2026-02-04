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

        require_once $root_directory . 'vendor/autoload.php';

        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');

        $year = date('Y');
        $docNoParts = explode('/', (string)$request->get('docNo'));
        $docNoLastPart = end($docNoParts) ?: 'NO-DOCNO';
        $template_name = "CR";

        $fileName = $clientID . '-' . $template_name . '-' . $year . '-' . $docNoLastPart . '-' . $template_name;

        // Writable temp dir
        $tmpDir = rtrim($root_directory, '/') . '/storage/';
        if (!is_dir($tmpDir)) @mkdir($tmpDir, 0775, true);
        if (!is_writable($tmpDir)) die('Temp dir not writable: ' . $tmpDir);

        $htmlPath = $tmpDir . $fileName . '.html';
        $basePdfPath = $tmpDir . $fileName . '_base.pdf';
        $finalPdfPath = $tmpDir . $fileName . '.pdf';

        // 1) HTML -> PDF (keeps your exact template)
        file_put_contents($htmlPath, $html);

        // $cmd = "wkhtmltopdf --enable-local-file-access -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking "
        //     . escapeshellarg($htmlPath) . " " . escapeshellarg($basePdfPath) . " 2>&1";

        $cmd = "wkhtmltopdf --enable-local-file-access "
            . "--page-size A4 --dpi 96 --zoom 1 "
            . "--margin-top 10mm --margin-right 10mm --margin-bottom 10mm --margin-left 10mm "
            . "--disable-smart-shrinking "
            . escapeshellarg($htmlPath) . " " . escapeshellarg($basePdfPath) . " 2>&1";


        $out = [];
        $code = 0;
        exec($cmd, $out, $code);

        @unlink($htmlPath);

        if ($code !== 0 || !file_exists($basePdfPath)) {
            die("wkhtmltopdf failed (exit=$code):\n" . implode("\n", $out));
        }

        // 2) Overlay fillable fields onto the generated PDF
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->AddPage();

        $pageCount = $pdf->setSourceFile($basePdfPath);
        $tplId = $pdf->importPage(1);
        $pdf->useTemplate($tplId, 0, 0, 210, 297);

        // IMPORTANT: these coordinates MUST match your template.
        // I’m giving you working defaults close to your screenshot.
        // You will adjust X/Y once and it's done forever.

        // City/Location field (approx)
        $pdf->SetXY(60, 92);
        $pdf->TextField('city_location', 120, 7, ['border' => 0]);

        // Table fields - 10 rows
        $startY = 118;   // top of first row inputs
        $rowH   = 7.5;   // distance between rows
        $xQty   = 22;
        $wQty   = 18;

        $xDesc  = 42;
        $wDesc  = 95;

        $xSerial = 138;
        $wSerial = 42;

        $xFine  = 181;
        $wFine  = 22;

        for ($i = 1; $i <= 10; $i++) {
            $y = $startY + ($i - 1) * $rowH;

            $pdf->SetXY($xQty, $y);
            $pdf->TextField("qty_$i", $wQty, 6, ['border' => 0]);

            $pdf->SetXY($xDesc, $y);
            $pdf->TextField("desc_$i", $wDesc, 6, ['border' => 0]);

            $pdf->SetXY($xSerial, $y);
            $pdf->TextField("serial_$i", $wSerial, 6, ['border' => 0]);

            $pdf->SetXY($xFine, $y);
            $pdf->TextField("fine_$i", $wFine, 6, ['border' => 0]);
        }

        // Collection date field (approx)
        $pdf->SetXY(110, 205);
        $pdf->TextField('collection_date', 70, 7, ['border' => 0]);

        // Save final
        $pdf->Output($finalPdfPath, 'F');

        @unlink($basePdfPath);

        // Download
        header("Content-Type: application/pdf");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=\"$fileName.pdf\"");
        if (ob_get_length()) ob_clean();
        flush();
        readfile($finalPdfPath);
        @unlink($finalPdfPath);
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
