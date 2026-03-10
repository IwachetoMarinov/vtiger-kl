<?php

include_once 'modules/Contacts/download/CoreDownload.php';
/**
 * SaleOrderDownload.php
 *
 * Uses CoreDownload helpers + layout config to keep the class small.
 */

class StockTransferOrderDownload
{
    /** Overlay layout config (TCPDF units are mm by default) */
    private const LAYOUT = [
        'page' => 1,

        'defaults' => [
            'h' => 5.5,
            'style' => ['border' => 0],
            'opts' => [
                // smaller font inside form fields
                'da' => '/Helv 5.5 Tf 0 g',
            ],
        ],

        'fields' => [
            // description
            ['name' => 'description',    'x' => 12.0, 'y' => 132.5, 'w' => 175.0],
            // from / to
            ['name' => 'from_location',  'x' => 61.0, 'y' => 140.0, 'w' => 145.0],
            ['name' => 'to_location',    'x' => 61.0, 'y' => 147.0, 'w' => 145.0],

            // country text input (when "Other" is chosen)
            ['name' => 'country',        'x' => 28.0, 'y' => 196.0, 'w' => 41.0],

            // signature section
            ['name' => 'place_input',    'x' => 22.0, 'y' => 256.0,  'w' => 48.0],
            ['name' => 'signed_by',      'x' => 108.0, 'y' => 256.0,  'w' => 70.0],
            ['name' => 'date_input',     'x' => 22.0, 'y' => 265.0, 'w' => 48.0],
            ['name' => 'on_behalf_of',   'x' => 112.0, 'y' => 265.0, 'w' => 67.0],
        ],

        'grids' => [
            [
                'namePattern' => 'metal_{r}_weight_{c}',
                'startX' => 41.2,
                'startY' => 95.7,
                'cellW'  => 18.10,
                'cellH'  => 6.95,
                'rows'   => 4,
                'cols'   => 9,
                'padX'   => 1.2,
                'padY'   => 0.6,
                'innerPad' => 1.3,
                'opts' => [
                    'da' => '/Helv 5.5 Tf 0 g',
                    'q'  => 1,
                ],
            ],
        ],
    ];

    /**
     * Entry point: create STO PDF from HTML +(wkhtmltopdf) + overlay fields + stream.
     */
    public static function process($html, $recordModel, Vtiger_Request $request): void
    {
        global $root_directory;

        // CLIENT-YYYY-STO
        $fileName = CoreDownload::safeFileName($recordModel, 'STO');

        // temp paths (PDFs stay in tmp)
        $tmpDir = CoreDownload::getWritableTmpDir($root_directory);
        [, $basePdfPath, $finalPdfPath] = CoreDownload::buildPaths($tmpDir, $fileName);

        // HTML must be written in vtiger root so relative assets resolve
        $htmlPath = rtrim($root_directory, "/\\") . DIRECTORY_SEPARATOR . $fileName . '.html';

        CoreDownload::writeFileOrFail($htmlPath, (string)$html);
        // CoreDownload::runWkhtmltopdfOrFail($htmlPath, $basePdfPath);
        CoreDownload::runChromePdfOrFail($htmlPath, $basePdfPath);

        @unlink($htmlPath);

        // import base pdf
        $pdf = CoreDownload::makeFpdi();
        CoreDownload::addPageFromBasePdf($pdf, $basePdfPath, (int)self::LAYOUT['page']);
        @unlink($basePdfPath);

        // optional debug grid
        if ((string)$request->get('debug') === '1') CoreDownload::drawDebugGrid($pdf);

        // overlay fields + metals grid
        CoreDownload::applyLayout($pdf, $request, self::LAYOUT);

        // overlay checkboxes (kept here, because applyLayout handles only TextField/grids)
        self::applyCheckboxes($pdf, $request);

        // save + stream
        $pdf->Output($finalPdfPath, 'F');
        CoreDownload::streamPdfAndCleanup($finalPdfPath, $fileName . '.pdf');
    }

    /**
     * Draw AcroForm checkboxes with fixed coordinates (same as your old code).
     */
    private static function applyCheckboxes($pdf, Vtiger_Request $request): void
    {
        // Drawn as actual AcroForm checkboxes (not ✓ text)
        $makeCheckbox = function (string $name, float $x, float $y, bool $checked) use ($pdf) {
            $size = 3.4;

            $pdf->SetXY($x, $y);
            $pdf->CheckBox(
                $name,
                $size,
                $checked,
                [
                    'border' => 1,
                    'borderWidth' => 0.25,
                    'borderColor' => [0, 0, 0],
                    'fillColor' => [255, 255, 255],
                ],
                [
                    'v'  => $checked ? 'Yes' : 'Off',
                    'dv' => 'Off',
                    'da' => '/ZaDb 10 Tf 0 g',
                ]
            );
        };

        $opt = (string)$request->get('countryOption');

        $makeCheckbox('singapore_checked',   13.0, 160.0, $opt === '1');
        $makeCheckbox('switzerland_checked', 44.0, 160.0, $opt === '2');
        $makeCheckbox('hongkong_checked',    78.5, 160.0, $opt === '3');
        $makeCheckbox('dubai_checked',      112.5, 160.0, $opt === '4');
        $makeCheckbox('other_checked',       13.5, 167.5, $opt === '5');
    }
}
