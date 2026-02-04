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
        $docNoParts = explode('/', $request->get('docNo'));
        $docNoLastPart = end($docNoParts);
        $template_name = "CR";

        $fileName = $clientID . '-' . $template_name . '-' . $year . '-' . $docNoLastPart;

        // ---- TCPDF ----
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('vTiger');
        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(true, 8);
        $pdf->AddPage();

        $pdf->SetFont('helvetica', '', 9);

        // Title
        $pdf->Cell(0, 6, 'COLLECTION REQUEST', 0, 1, 'R');
        $pdf->Ln(4);

        // === TABLE HEADER ===
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(15, 8, 'QTY', 1);
        $pdf->Cell(110, 8, 'DESCRIPTION', 1);
        $pdf->Cell(35, 8, 'SERIAL NUMBERS', 1);
        $pdf->Cell(25, 8, 'FINE OZ', 1);
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 9);

        // === 10 EDITABLE ROWS ===
        for ($i = 1; $i <= 10; $i++) {
            $y = $pdf->GetY();
            $x = $pdf->GetX();

            $pdf->Cell(15, 8, '', 1);
            $pdf->Cell(110, 8, '', 1);
            $pdf->Cell(35, 8, '', 1);
            $pdf->Cell(25, 8, '', 1);
            $pdf->Ln();

            // Inputs inside cells
            $pdf->SetXY($x + 1, $y + 1);
            $pdf->TextField("qty_$i", 13, 6, ['border' => 0]);

            $pdf->SetXY($x + 16, $y + 1);
            $pdf->TextField("desc_$i", 108, 6, ['border' => 0]);

            $pdf->SetXY($x + 126, $y + 1);
            $pdf->TextField("serial_$i", 33, 6, ['border' => 0]);

            $pdf->SetXY($x + 161, $y + 1);
            $pdf->TextField("fine_$i", 23, 6, ['border' => 0]);
        }

        // Output
        $pdfPath = $root_directory . $fileName . '.pdf';
        $pdf->Output($pdfPath, 'F');

        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=$fileName.pdf");
        header("Cache-Control: private");
        ob_clean();
        flush();
        readfile($pdfPath);
        unlink($pdfPath);
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
