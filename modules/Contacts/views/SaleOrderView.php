<?php

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
        $client_type = $recordModel->get('cf_927');

        $companyRecord = Contacts_DefaultCompany_View::process();

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('PAGES', 1);
        $viewer->assign('HIDE_BP_INFO', false);
        $viewer->assign('COMPANY', $companyRecord);
        $viewer->assign('CLIENT_TYPE', $client_type);
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

    // protected function downloadPDF($html, Vtiger_Request $request)
    // {
    //     global $root_directory;

    //     // --- Build safe paths ---
    //     $recordModel = $this->record->getRecord();
    //     $clientID = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$recordModel->get('cf_898'));
    //     $year = date('Y');

    //     $fileName = $clientID . '-' . $year . '-SO';

    //     // Ensure root_directory ends with /
    //     $dir = rtrim($root_directory, '/\\') . DIRECTORY_SEPARATOR;

    //     $tmpDir = $dir . 'cache' . DIRECTORY_SEPARATOR;
    //     if (!is_dir($tmpDir)) @mkdir($tmpDir, 0775, true);

    //     $htmlPath = $tmpDir . $fileName . '.html';
    //     $pdfPath  = $tmpDir . $fileName . '.pdf';

    //     // --- Write HTML ---
    //     $bytes = @file_put_contents($htmlPath, $html);
    //     if ($bytes === false) {
    //         header("HTTP/1.1 500 Internal Server Error");
    //         die("Cannot write HTML file: {$htmlPath}");
    //     }

    //     // --- Generate PDF (forms enabled) ---
    //     // NOTE: --enable-forms only works if your HTML inputs have NAME attributes.
    //     // e.g. <input type="text" name="serials" ...>
    //     $cmd = sprintf(
    //         'wkhtmltopdf --enable-local-file-access --enable-forms --print-media-type -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking %s %s 2>&1',
    //         escapeshellarg($htmlPath),
    //         escapeshellarg($pdfPath)
    //     );

    //     $output = shell_exec($cmd);

    //     // Remove html always
    //     @unlink($htmlPath);

    //     // --- Validate PDF creation ---
    //     if (!file_exists($pdfPath) || filesize($pdfPath) < 5000) {
    //         header("HTTP/1.1 500 Internal Server Error");
    //         $msg = "wkhtmltopdf failed.\n\nCMD:\n{$cmd}\n\nOUTPUT:\n{$output}\n";
    //         die(nl2br(htmlspecialchars($msg, ENT_QUOTES, 'UTF-8')));
    //     }

    //     // --- Stream PDF to browser ---
    //     // Clean any output buffer before headers
    //     while (ob_get_level()) {
    //         ob_end_clean();
    //     }

    //     header('Content-Type: application/pdf');
    //     header('Cache-Control: private');
    //     header('Content-Disposition: attachment; filename="' . $fileName . '.pdf"');
    //     header('Content-Length: ' . filesize($pdfPath));

    //     // Stream
    //     $fp = fopen($pdfPath, 'rb');
    //     if ($fp === false) {
    //         header("HTTP/1.1 500 Internal Server Error");
    //         die("Cannot open PDF for reading: {$pdfPath}");
    //     }

    //     fpassthru($fp);
    //     fclose($fp);

    //     // Cleanup
    //     @unlink($pdfPath);
    //     exit;
    // }

    /**
     * HTML -> PDF via wkhtmltopdf, then overlay ONE PDF form field (serial_numbers),
     * and download.
     *
     * Query param used:
     *   serial_numbers
     *
     * Use &debug=1 to draw a grid and adjust coordinates.
     */
    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;

        $recordModel = $this->record->getRecord();

        // ---- Safe filename ----
        $clientID = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$recordModel->get('cf_898'));
        $year = date('Y');
        $fileName = $clientID . '-' . $year . '-SO';

        // ---- Writable temp dir ----
        $tmpDir = rtrim((string)$root_directory, '/\\') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR;
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        if (!is_writable($tmpDir)) {
            $tmpDir = rtrim((string)sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'vtiger_pdf' . DIRECTORY_SEPARATOR;
            if (!is_dir($tmpDir)) {
                @mkdir($tmpDir, 0775, true);
            }
        }
        if (!is_dir($tmpDir) || !is_writable($tmpDir)) {
            header("HTTP/1.1 500 Internal Server Error");
            die('Temp directory not writable: ' . $tmpDir);
        }

        $htmlPath     = $tmpDir . $fileName . '.html';
        $basePdfPath  = $tmpDir . $fileName . '_base.pdf';
        $finalPdfPath = $tmpDir . $fileName . '.pdf';

        // ---- Write HTML ----
        if (@file_put_contents($htmlPath, $html) === false) {
            header("HTTP/1.1 500 Internal Server Error");
            die('Cannot write HTML file: ' . $htmlPath);
        }

        // ---- HTML -> PDF ----
        $cmd = "wkhtmltopdf --enable-local-file-access "
            . "--page-size A4 --dpi 96 --zoom 1 "
            . "--margin-top 0 --margin-right 0 --margin-bottom 0 --margin-left 0 "
            . "--disable-smart-shrinking "
            . escapeshellarg($htmlPath) . " " . escapeshellarg($basePdfPath) . " 2>&1";

        $out = [];
        $code = 0;
        exec($cmd, $out, $code);
        @unlink($htmlPath);

        if ($code !== 0 || !file_exists($basePdfPath) || filesize($basePdfPath) < 2000) {
            header("HTTP/1.1 500 Internal Server Error");
            die("wkhtmltopdf failed (exit=$code):\n" . implode("\n", $out));
        }

        // ---- Import base PDF and overlay ONE field ----
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pageCount = $pdf->setSourceFile($basePdfPath);

        // page 1 only (your screenshot section is page 1)
        $tplId = $pdf->importPage(1);
        $size  = $pdf->getTemplateSize($tplId);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);

        // ---- DEBUG GRID MODE (call with &debug=1) ----
        $debug = (string)$request->get('debug') === '1';
        if ($debug) {
            $pdf->SetFont('helvetica', '', 6);

            $pageW = $pdf->getPageWidth();
            $pageH = $pdf->getPageHeight();

            // Major grid every 10mm
            for ($x = 0; $x <= $pageW; $x += 10) {
                $pdf->Line($x, 0, $x, $pageH, ['width' => 0.1, 'color' => [180, 180, 180]]);
                $pdf->Text($x + 0.5, 1, (string)$x); // x labels at top
            }
            for ($y = 0; $y <= $pageH; $y += 10) {
                $pdf->Line(0, $y, $pageW, $y, ['width' => 0.1, 'color' => [180, 180, 180]]);
                $pdf->Text(1, $y + 0.5, (string)$y); // y labels at left
            }

            // Optional: minor grid every 5mm (lighter)
            for ($x = 0; $x <= $pageW; $x += 5) {
                $pdf->Line($x, 0, $x, $pageH, ['width' => 0.05, 'color' => [220, 220, 220]]);
            }
            for ($y = 0; $y <= $pageH; $y += 5) {
                $pdf->Line(0, $y, $pageW, $y, ['width' => 0.05, 'color' => [220, 220, 220]]);
            }
        }

        // ---- Field appearance ----
        $fieldStyle = [
            'border'    => 0,
            'font'      => 'helvetica',
            'fontsize'  => 8,
            'textcolor' => [0, 0, 0],
        ];

        // ---- ONLY ONE INPUT: serial_numbers ----
        // Adjust X/Y using debug grid:
        // In your screenshot the dotted line starts shortly after the left margin on that "serials" line.
        $x = 18.0;     // <-- adjust
        $y = 63.0;     // <-- adjust
        $serial_input_widthw = 143.0;    // width across dotted line
        $h = 5.5;

        // Serial numbers field
        $pdf->SetXY(32, 153.5); // A4 coords are in mm
        $pdf->TextField(
            'serial_numbers',
            $serial_input_widthw,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('serial_numbers')]
        );

        // Pick up location field (added as example of second field, adjust coords as needed)
        $pdf->SetXY(53.5, 172.0); // A4 coords are in mm
        $pdf->TextField(
            'pick_up_location',
            127.0,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('pick_up_location')]
        );

        // authorised_person_name input
        $pdf->SetXY(74, 181.5); // A4 coords are in mm
        $pdf->TextField(
            'authorised_person_name',
            43,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('authorised_person_name')]
        );

        // authorised_person_id input
        $pdf->SetXY(141.5, 181.5); // A4 coords are in mm
        $pdf->TextField(
            'authorised_person_id',
            40,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('authorised_person_id')]
        );

        // bank_name input
        $pdf->SetXY(52, 219.0); // A4 coords are in mm
        $pdf->TextField(
            'bank_name',
            128,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('bank_name')]
        );

        // bank_address input
        $pdf->SetXY(55, 225.5); // A4 coords are in mm
        $pdf->TextField(
            'bank_address',
            130,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('bank_address')]
        );

        // bank_code input
        $pdf->SetXY(52, 232.5); // A4 coords are in mm
        $pdf->TextField(
            'bank_code',
            52,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('bank_code')]
        );

        // swift_code input
        $pdf->SetXY(122, 232.5); // A4 coords are in mm
        $pdf->TextField(
            'swift_code',
            55,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('swift_code')]
        );

        // account_no input
        $pdf->SetXY(52, 237.0); // A4 coords are in mm
        $pdf->TextField(
            'account_no',
            47,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('account_no')]
        );

        // account_currency input
        $pdf->SetXY(126, 237.5); // A4 coords are in mm
        $pdf->TextField(
            'account_currency',
            47,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('account_currency')]
        );

        // ---- Save final ----
        $pdf->Output($finalPdfPath, 'F');
        @unlink($basePdfPath);

        // ---- Stream to browser ----
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Cache-Control: private');
        header('Content-Disposition: attachment; filename="' . $fileName . '.pdf"');
        header('Content-Length: ' . filesize($finalPdfPath));

        $fp = fopen($finalPdfPath, 'rb');
        if ($fp === false) {
            header("HTTP/1.1 500 Internal Server Error");
            die("Cannot open PDF for reading: {$finalPdfPath}");
        }
        fpassthru($fp);
        fclose($fp);

        @unlink($finalPdfPath);
        exit;
    }
}
