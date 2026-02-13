<?php

class SaleOrderDownload
{
    public static function process($html, $recordModel, Vtiger_Request $request)
    {
        global $root_directory;

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

        // Ensure TCPDF sets up font resources for AcroForm
        $pdf->SetFont('helvetica', '', 6.5);

        // Set global default form appearance (creates /F1 in /AcroForm /DR)
        $pdf->setFormDefaultProp([
            'font' => 'helvetica',
            'fontsize' => 6.8,
            'textcolor' => [0, 0, 0],
        ]);

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
                $pdf->Text($x + 0.5, 1, (string)$x);
            }
            for ($y = 0; $y <= $pageH; $y += 10) {
                $pdf->Line(0, $y, $pageW, $y, ['width' => 0.1, 'color' => [180, 180, 180]]);
                $pdf->Text(1, $y + 0.5, (string)$y);
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
        $fieldStyle = ['border'    => 0,];

        // ---- ONLY ONE INPUT: serial_numbers ----
        $x = 18.0;
        $y = 63.0;
        $h = 5.5;
        $serial_input_widthw = 145.0;

        // Serial numbers field
        $pdf->SetXY(32, 148.0);
        $pdf->TextField(
            'serial_numbers',
            $serial_input_widthw,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('serial_numbers')]
        );

        // Pick up location field (added as example of second field, adjust coords as needed)
        $pdf->SetXY(51.5, 166.0);
        $pdf->TextField(
            'pick_up_location',
            127.0,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('pick_up_location')]
        );

        // authorised_person_name input
        $pdf->SetXY(70, 173.75);
        $pdf->TextField(
            'authorised_person_name',
            44,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('authorised_person_name')]
        );

        // authorised_person_id input
        $pdf->SetXY(134, 173.75);
        $pdf->TextField(
            'authorised_person_id',
            44,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('authorised_person_id')]
        );

        // bank_name input
        $pdf->SetXY(50, 203.0);
        $pdf->TextField(
            'bank_name',
            125,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('bank_name')]
        );

        // bank_address input
        $pdf->SetXY(53, 210.0);
        $pdf->TextField(
            'bank_address',
            120,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('bank_address')]
        );

        // bank_code input
        $pdf->SetXY(49, 217.0);
        $pdf->TextField(
            'bank_code',
            51,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('bank_code')]
        );

        // swift_code input
        $pdf->SetXY(120, 217.0);
        $pdf->TextField(
            'swift_code',
            55,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('swift_code')]
        );

        // account_no input
        $pdf->SetXY(49, 224.0);
        $pdf->TextField(
            'account_no',
            47,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('account_no')]
        );

        // account_currency input
        $pdf->SetXY(124, 224.0);
        $pdf->TextField(
            'account_currency',
            49,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('account_currency')]
        );

        // place_input input
        $pdf->SetXY(41, 235.5);
        $pdf->TextField(
            'place_input',
            45,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('place_input')]
        );

        // signed_by input
        $pdf->SetXY(109, 235.5);
        $pdf->TextField(
            'signed_by',
            65,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('signed_by')]
        );

        // date_input input
        $pdf->SetXY(41, 243.8);
        $pdf->TextField(
            'date_input',
            45,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('date_input')]
        );

        // on_behalf_of input
        $pdf->SetXY(113, 243.8);
        $pdf->TextField(
            'on_behalf_of',
            62,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('on_behalf_of')]
        );

        // ---- METALS TABLE CONFIG (ADJUSTED) ----
        $startX = 58.0;   // was ~48.0
        $startY = 115.2;  // was ~116.0
        $cellW  = 13.5;   // was ~15
        $cellH  = 6.6;    // was ~7

        $metalCount  = 4;   // Gold, Silver, Platinum, Palladium
        $weightCount = 9;   // 1000oz ... Other

        $offsetX = 0.6;   // move inside cell (right)
        $offsetY = 0.6;   // move inside cell (down)

        $fieldW  = $cellW - 1.2; // leave padding both sides
        $fieldH  = $cellH - 1.2;

        $fieldStyle = [
            'border' => 0,
        ];

        $fieldOptsBase = [
            'da' => '/Helv 5.5 Tf 0 g',   // smaller font
        ];

        // ---- METALS TABLE FIELDS ----
        for ($mi = 0; $mi < $metalCount; $mi++) {
            for ($wi = 0; $wi < $weightCount; $wi++) {

                $fieldName = "metal_{$mi}_weight_{$wi}";
                $value     = (string)($request->get($fieldName) ?? '');

                $x = $startX + ($wi * $cellW) + $offsetX;
                $y = $startY + ($mi * $cellH) + $offsetY;

                $pdf->SetXY($x, $y);
                $pdf->TextField(
                    $fieldName,
                    $fieldW,
                    $fieldH,
                    $fieldStyle,
                    array_merge($fieldOptsBase, [
                        'v' => $value, // blank allowed
                    ])
                );
            }
        }


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
