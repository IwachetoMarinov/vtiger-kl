<?php

include_once 'modules/Contacts/download/CoreDownload.php';

/**
 * PurchaseOrderDownload.php
 *
 * Uses CoreDownload helpers + layout config to keep the class small.
 */
class PurchaseOrderDownload
{
    /** Layout / coordinates for Purchase Order overlay */
    private const LAYOUT = [
        'page' => 1,

        'defaults' => [
            'h' => 5.5,
            'style' => ['border' => 0],
            // base default appearance for text fields
            'opts' => ['da' => '/Helv 6.5 Tf 0 g'],
        ],

        'fields' => [
            ['name' => 'currency',     'x' => 45.0,  'y' => 124.0,  'w' => 40.5, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'location',     'x' => 90.0, 'y' => 141.5,  'w' => 42.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'address',      'x' => 57.0,  'y' => 149.3,  'w' => 55.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'country',      'x' => 34.0,  'y' => 167.5,  'w' => 48.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],

            ['name' => 'place_input',  'x' => 28.0,  'y' => 255.5,  'w' => 50.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'signed_by',    'x' => 107.0, 'y' => 255.5,  'w' => 69.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'date_input',   'x' => 29.0,  'y' => 262.0,  'w' => 50.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'on_behalf_of', 'x' => 111.0, 'y' => 262.0,  'w' => 66.0],
        ],

        'grids' => [
            [
                'namePattern' => 'metal_{r}_weight_{c}',
                'startX' => 45.2,
                'startY' => 94.8,
                'cellW'  => 17.05,
                'cellH'  => 6.87,
                'rows'   => 4,
                'cols'   => 9,
                'padX'   => 0.6,
                'padY'   => 0.40,
                'innerPad' => 1.2,
                'opts' => [
                    'da' => '/Helv 5.5 Tf 0 g',
                    'q'  => 1, // <-- center align (PDF /Q = 1)
                ],
            ],
        ],
    ];

    /**
     * Create PO PDF from HTML (wkhtmltopdf) + overlay fields + stream
     */
    public static function process($html, $recordModel, Vtiger_Request $request): void
    {
        global $root_directory;

        // CLIENT-YYYY-PO
        $fileName = CoreDownload::safeFileName($recordModel, 'PO');

        // temp paths
        // temp paths (PDFs stay in tmp)
        $tmpDir = CoreDownload::getWritableTmpDir($root_directory);
        [, $basePdfPath, $finalPdfPath] = CoreDownload::buildPaths($tmpDir, $fileName);

        // HTML must be written in vtiger root so relative assets resolve
        $htmlPath = rtrim($root_directory, "/\\") . DIRECTORY_SEPARATOR . $fileName . '.html';

        CoreDownload::writeFileOrFail($htmlPath, (string)$html);
        // CoreDownload::runWkhtmltopdfOrFail($htmlPath, $basePdfPath);
        CoreDownload::runChromePdfOrFail($htmlPath, $basePdfPath);

        @unlink($htmlPath);

        // Import base PDF
        $pdf = CoreDownload::makeFpdi();
        CoreDownload::addPageFromBasePdf($pdf, $basePdfPath, (int)self::LAYOUT['page']);
        @unlink($basePdfPath);

        // Debug grid
        if ((string)$request->get('debug') === '1')  CoreDownload::drawDebugGrid($pdf);

        // Overlay text fields + metals grid
        CoreDownload::applyLayout($pdf, $request, self::LAYOUT);

        // Overlay checkboxes
        self::applyCheckboxes($pdf, $request);

        // Save + stream
        $pdf->Output($finalPdfPath, 'F');
        CoreDownload::streamPdfAndCleanup($finalPdfPath, $fileName . '.pdf');
    }

    /**
     * AcroForm checkboxes for PO
     */
    private static function applyCheckboxes($pdf, Vtiger_Request $request): void
    {
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

        $firstPricing  = (string)$request->get('pricing_option') === '1';
        $secondPricing = (string)$request->get('pricing_option') === '2';
        $countryChk    = (string)$request->get('countryOption') === '1';
        $addressChk    = (string)$request->get('addressOption') === '1';

        $makeCheckbox('country_checked',   19.0, 143.5, $countryChk);
        $makeCheckbox('address_checked',   19.0, 150.3, $addressChk);

        $makeCheckbox('pricing_option_1',  17.0, 228.5, $firstPricing);
        $makeCheckbox('pricing_option_2',  17.0, 235.5, $secondPricing);
    }
}
