<?php

include_once 'modules/Contacts/download/CoreDownload.php';

/**
 * CollectionRequestDownload.php
 *
 * Refactor of your old downloadPDF() using CoreDownload for:
 * - temp dir / paths
 * - wkhtmltopdf
 * - FPDI init + import page
 * - debug grid
 * - streaming
 *
 * Keeps your dynamic row logic + checkbox logic.
 */
class CollectionRequestDownload
{
    /**
     * wkhtmltopdf options specific for CR (needs --print-media-type)
     */
    private const WKHTML_OPTS = [
        '--print-media-type' => null,
    ];

    /**
     * Static fields (non-table) overlay config.
     * (Dynamic rows are handled separately because row count depends on HTML.)
     */
    private const LAYOUT = [
        'page' => 1,
        'defaults' => [
            'h' => 5.6,
            'style' => ['border' => 0],
            'opts' => ['da' => '/Helv 6.5 Tf 0 g'],
        ],
        'fields' => [
            // These get values from request via CoreDownload::applyLayout
            ['name' => 'reference',             'x' => 33.5, 'y' => 68.2,  'w' => 40.0, 'h' => 5.6],
            ['name' => 'place_input',           'x' => 38.0, 'y' => 244.0, 'w' => 48.0, 'h' => 5.6],
            ['name' => 'signed_by',             'x' => 109.0, 'y' => 244.0, 'w' => 70.0, 'h' => 5.6],
            ['name' => 'date_input',            'x' => 38.0, 'y' => 251.3, 'w' => 48.0, 'h' => 5.6],
            ['name' => 'on_behalf_of',          'x' => 113.0, 'y' => 251.3, 'w' => 67.0, 'h' => 5.6],

            // totals (no request mapping in old code, but still valid fields)
            ['name' => 'total_value',           'x' => 29.0, 'y' => 172.0, 'w' => 35.0, 'h' => 5.5],
            ['name' => 'total_oz',              'x' => 142.0, 'y' => 172.0, 'w' => 35.0, 'h' => 5.5],

            // custom request-mapped fields (names differ in request)
            // We'll set these manually in applyExtraFields() below:
            // collection_date, passport_number, company_input, holding_passport_number
        ],
    ];

    /**
     * Main entry point (call this from your controller).
     */
    public static function process($html, $recordModel, Vtiger_Request $request): void
    {
        global $root_directory;

        $fileName = self::buildFileName($recordModel, $request);

        $tmpDir = CoreDownload::getWritableTmpDir($root_directory);
        [$htmlPath, $basePdfPath, $finalPdfPath] = CoreDownload::buildPaths($tmpDir, $fileName);

        // HTML -> base PDF
        CoreDownload::writeFileOrFail($htmlPath, (string)$html);
        CoreDownload::runWkhtmltopdfOrFail($htmlPath, $basePdfPath, self::WKHTML_OPTS);
        @unlink($htmlPath);

        // Infer dynamic row count from HTML names: qty_1..qty_N
        $rowCount = self::inferRowCountFromHtml((string)$html, 30);

        // Import base PDF
        $pdf = CoreDownload::makeFpdi();
        CoreDownload::addPageFromBasePdf($pdf, $basePdfPath, (int)self::LAYOUT['page']);
        @unlink($basePdfPath);

        // Debug grid
        if ((string)$request->get('debug') === '1') {
            CoreDownload::drawDebugGrid($pdf);
        }

        // Apply static fields (reference/place/signed/date/etc + totals)
        CoreDownload::applyLayout($pdf, $request, self::LAYOUT);

        // Apply fields that map to different request keys
        self::applyExtraFields($pdf, $request);

        // Apply the dynamic table fields (qty_i, desc_i, serial_i, fine_oz_i)
        self::applyDynamicRows($pdf, $rowCount);

        // Apply checkboxes
        self::applyCheckboxes($pdf, $request);

        // Save + stream
        $pdf->Output($finalPdfPath, 'F');
        CoreDownload::streamPdfAndCleanup($finalPdfPath, $fileName . '.pdf');
    }

    /**
     * File name format from your old code:
     * <clientID>-CR-<year>-<docNoLastPart>-CR
     */
    private static function buildFileName($recordModel, Vtiger_Request $request): string
    {
        $clientID = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$recordModel->get('cf_898'));
        $year = date('Y');

        $docNoParts = explode('/', (string)$request->get('docNo'));
        $docNoLastPart = end($docNoParts) ?: 'NO-DOCNO';
        $docNoLastPart = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$docNoLastPart);

        $template = 'CR';

        if ($clientID === '') {
            $clientID = 'CLIENT';
        }

        return $clientID . '-' . $template . '-' . $year . '-' . $docNoLastPart . '-' . $template;
    }

    /**
     * Infer how many dynamic rows exist by inspecting HTML field names.
     * Looks for: name="qty_12" or name='qty_12'
     */
    private static function inferRowCountFromHtml(string $html, int $maxRows = 30): int
    {
        $rowCount = 0;

        if (preg_match_all('/name\s*=\s*["\']qty_(\d+)["\']/', $html, $m)) {
            $nums = array_map('intval', $m[1]);
            $rowCount = $nums ? max($nums) : 0;
        }

        return max(0, min($rowCount, $maxRows));
    }

    /**
     * Applies request-mapped fields where request param names differ
     * from PDF field names (kept exactly as your old code).
     */
    private static function applyExtraFields($pdf, Vtiger_Request $request): void
    {
        $fieldStyle = ['border' => 0];
        $h = 5.6;

        $dx = 0.0;
        $dy = 0.0;

        // These were set manually in old code (request keys are different)
        $pdf->SetXY(86.0 + $dx, 180.0 + $dy);
        $pdf->TextField('collection_date', 29.0, $h, $fieldStyle, ['v' => (string)$request->get('collectionDateInput')]);

        $pdf->SetXY(27.0 + $dx, 194.0 + $dy);
        $pdf->TextField('passport_number', 29.0, 5.0, $fieldStyle, ['v' => (string)$request->get('passportNumberInput')]);

        $pdf->SetXY(111.0 + $dx, 200.0 + $dy);
        $pdf->TextField('company_input', 70.0, $h, $fieldStyle, ['v' => (string)$request->get('companyInput')]);

        $pdf->SetXY(64.5 + $dx, 207.5 + $dy);
        $pdf->TextField('holding_passport_number', 29.0, 5.0, $fieldStyle, ['v' => (string)$request->get('holdingPassportInput')]);
    }

    /**
     * Creates the dynamic table rows (qty_i / desc_i / serial_i / fine_oz_i).
     * This is the “complex” part (kept same geometry as your old code).
     */
    private static function applyDynamicRows($pdf, int $rowCount): void
    {
        if ($rowCount <= 0) {
            return;
        }

        $fieldStyle = ['border' => 0];

        // insetX/insetY = inner padding INSIDE each PDF form field (mm)
        $insetX = 1.3;
        $insetY = 0.88;
        $fieldH = 5.0;
        $descH  = 9.6;

        // row positioning
        $startY  = 95.5;
        // $startY  = 89.5;
        $rowStep = 11.7;

        // table geometry
        $xTable = 27.5;
        $wTable = 155.0;

        // column ratios
        $ratioQty    = 0.055;
        $ratioDesc   = 0.65;
        $ratioSerial = 0.20;
        $ratioFine   = 0.10;

        // computed widths
        $wQty    = $wTable * $ratioQty;
        $wDesc   = $wTable * $ratioDesc;
        $wSerial = $wTable * $ratioSerial;
        $wFine   = $wTable * $ratioFine;

        // column start positions
        $xQty    = $xTable;
        $xDesc   = $xQty + $wQty;
        $xSerial = $xDesc + $wDesc;
        $xFine   = $xSerial + $wSerial;

        for ($i = 1; $i <= $rowCount; $i++) {
            $y = $startY + ($i - 1) * $rowStep;

            $pdf->SetXY($xQty + $insetX, $y + $insetY);
            $pdf->TextField("qty_$i", $wQty - 2 * $insetX, $fieldH, $fieldStyle);

            $pdf->SetXY($xDesc + $insetX, $y + $insetY);
            $pdf->TextField(
                "desc_$i",
                $wDesc - 2 * 0.3,
                $descH,
                $fieldStyle + [
                    'multiline' => true,
                    'linebreak' => true,
                    'padding'   => 0,
                    'style'     => [
                        'margin' => [0, 0, 0, 0],
                        'padding' => [0, 0, 0, 0],
                    ],
                ]
            );

            $pdf->SetXY($xSerial + $insetX, $y + $insetY);
            $pdf->TextField("serial_$i", $wSerial - 2 * $insetX, $fieldH, $fieldStyle);

            $pdf->SetXY($xFine + $insetX, $y + $insetY);
            $pdf->TextField("fine_oz_$i", $wFine - 2 * $insetX, $fieldH, $fieldStyle);
        }
    }

    /**
     * Checkboxes: company_checked + id_option_checked
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

        $companyChecked = (string)$request->get('companyName') === '1';
        $idOptionChecked = (string)$request->get('idOption') === '1';

        $makeCheckbox('company_checked',   27.6, 189.5, $companyChecked);
        $makeCheckbox('id_option_checked', 27.6, 201.3, $idOptionChecked);
    }
}
