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


    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;

        $company = (string)$request->get('companyInput');
        $passport = (string)$request->get('passportNumberInput');
        $holding_passport = (string)$request->get('holdingPassportInput');
        $collection_date = (string)$request->get('collectionDateInput');

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

        // (1) HTML -> PDF (keeps your exact template)
        if (@file_put_contents($htmlPath, $html) === false) {
            die('Cannot write HTML file: ' . $htmlPath);
        }

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

        // ------------------------------------------------------------------
        // (A) DYNAMIC ROW COUNT - infer from HTML field names qty_1..qty_N
        // ------------------------------------------------------------------
        $rowCount = 0;

        // match: name="qty_12" or name='qty_12'
        if (preg_match_all('/name\s*=\s*["\']qty_(\d+)["\']/', (string)$html, $m)) {
            $nums = array_map('intval', $m[1]);
            $rowCount = $nums ? max($nums) : 0;
        }

        // Safety: never create insane amounts of fields
        $MAX_ROWS = 30; // adjust if you want more
        $rowCount = max(0, min($rowCount, $MAX_ROWS));

        // (2) Import the rendered PDF and overlay form fields
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pageCount = $pdf->setSourceFile($basePdfPath);
        $tplId = $pdf->importPage(1);
        $size  = $pdf->getTemplateSize($tplId);

        // Create page EXACTLY like template size (prevents scaling mismatch)
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);

        function mark($pdf, $x, $y, $label)
        {
            $pdf->SetFont('helvetica', '', 7);
            $pdf->Line($x - 2, $y, $x + 2, $y);     // small crosshair
            $pdf->Line($x, $y - 2, $x, $y + 2);
            $pdf->Text($x + 2.5, $y - 1.5, $label . " ($x,$y)");
        }


        // DEBUG GRID MODE (call with &debug=1)
        $debug = (string)$request->get('debug') === '1';
        if ($debug) {
            mark($pdf, 60.0, 222.0, 'company');

            mark($pdf, 60.0, 229.0, 'passport');

            mark($pdf, 100.0, 236.0, 'holding_passport');

            mark($pdf, 60.0, 215.0, 'collection_date');
        }

        // ---- Field appearance ----
        $fieldStyle = [
            'border'    => 0,
            'font'      => 'helvetica',
            'fontsize'  => 7,
            'textcolor' => [0, 0, 0],
        ];

        // ---- COORDINATES ----
        // insetX/insetY = inner padding INSIDE each PDF form field (mm). Bigger => field becomes narrower/shorter.
        // fieldH        = height of each field (mm). Bigger => taller input box.
        $insetX = 1.1;   // ↓ increase to DECREASE width (try 1.2 or 1.5)
        $insetY = 0.88;   // vertical inner padding
        $fieldH = 5.0;          // normal fields
        $descH  = 9.6;         // taller textarea-like field (adjust)

        // ---- ROW POSITION ----
        $startY  = 97.0;
        // $rowStep = 7.0;
        $rowStep = 11.7;  // must be >= descH + some padding

        // ---- TABLE GEOMETRY ----
        // Left edge of table (matches your current alignment)
        $xTable = 6.0;

        // Total usable table width in PDF (keep as-is or tweak slightly)
        $wTable = 150.5;

        // ---- COLUMN RATIOS (SUM = 100%) ----
        $ratioQty    = 0.05;
        $ratioDesc   = 0.65;
        $ratioSerial = 0.20;
        $ratioFine   = 0.10;

        // ---- COMPUTED WIDTHS ----
        $wQty    = $wTable * $ratioQty;      // 7.30 mm
        $wDesc   = $wTable * $ratioDesc;     // 94.90 mm
        $wSerial = $wTable * $ratioSerial;   // 29.20 mm
        $wFine   = $wTable * $ratioFine;     // 14.60 mm

        // ---- COLUMN START POSITIONS ----
        $xQty    = $xTable;
        $xDesc   = $xQty + $wQty;
        $xSerial = $xDesc + $wDesc;
        $xFine   = $xSerial + $wSerial;


        // ------------------------------------------------------------------
        // (B) Create fields dynamically (names match your Smarty template)
        // ------------------------------------------------------------------
        for ($i = 1; $i <= $rowCount; $i++) {
            $y = $startY + ($i - 1) * $rowStep;

            $pdf->SetXY($xQty + $insetX, $y + $insetY);
            $pdf->TextField("qty_$i", $wQty - 2 * $insetX, $fieldH, $fieldStyle);

            $pdf->SetXY($xDesc + $insetX, $y + $insetY);
            // $pdf->TextField("desc_$i", $wDesc - 2 * $insetX, $descH, $fieldStyle);
            $pdf->TextField(
                "desc_$i",
                $wDesc - 2 * $insetX,
                $descH,
                $fieldStyle + [
                    'multiline' => true,
                    'linebreak' => true
                ]
            );

            $pdf->SetXY($xSerial + $insetX, $y + $insetY);
            $pdf->TextField("serial_$i", $wSerial - 2 * $insetX, $fieldH, $fieldStyle);

            // IMPORTANT: your tpl uses fine_oz_#
            $pdf->SetXY($xFine + $insetX, $y + $insetY);
            $pdf->TextField("fine_oz_$i", $wFine - 2 * $insetX, $fieldH, $fieldStyle);
        }

        // Collection date (adjust with debug grid)
        // $pdf->SetXY(112.0, 254.0);  // adjust
        // $pdf->TextField('collection_date', 70, 6, $fieldStyle);
        $yTotals = 215;   // adjust here

        $pdf->SetXY(8.0, $yTotals);
        $pdf->TextField('total_value', 35, 5.5, $fieldStyle);

        $pdf->SetXY(118.0, $yTotals);
        $pdf->TextField('total_oz', 35, 5.5, $fieldStyle);

        // ---- EXTRA INPUTS (VALUES FROM REQUEST) ----

        // Company
        $pdf->SetXY(60.0, 222.0);  // adjust Y once
        $pdf->TextField(
            'company_input',
            40,
            6,
            $fieldStyle,
            ['v' => $company]
        );

        // Passport number
        $pdf->SetXY(60.0, 229.0);
        $pdf->TextField(
            'passport_number',
            40,
            6,
            $fieldStyle,
            ['v' => $passport]
        );

        // Holding passport number
        $pdf->SetXY(100.0, 236.0);
        $pdf->TextField(
            'holding_passport_number',
            40,
            6,
            $fieldStyle,
            ['v' => $holding_passport]
        );

        // Collection date
        $pdf->SetXY(60.0, 215.0);
        $pdf->TextField(
            'collection_date',
            40,
            6,
            $fieldStyle,
            ['v' => $collection_date]
        );


        // Save final
        $pdf->Output($finalPdfPath, 'F');
        @unlink($basePdfPath);

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
}
