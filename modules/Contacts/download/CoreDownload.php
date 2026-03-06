<?php

/**
 * CoreDownload.php
 *
 * Shared helpers for all vtiger “Download as PDF” actions:
 * - Safe filename
 * - Writable temp dir + path builder
 * - Write HTML
 * - wkhtmltopdf runner (with overridable options)
 * - FPDI/TCPDF page import helper
 * - Optional debug grid
 * - Stream PDF + cleanup
 */

final class CoreDownload
{
    /**
     * Build a safe download file name like: CLIENTID-2026-SO
     *
     * @param mixed  $recordModel  vtiger record model (must support ->get())
     * @param string $suffix       e.g. 'SO', 'INV', 'DELIVERY'
     * @param string $clientField  record field containing client ID (default cf_898)
     */
    public static function safeFileName($recordModel, string $suffix, string $clientField = 'cf_898'): string
    {
        $clientID = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$recordModel->get($clientField));
        $year = date('Y');
        $suffix = preg_replace('/[^A-Za-z0-9_-]/', '', $suffix);

        // Avoid empty client IDs producing weird names
        if ($clientID === '') {
            $clientID = 'CLIENT';
        }

        return $clientID . '-' . $year . '-' . $suffix;
    }

    /**
     * Returns a writable temp directory. Tries vtiger storage first, then OS temp.
     */
    public static function getWritableTmpDir(string $root_directory): string
    {
        $dir = rtrim($root_directory, '/\\') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        if (!is_writable($dir)) {
            $dir = rtrim((string)sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'vtiger_pdf' . DIRECTORY_SEPARATOR;
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
        }

        if (!is_dir($dir) || !is_writable($dir)) {
            header("HTTP/1.1 500 Internal Server Error");
            die('Temp directory not writable: ' . $dir);
        }

        return $dir;
    }

    /**
     * Build standard temp paths for one download:
     * - HTML input
     * - base PDF (wkhtmltopdf output)
     * - final PDF (with overlays)
     */
    public static function buildPaths(string $tmpDir, string $fileName): array
    {
        $htmlPath     = $tmpDir . $fileName . '.html';
        $basePdfPath  = $tmpDir . $fileName . '_base.pdf';
        $finalPdfPath = $tmpDir . $fileName . '.pdf';
        return [$htmlPath, $basePdfPath, $finalPdfPath];
    }

    /**
     * Writes a file and terminates with HTTP 500 on failure.
     */
    public static function writeFileOrFail(string $path, string $content): void
    {
        if (@file_put_contents($path, $content) === false) {
            header("HTTP/1.1 500 Internal Server Error");
            die('Cannot write file: ' . $path);
        }
    }

    /**
     * Build a wkhtmltopdf command with sane defaults.
     * You can override defaults via $opts (e.g. margins, orientation).
     *
     * Example override:
     *   ['--orientation' => 'Landscape', '--margin-top' => '5']
     */
    public static function wkhtmltopdfCmd(string $htmlPath, string $pdfPath, array $opts = []): string
    {
        $defaults = [
            '--enable-local-file-access' => null,
            '--page-size' => 'A4',
            '--dpi' => '96',
            '--zoom' => '1',
            '--margin-top' => '0',
            '--margin-right' => '0',
            '--margin-bottom' => '0',
            '--margin-left' => '0',
            '--disable-smart-shrinking' => null,
        ];

        // Caller overrides take priority
        $opts = $opts + $defaults;

        $parts = ['wkhtmltopdf'];
        foreach ($opts as $k => $v) {
            $parts[] = ($v === null) ? $k : ($k . ' ' . escapeshellarg((string)$v));
        }

        $parts[] = escapeshellarg($htmlPath);
        $parts[] = escapeshellarg($pdfPath);

        // Capture stderr into stdout for troubleshooting
        return implode(' ', $parts) . ' 2>&1';
    }

    /**
     * Runs wkhtmltopdf and validates output.
     */
    public static function runWkhtmltopdfOrFail(string $htmlPath, string $basePdfPath, array $opts = []): void
    {
        $cmd = self::wkhtmltopdfCmd($htmlPath, $basePdfPath, $opts);

        $out = [];
        $code = 0;
        exec($cmd, $out, $code);

        if ($code !== 0 || !file_exists($basePdfPath) || filesize($basePdfPath) < 2000) {
            header("HTTP/1.1 500 Internal Server Error");
            die("wkhtmltopdf failed (exit=$code):\n" . implode("\n", $out));
        }
    }

    /**
     * Create a ready-to-use FPDI(TCPDF) instance with default form appearance.
     */
    public static function makeFpdi(array $formDefault = null)
    {
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        // Ensures font resources exist for AcroForm
        $pdf->SetFont('helvetica', '', 6.5);

        $pdf->setFormDefaultProp($formDefault ?? [
            'font' => 'helvetica',
            'fontsize' => 6.8,
            'textcolor' => [0, 0, 0],
        ]);

        return $pdf;
    }

    /**
     * Imports one page from the base PDF and adds it as a new page in $pdf.
     */
    public static function addPageFromBasePdf($pdf, string $basePdfPath, int $pageNo): void
    {
        $pdf->setSourceFile($basePdfPath);

        $tplId = $pdf->importPage($pageNo);
        $size  = $pdf->getTemplateSize($tplId);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);
    }

    /**
     * Draws a mm grid on the PDF to help coordinate positioning.
     */
    public static function drawDebugGrid($pdf): void
    {
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

        // Minor grid every 5mm (lighter)
        for ($x = 0; $x <= $pageW; $x += 5) {
            $pdf->Line($x, 0, $x, $pageH, ['width' => 0.05, 'color' => [220, 220, 220]]);
        }
        for ($y = 0; $y <= $pageH; $y += 5) {
            $pdf->Line(0, $y, $pageW, $y, ['width' => 0.05, 'color' => [220, 220, 220]]);
        }
    }

    /**
     * Streams a PDF file as an attachment and deletes it afterwards.
     */
    public static function streamPdfAndCleanup(string $finalPdfPath, string $downloadName): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

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

    /**
     * Generic field applier:
     * - Apply simple fields list
     * - Apply one or more grids
     *
     * Layout format:
     * [
     *   'defaults' => ['h'=>5.5, 'style'=>['border'=>0], 'opts'=>['da'=>'...']],
     *   'fields' => [ ['name'=>'x', 'x'=>1,'y'=>2,'w'=>10,'h'=>5.5,'opts'=>[] ], ... ],
     *   'grids' => [ ['namePattern'=>'metal_{r}_weight_{c}', ...], ... ]
     * ]
     */
    public static function applyLayout($pdf, Vtiger_Request $request, array $layout): void
    {
        $defaults = $layout['defaults'] ?? [];

        $hDefault = isset($defaults['h']) ? (float)$defaults['h'] : 5.5;
        $style    = $defaults['style'] ?? ['border' => 0];
        $baseOpts = $defaults['opts'] ?? [];

        // -------- simple fields --------
        foreach (($layout['fields'] ?? []) as $f) {
            $name = (string)$f['name'];
            $x = (float)$f['x'];
            $y = (float)$f['y'];
            $w = (float)$f['w'];
            $h = isset($f['h']) ? (float)$f['h'] : $hDefault;

            $opts = $baseOpts;
            if (isset($f['opts']) && is_array($f['opts'])) {
                $opts = array_merge($opts, $f['opts']);
            }

            $opts['v'] = (string)$request->get($name);

            $pdf->SetXY($x, $y);
            $pdf->TextField($name, $w, $h, $style, $opts);
        }

        // -------- grids --------
        foreach (($layout['grids'] ?? []) as $g) {
            $rows = (int)$g['rows'];
            $cols = (int)$g['cols'];

            $startX = (float)$g['startX'];
            $startY = (float)$g['startY'];
            $cellW  = (float)$g['cellW'];
            $cellH  = (float)$g['cellH'];

            $padX = isset($g['padX']) ? (float)$g['padX'] : 0.0;
            $padY = isset($g['padY']) ? (float)$g['padY'] : 0.0;

            $innerPad = isset($g['innerPad']) ? (float)$g['innerPad'] : 0.0;
            $fieldW = $cellW - $innerPad;
            $fieldH = $cellH - $innerPad;

            $pattern = (string)$g['namePattern'];

            $gridOpts = $baseOpts;
            if (isset($g['opts']) && is_array($g['opts'])) {
                $gridOpts = array_merge($gridOpts, $g['opts']);
            }

            for ($r = 0; $r < $rows; $r++) {
                for ($c = 0; $c < $cols; $c++) {
                    $name = str_replace(['{r}', '{c}'], [$r, $c], $pattern);
                    $value = (string)($request->get($name) ?? '');

                    $x = $startX + ($c * $cellW) + $padX;
                    $y = $startY + ($r * $cellH) + $padY;

                    $opts = $gridOpts;
                    $opts['v'] = $value;

                    $pdf->SetXY($x, $y);
                    $pdf->TextField($name, $fieldW, $fieldH, $style, $opts);
                }
            }
        }
    }
}
