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
            ['name' => 'description',    'x' => 32.0, 'y' => 141.5, 'w' => 145.0],
            // from / to
            ['name' => 'from_location',  'x' => 32.0, 'y' => 155.0, 'w' => 145.0],
            ['name' => 'to_location',    'x' => 32.0, 'y' => 167.0, 'w' => 145.0],

            // country text input (when "Other" is chosen)
            ['name' => 'country',        'x' => 47.0, 'y' => 209.5, 'w' => 41.0],

            // signature section
            ['name' => 'place_input',    'x' => 41.0, 'y' => 264.0,  'w' => 48.0],
            ['name' => 'signed_by',      'x' => 108.0, 'y' => 264.0,  'w' => 70.0],
            ['name' => 'date_input',     'x' => 41.0, 'y' => 270.75, 'w' => 48.0],
            ['name' => 'on_behalf_of',   'x' => 112.0, 'y' => 270.75, 'w' => 67.0],
        ],

        'grids' => [
            [
                'namePattern' => 'metal_{r}_weight_{c}',
                'startX' => 57.5,
                'startY' => 107.1,
                'cellW'  => 13.57,
                'cellH'  => 6.75,
                'rows'   => 4,   // metals
                'cols'   => 9,   // weights
                'padX'   => 0.6,
                'padY'   => 0.6,
                'innerPad' => 1.2,
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

        // temp paths
        $tmpDir = CoreDownload::getWritableTmpDir($root_directory);
        [$htmlPath, $basePdfPath, $finalPdfPath] = CoreDownload::buildPaths($tmpDir, $fileName);

        // HTML -> base PDF
        CoreDownload::writeFileOrFail($htmlPath, (string)$html);
        CoreDownload::runWkhtmltopdfOrFail($htmlPath, $basePdfPath);
        @unlink($htmlPath);

        // import base pdf
        $pdf = CoreDownload::makeFpdi();
        CoreDownload::addPageFromBasePdf($pdf, $basePdfPath, (int)self::LAYOUT['page']);
        @unlink($basePdfPath);

        // optional debug grid
        if ((string)$request->get('debug') === '1') {
            CoreDownload::drawDebugGrid($pdf);
        }

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

        $makeCheckbox('singapore_checked',   33.5, 178.5, $opt === '1');
        $makeCheckbox('switzerland_checked', 55.0, 178.5, $opt === '2');
        $makeCheckbox('hongkong_checked',    78.5, 178.5, $opt === '3');
        $makeCheckbox('dubai_checked',      100.0, 178.5, $opt === '4');
        $makeCheckbox('other_checked',       33.5, 183.5, $opt === '5');
    }
}
