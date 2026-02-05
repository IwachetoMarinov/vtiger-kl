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

        $tmpDir = rtrim($root_directory, '/') . '/storage/';
        if (!is_dir($tmpDir)) @mkdir($tmpDir, 0775, true);
        if (!is_writable($tmpDir)) {
            $tmpDir = rtrim(sys_get_temp_dir(), '/') . '/vtiger_pdf/';
            if (!is_dir($tmpDir)) @mkdir($tmpDir, 0775, true);
        }
        if (!is_writable($tmpDir)) die('Temp dir not writable');

        $htmlPath     = $tmpDir . $fileName . '.html';
        $basePdfPath  = $tmpDir . $fileName . '_base.pdf';
        $finalPdfPath = $tmpDir . $fileName . '.pdf';

        file_put_contents($htmlPath, $html);

        $cmd = "wkhtmltopdf --enable-local-file-access "
            . "--page-size A4 --dpi 96 --zoom 1 "
            . "--margin-top 0 --margin-right 0 --margin-bottom 0 --margin-left 0 "
            . "--disable-smart-shrinking "
            . escapeshellarg($htmlPath) . " "
            . escapeshellarg($basePdfPath) . " 2>&1";

        exec($cmd, $out, $code);
        @unlink($htmlPath);

        if ($code !== 0 || !file_exists($basePdfPath)) {
            die("wkhtmltopdf failed");
        }

        $rowCount = (int)$request->get('rows_count');
        $rowCount = max(0, min($rowCount, 20));

        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        $tplId = $pdf->importPage(1, '/MediaBox');
        $size  = $pdf->getTemplateSize($tplId);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplId);

        $fieldStyle = [
            'border'    => 0,
            'font'      => 'helvetica',
            'fontsize'  => 9,
            'textcolor' => [0, 0, 0],
        ];

        $startY  = 112.0;
        $rowStep = 7.4;
        $fieldH  = 5.0;

        $xQty    = 19.0;
        $wQty    = 18.0;
        $xDesc   = 37.0;
        $wDesc   = 94.0;
        $xSerial = 131.0;
        $wSerial = 38.0;
        $xFine   = 169.0;
        $wFine   = 19.0;

        for ($i = 1; $i <= $rowCount; $i++) {
            $y = $startY + ($i - 1) * $rowStep;

            $pdf->SetXY($xQty, $y);
            $pdf->TextField("qty_$i", $wQty, $fieldH, $fieldStyle);

            $pdf->SetXY($xDesc, $y);
            $pdf->TextField("desc_$i", $wDesc, $fieldH, $fieldStyle);

            $pdf->SetXY($xSerial, $y);
            $pdf->TextField("serial_$i", $wSerial, $fieldH, $fieldStyle);

            $pdf->SetXY($xFine, $y);
            $pdf->TextField("fine_oz_$i", $wFine, $fieldH, $fieldStyle);
        }

        $pdf->SetXY(112.0, 254.0);
        $pdf->TextField('collection_date', 70, 6, $fieldStyle);

        $pdf->Output($finalPdfPath, 'F');
        @unlink($basePdfPath);

        header("Content-Type: application/pdf");
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
