<?php

class SaleOrderDownload
{
    /** All coordinates/sizes live here */
    private const FORM = [
        'page' => 1,

        // PDF/Form defaults
        'defaults' => [
            'font' => ['helvetica', '', 6.5],
            'formDefault' => ['font' => 'helvetica', 'fontsize' => 6.8, 'textcolor' => [0, 0, 0]],
            'fieldStyle' => ['border' => 0],
            'fieldH' => 5.5,
            'fieldDa' => '/Helv 5.5 Tf 0 g',
        ],

        // Simple one-off fields (same height unless overridden)
        'fields' => [
            ['name' => 'serial_numbers',         'x' => 32.0,  'y' => 148.0,  'w' => 145.0],
            ['name' => 'pick_up_location',       'x' => 51.5,  'y' => 166.0,  'w' => 127.0],
            ['name' => 'authorised_person_name', 'x' => 70.0,  'y' => 173.75, 'w' => 44.0],
            ['name' => 'authorised_person_id',   'x' => 134.0, 'y' => 173.75, 'w' => 44.0],

            ['name' => 'bank_name',              'x' => 50.0,  'y' => 203.0,  'w' => 125.0],
            ['name' => 'bank_address',           'x' => 53.0,  'y' => 210.0,  'w' => 120.0],
            ['name' => 'bank_code',              'x' => 49.0,  'y' => 217.0,  'w' => 51.0],
            ['name' => 'swift_code',             'x' => 120.0, 'y' => 217.0,  'w' => 55.0],
            ['name' => 'account_no',             'x' => 49.0,  'y' => 224.0,  'w' => 47.0],
            ['name' => 'account_currency',       'x' => 124.0, 'y' => 224.0,  'w' => 49.0],

            ['name' => 'place_input',            'x' => 41.0,  'y' => 235.5,  'w' => 45.0],
            ['name' => 'signed_by',              'x' => 109.0, 'y' => 235.5,  'w' => 65.0],
            ['name' => 'date_input',             'x' => 41.0,  'y' => 243.8,  'w' => 45.0],
            ['name' => 'on_behalf_of',           'x' => 113.0, 'y' => 243.8,  'w' => 62.0],
        ],

        // Metals grid definition
        'grids' => [
            [
                'prefix' => 'metal',
                'namePattern' => 'metal_{r}_weight_{c}', // request param names
                'startX' => 58.0,
                'startY' => 115.2,
                'cellW'  => 13.5,
                'cellH'  => 6.6,
                'rows'   => 4,  // metals
                'cols'   => 9,  // weights
                'padX'   => 0.6,
                'padY'   => 0.6,
                'innerPad' => 1.2, // field size = cell - innerPad
            ],
        ],
    ];

    public static function process($html, $recordModel, Vtiger_Request $request)
    {
        global $root_directory;

        $fileName = self::safeFileName($recordModel);

        $tmpDir = self::getWritableTmpDir($root_directory);
        [$htmlPath, $basePdfPath, $finalPdfPath] = self::buildPaths($tmpDir, $fileName);

        self::writeFileOrFail($htmlPath, $html);
        self::runWkhtmltopdfOrFail($htmlPath, $basePdfPath);
        @unlink($htmlPath);

        $pdf = self::makePdf();

        self::addPageFromBasePdf($pdf, $basePdfPath, self::FORM['page']);
        @unlink($basePdfPath);

        if ((string)$request->get('debug') === '1') {
            self::drawDebugGrid($pdf);
        }

        self::applyConfiguredFields($pdf, $request);

        $pdf->Output($finalPdfPath, 'F');
        self::streamPdfAndCleanup($finalPdfPath, $fileName . '.pdf');
    }

    // ----------------- helpers -----------------

    private static function safeFileName($recordModel): string
    {
        $clientID = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$recordModel->get('cf_898'));
        $year = date('Y');
        return $clientID . '-' . $year . '-SO';
    }

    private static function getWritableTmpDir(string $root_directory): string
    {
        $dir = rtrim($root_directory, '/\\') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        if (!is_writable($dir)) {
            $dir = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'vtiger_pdf' . DIRECTORY_SEPARATOR;
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            header("HTTP/1.1 500 Internal Server Error");
            die('Temp directory not writable: ' . $dir);
        }
        return $dir;
    }

    private static function buildPaths(string $tmpDir, string $fileName): array
    {
        $htmlPath     = $tmpDir . $fileName . '.html';
        $basePdfPath  = $tmpDir . $fileName . '_base.pdf';
        $finalPdfPath = $tmpDir . $fileName . '.pdf';
        return [$htmlPath, $basePdfPath, $finalPdfPath];
    }

    private static function writeFileOrFail(string $path, string $content): void
    {
        if (@file_put_contents($path, $content) === false) {
            header("HTTP/1.1 500 Internal Server Error");
            die('Cannot write file: ' . $path);
        }
    }

    private static function runWkhtmltopdfOrFail(string $htmlPath, string $basePdfPath): void
    {
        $cmd = "wkhtmltopdf --enable-local-file-access "
            . "--page-size A4 --dpi 96 --zoom 1 "
            . "--margin-top 0 --margin-right 0 --margin-bottom 0 --margin-left 0 "
            . "--disable-smart-shrinking "
            . escapeshellarg($htmlPath) . " " . escapeshellarg($basePdfPath) . " 2>&1";

        $out = [];
        $code = 0;
        exec($cmd, $out, $code);

        if ($code !== 0 || !file_exists($basePdfPath) || filesize($basePdfPath) < 2000) {
            header("HTTP/1.1 500 Internal Server Error");
            die("wkhtmltopdf failed (exit=$code):\n" . implode("\n", $out));
        }
    }

    private static function makePdf()
    {
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        [$font, $style, $size] = self::FORM['defaults']['font'];
        $pdf->SetFont($font, $style, $size);

        $pdf->setFormDefaultProp(self::FORM['defaults']['formDefault']);
        return $pdf;
    }

    private static function addPageFromBasePdf($pdf, string $basePdfPath, int $pageNo): void
    {
        $pdf->setSourceFile($basePdfPath);

        $tplId = $pdf->importPage($pageNo);
        $size  = $pdf->getTemplateSize($tplId);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);
    }

    private static function drawDebugGrid($pdf): void
    {
        $pdf->SetFont('helvetica', '', 6);

        $pageW = $pdf->getPageWidth();
        $pageH = $pdf->getPageHeight();

        for ($x = 0; $x <= $pageW; $x += 10) {
            $pdf->Line($x, 0, $x, $pageH, ['width' => 0.1, 'color' => [180, 180, 180]]);
            $pdf->Text($x + 0.5, 1, (string)$x);
        }
        for ($y = 0; $y <= $pageH; $y += 10) {
            $pdf->Line(0, $y, $pageW, $y, ['width' => 0.1, 'color' => [180, 180, 180]]);
            $pdf->Text(1, $y + 0.5, (string)$y);
        }
        for ($x = 0; $x <= $pageW; $x += 5) {
            $pdf->Line($x, 0, $x, $pageH, ['width' => 0.05, 'color' => [220, 220, 220]]);
        }
        for ($y = 0; $y <= $pageH; $y += 5) {
            $pdf->Line(0, $y, $pageW, $y, ['width' => 0.05, 'color' => [220, 220, 220]]);
        }
    }

    private static function applyConfiguredFields($pdf, Vtiger_Request $request): void
    {
        $def = self::FORM['defaults'];
        $style = $def['fieldStyle'];
        $hDefault = $def['fieldH'];
        $baseOpts = ['da' => $def['fieldDa']];

        // Simple fields
        foreach (self::FORM['fields'] as $f) {
            $name = $f['name'];
            $x = (float)$f['x'];
            $y = (float)$f['y'];
            $w = (float)$f['w'];
            $h = isset($f['h']) ? (float)$f['h'] : (float)$hDefault;

            $pdf->SetXY($x, $y);
            $pdf->TextField(
                $name,
                $w,
                $h,
                $style,
                $baseOpts + ['v' => (string)$request->get($name)]
            );
        }

        // Grids (metals table etc.)
        foreach (self::FORM['grids'] as $g) {
            $rows = (int)$g['rows'];
            $cols = (int)$g['cols'];

            $startX = (float)$g['startX'];
            $startY = (float)$g['startY'];
            $cellW  = (float)$g['cellW'];
            $cellH  = (float)$g['cellH'];

            $padX = (float)$g['padX'];
            $padY = (float)$g['padY'];
            $innerPad = (float)$g['innerPad'];

            $fieldW = $cellW - $innerPad;
            $fieldH = $cellH - $innerPad;

            for ($r = 0; $r < $rows; $r++) {
                for ($c = 0; $c < $cols; $c++) {
                    $name = str_replace(['{r}', '{c}'], [$r, $c], $g['namePattern']);
                    $value = (string)($request->get($name) ?? '');

                    $x = $startX + ($c * $cellW) + $padX;
                    $y = $startY + ($r * $cellH) + $padY;

                    $pdf->SetXY($x, $y);
                    $pdf->TextField($name, $fieldW, $fieldH, $style, $baseOpts + ['v' => $value]);
                }
            }
        }
    }

    private static function streamPdfAndCleanup(string $finalPdfPath, string $downloadName): void
    {
        while (ob_get_level()) ob_end_clean();

        header('Content-Type: application/pdf');
        header('Cache-Control: private');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
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
