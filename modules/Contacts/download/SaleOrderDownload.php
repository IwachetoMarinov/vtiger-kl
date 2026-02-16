<?php

include_once 'modules/Contacts/download/CoreDownload.php';
/**
 * SaleOrderDownload.php
 *
 * Uses CoreDownload helpers + layout config to keep the class small.
 */

class SaleOrderDownload
{
    /** Layout / coordinates for the Sale Order PDF overlay (units: TCPDF default = mm) */
    private const LAYOUT = [
        'page' => 1,

        'defaults' => [
            'h' => 5.5,
            'style' => ['border' => 0],
            // Default appearance for fields (smaller font)
            'opts' => ['da' => '/Helv 5.5 Tf 0 g'],
        ],

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

        'grids' => [
            [
                // Request fields: metal_0_weight_0 ... metal_3_weight_8
                'namePattern' => 'metal_{r}_weight_{c}',
                'startX' => 58.0,
                'startY' => 115.2,
                'cellW'  => 13.5,
                'cellH'  => 6.6,
                'rows'   => 4,
                'cols'   => 9,
                'padX'   => 0.6,
                'padY'   => 0.6,
                'innerPad' => 1.2,
                // Optional grid-only overrides:
                'opts' => [
                    'da' => '/Helv 5.5 Tf 0 g',
                    'q'  => 1, // <-- center align (PDF /Q = 1)
                ],
            ],
        ],
    ];

    public static function process($html, $recordModel, Vtiger_Request $request)
    {
        global $root_directory;

        // File name like CLIENT-2026-SO
        $fileName = CoreDownload::safeFileName($recordModel, 'SO');

        // Temp paths
        $tmpDir = CoreDownload::getWritableTmpDir($root_directory);
        [$htmlPath, $basePdfPath, $finalPdfPath] = CoreDownload::buildPaths($tmpDir, $fileName);

        // HTML -> base PDF
        CoreDownload::writeFileOrFail($htmlPath, (string)$html);
        CoreDownload::runWkhtmltopdfOrFail($htmlPath, $basePdfPath);
        @unlink($htmlPath);

        // Import base PDF and overlay fields
        $pdf = CoreDownload::makeFpdi();
        CoreDownload::addPageFromBasePdf($pdf, $basePdfPath, (int)self::LAYOUT['page']);
        @unlink($basePdfPath);

        // Debug grid
        if ((string)$request->get('debug') === '1') {
            CoreDownload::drawDebugGrid($pdf);
        }

        // Apply all fields/grids from config
        CoreDownload::applyLayout($pdf, $request, self::LAYOUT);

        // Save & stream
        $pdf->Output($finalPdfPath, 'F');
        CoreDownload::streamPdfAndCleanup($finalPdfPath, $fileName . '.pdf');
    }
}
