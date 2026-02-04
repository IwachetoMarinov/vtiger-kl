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
        $docNoParts = explode('/', (string)$request->get('docNo'));
        $docNoLastPart = end($docNoParts) ?: 'NO-DOCNO';
        $template_name = "CR";

        $fileName = $clientID . '-' . $template_name . '-' . $year . '-' . $docNoLastPart . '-' . $template_name;

        // ---- Writable temp dir ----
        $tmpDir = rtrim($root_directory, '/') . '/storage/';
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        if (!is_writable($tmpDir)) {
            // fallback to system temp
            $tmpDir = rtrim(sys_get_temp_dir(), '/') . '/vtiger_pdf/';
            if (!is_dir($tmpDir)) {
                @mkdir($tmpDir, 0775, true);
            }
        }
        if (!is_dir($tmpDir) || !is_writable($tmpDir)) {
            die('Temp directory not writable: ' . $tmpDir);
        }

        $htmlPath     = $tmpDir . $fileName . '.html';
        $basePdfPath  = $tmpDir . $fileName . '_base.pdf';
        $finalPdfPath = $tmpDir . $fileName . '.pdf';

        // ---- (1) Render your HTML template to PDF (pretty layout) ----
        if (@file_put_contents($htmlPath, $html) === false) {
            die('Cannot write HTML file: ' . $htmlPath);
        }

        // LOCK page size/zoom so coordinates are stable
        $cmd = "wkhtmltopdf --enable-local-file-access "
            . "--page-size A4 --dpi 96 --zoom 1 "
            . "--margin-top 0 --margin-right 0 --margin-bottom 0 --margin-left 0 "
            . "--disable-smart-shrinking "
            . escapeshellarg($htmlPath) . " " . escapeshellarg($basePdfPath) . " 2>&1";

        $out = [];
        $code = 0;
        exec($cmd, $out, $code);

        @unlink($htmlPath);

        if ($code !== 0 || !file_exists($basePdfPath)) {
            die("wkhtmltopdf failed (exit=$code):\n" . implode("\n", $out));
        }

        // ---- (2) Overlay REAL editable fields (AcroForm) on top of the PDF ----
        // FPDI + TCPDF
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pageCount = $pdf->setSourceFile($basePdfPath);
        if ($pageCount < 1) {
            @unlink($basePdfPath);
            die('Base PDF has no pages: ' . $basePdfPath);
        }

        $tplId = $pdf->importPage(1);
        $size  = $pdf->getTemplateSize($tplId);

        // Create page with EXACT imported size (prevents scaling mismatch)
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);

        // ---- Field tuning (adjust these numbers once) ----
        // Insets make fields look "inside" cells
        $insetX = 1.0;
        $insetY = 0.8;
        $fieldH = 5.2;

        // City/Location field (X/Y tuned for your template)
        $pdf->SetXY(60, 92);
        $pdf->TextField('city_location', 120, 6, ['border' => 0]);

        // Reference field (optional)
        $pdf->SetXY(70, 113);
        $pdf->TextField('reference', 120, 6, ['border' => 0]);

        // Table fields (10 rows) - tuned for your screenshot
        $startY  = 130;  // first row Y
        $rowStep = 7.5;  // distance between rows

        $xQty    = 22;
        $wQty    = 18;
        $xDesc   = 42;
        $wDesc   = 95;
        $xSerial = 138;
        $wSerial = 42;
        $xFine   = 181;
        $wFine   = 22;

        for ($i = 1; $i <= 10; $i++) {
            $y = $startY + ($i - 1) * $rowStep;

            $pdf->SetXY($xQty + $insetX, $y + $insetY);
            $pdf->TextField("qty_$i", $wQty - 2 * $insetX, $fieldH, ['border' => 0]);

            $pdf->SetXY($xDesc + $insetX, $y + $insetY);
            $pdf->TextField("desc_$i", $wDesc - 2 * $insetX, $fieldH, ['border' => 0]);

            $pdf->SetXY($xSerial + $insetX, $y + $insetY);
            $pdf->TextField("serial_$i", $wSerial - 2 * $insetX, $fieldH, ['border' => 0]);

            $pdf->SetXY($xFine + $insetX, $y + $insetY);
            $pdf->TextField("fine_$i", $wFine - 2 * $insetX, $fieldH, ['border' => 0]);
        }

        // Collection date
        $pdf->SetXY(110, 250);
        $pdf->TextField('collection_date', 70, 6, ['border' => 0]);

        // Save final PDF
        $pdf->Output($finalPdfPath, 'F');

        @unlink($basePdfPath);

        if (!file_exists($finalPdfPath)) {
            die('Final PDF was not created: ' . $finalPdfPath);
        }

        // ---- Download ----
        header("Content-Type: application/pdf");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=\"$fileName.pdf\"");

        if (ob_get_length()) {
            ob_clean();
        }
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
