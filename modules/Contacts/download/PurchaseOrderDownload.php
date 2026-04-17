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
            ['name' => 'currency',     'x' => 43.0,  'y' => 132.0,  'w' => 40.5, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'location',     'x' => 91.0, 'y' => 149.5,  'w' => 42.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'address',      'x' => 57.0,  'y' => 157.3,  'w' => 60.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'country',      'x' => 32.0,  'y' => 175.5,  'w' => 60.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],

            ['name' => 'place_input',  'x' => 21.0,  'y' => 258.6,  'w' => 50.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'signed_by',    'x' => 108.0, 'y' => 258.6,  'w' => 69.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'date_input',   'x' => 22.0,  'y' => 265.5,  'w' => 50.0, 'opts' => ['da' => '/Helv 6.5 Tf 0 g']],
            ['name' => 'on_behalf_of', 'x' => 112.0, 'y' => 265.5,  'w' => 66.0],
        ],

        'grids' => [
            [
                'namePattern' => 'metal_{r}_weight_{c}',
                'startX' => 42.0,
                'startY' => 101.0,
                'cellW'  => 18.0,
                'cellH'  => 7.00,
                'rows'   => 4,
                'cols'   => 9,
                'padX'   => 0.65,
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

        $makeCheckbox('country_checked',   14.0, 150.5, $countryChk);
        $makeCheckbox('address_checked',   14.0, 158.0, $addressChk);

        $makeCheckbox('pricing_option_1',  12.0, 232.5, $firstPricing);
        $makeCheckbox('pricing_option_2',  12.0, 239.5, $secondPricing);
    }
}
