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
            ['name' => 'serial_numbers',         'x' => 12.0,  'y' => 147.0,  'w' => 155.0],
            ['name' => 'pick_up_location',       'x' => 35.5,  'y' => 169.5,  'w' => 147.0],

            ['name' => 'authorised_person_name', 'x' => 57.0,  'y' => 178.0, 'w' => 60.0],
            ['name' => 'authorised_person_id',   'x' => 145.0, 'y' => 178.0, 'w' => 61.0],

            ['name' => 'bank_name',              'x' => 31.0,  'y' => 216.5,  'w' => 135.0],
            ['name' => 'bank_address',           'x' => 35.0,  'y' => 224.0,  'w' => 135.0],
            ['name' => 'bank_code',              'x' => 34.0,  'y' => 231.5,  'w' => 61.0],
            ['name' => 'swift_code',             'x' => 127.0, 'y' => 231.5,  'w' => 65.0],

            ['name' => 'account_no',             'x' => 34.0,  'y' => 238.0,  'w' => 57.0],
            ['name' => 'account_currency',       'x' => 133.0, 'y' => 238.0,  'w' => 59.0],

            ['name' => 'place_input',            'x' => 21.0,  'y' => 252.5,  'w' => 65.0],
            ['name' => 'signed_by',              'x' => 110.0, 'y' => 252.5,  'w' => 75.0],

            ['name' => 'date_input',             'x' => 21.0,  'y' => 260.3,  'w' => 65.0],
            ['name' => 'on_behalf_of',           'x' => 114.0, 'y' => 260.3,  'w' => 72.0],
        ],

        'grids' => [
            [
                // Request fields: metal_0_weight_0 ... metal_3_weight_8
                'namePattern' => 'metal_{r}_weight_{c}',
                'startX' => 40.8,
                'startY' => 112.2,
                'cellW'  => 18.05,
                'cellH'  => 6.65,
                'rows'   => 4,
                'cols'   => 9,
                'padX'   => 1.2,
                'padY'   => 0.6,
                'innerPad' => 1.3,
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

        // temp paths (PDFs stay in tmp)
        $tmpDir = CoreDownload::getWritableTmpDir($root_directory);
        [, $basePdfPath, $finalPdfPath] = CoreDownload::buildPaths($tmpDir, $fileName);

        // HTML must be written in vtiger root so relative assets resolve
        $htmlPath = rtrim($root_directory, "/\\") . DIRECTORY_SEPARATOR . $fileName . '.html';

        CoreDownload::writeFileOrFail($htmlPath, (string)$html);
        // CoreDownload::runWkhtmltopdfOrFail($htmlPath, $basePdfPath);
        CoreDownload::runChromePdfOrFail($htmlPath, $basePdfPath);

        @unlink($htmlPath);

        // Import base PDF and overlay fields
        $pdf = CoreDownload::makeFpdi();
        CoreDownload::addPageFromBasePdf($pdf, $basePdfPath, (int)self::LAYOUT['page']);
        @unlink($basePdfPath);

        // Debug grid
        if ((string)$request->get('debug') === '1')  CoreDownload::drawDebugGrid($pdf);

        // Apply all fields/grids from config
        CoreDownload::applyLayout($pdf, $request, self::LAYOUT);

        // Save & stream
        $pdf->Output($finalPdfPath, 'F');
        CoreDownload::streamPdfAndCleanup($finalPdfPath, $fileName . '.pdf');
    }
}
