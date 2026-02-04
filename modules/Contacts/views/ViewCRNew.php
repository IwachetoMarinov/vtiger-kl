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
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');

        $year = date('Y');
        $docNoParts = explode('/', (string)$request->get('docNo'));
        $docNoLastPart = end($docNoParts);
        if (!$docNoLastPart) {
            $docNoLastPart = 'NO-DOCNO';
        }

        $template_name = "CR";
        $fileName = $clientID . '-' . $template_name . '-' . $year . '-' . $docNoLastPart . '-' . $template_name;

        // Create PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('vTiger');
        $pdf->SetAuthor('Global Precious Metals CRM');
        $pdf->SetTitle('Collection Request');

        // margins similar to your wkhtmltopdf
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        // Title
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, 'COLLECTION REQUEST', 0, 1, 'R');
        $pdf->Ln(3);

        // Header table (REFERENCE / CUSTOMER / ORDER)
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(200, 180, 120); // gold-ish like your template
        $w1 = 60;
        $w2 = 60;
        $w3 = 60;
        $h = 7;

        $pdf->Cell($w1, $h, 'REFERENCE', 1, 0, 'C', true);
        $pdf->Cell($w2, $h, 'CUSTOMER',  1, 0, 'C', true);
        $pdf->Cell($w3, $h, 'ORDER',     1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell($w1, 8, '',     1, 0, 'C', false);
        $pdf->Cell($w2, 8, $clientID ?: '', 1, 0, 'C', false);
        $pdf->Cell($w3, 8, 'COLLECTION',    1, 1, 'C', false);
        $pdf->Ln(4);

        // Intro + City/Location field
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, 6, 'I/We hereby wish to collect the Stored Metal detailed below at the following location:', 0, 'L', false, 1);
        $pdf->Ln(1);

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'CITY/LOCATION', 0, 0);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->TextField('city_location', 120, 6, ['border' => 1, 'value' => '']);
        $pdf->Ln(10);

        // Items table header
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(200, 180, 120);
        $colQty = 18;
        $colDesc = 105;
        $colSerial = 40;
        $colFine = 27;
        $rowH = 8;

        $pdf->Cell($colQty,   $rowH, 'QTY',           1, 0, 'C', true);
        $pdf->Cell($colDesc,  $rowH, 'DESCRIPTION',   1, 0, 'C', true);
        $pdf->Cell($colSerial, $rowH, 'SERIAL NUMBERS', 1, 0, 'C', true);
        $pdf->Cell($colFine,  $rowH, 'FINE OZ',       1, 1, 'C', true);

        // 10 rows of REAL editable fields (AcroForm)
        $pdf->SetFont('helvetica', '', 9);

        for ($i = 1; $i <= 10; $i++) {
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            // draw row cells
            $pdf->Cell($colQty,   $rowH, '', 1, 0);
            $pdf->Cell($colDesc,  $rowH, '', 1, 0);
            $pdf->Cell($colSerial, $rowH, '', 1, 0);
            $pdf->Cell($colFine,  $rowH, '', 1, 1);

            // add form fields on top (border=0 because the cell already has border)
            $insetX = 1.2;
            $insetY = 1.2;
            $pdf->SetXY($x + $insetX, $y + $insetY);
            $pdf->TextField("qty_$i", $colQty - 2 * $insetX, $rowH - 2 * $insetY, ['border' => 0]);

            $pdf->SetXY($x + $colQty + $insetX, $y + $insetY);
            $pdf->TextField("desc_$i", $colDesc - 2 * $insetX, $rowH - 2 * $insetY, ['border' => 0]);

            $pdf->SetXY($x + $colQty + $colDesc + $insetX, $y + $insetY);
            $pdf->TextField("serial_$i", $colSerial - 2 * $insetX, $rowH - 2 * $insetY, ['border' => 0]);

            $pdf->SetXY($x + $colQty + $colDesc + $colSerial + $insetX, $y + $insetY);
            $pdf->TextField("fine_$i", $colFine - 2 * $insetX, $rowH - 2 * $insetY, ['border' => 0]);
        }

        $pdf->Ln(5);

        // Collection date field
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(85, 6, 'I/We would like the Collection to take place on:', 0, 0);
        $pdf->TextField('collection_date', 70, 6, ['border' => 1, 'value' => '']);
        $pdf->Ln(10);

        // IMPORTANT: output directly (no saving, no readfile)
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '.pdf"');
        header('Cache-Control: private');

        $pdf->Output($fileName . '.pdf', 'D');
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
