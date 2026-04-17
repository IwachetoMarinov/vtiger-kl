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
            ['name' => 'reference',             'x' => 23.5, 'y' => 67.2,  'w' => 40.0, 'h' => 5.6],
            ['name' => 'place_input',           'x' => 25.0, 'y' => 260.0, 'w' => 58.0, 'h' => 5.6],
            ['name' => 'signed_by',             'x' => 109.0, 'y' => 260.0, 'w' => 80.0, 'h' => 5.6],
            ['name' => 'date_input',            'x' => 25.0, 'y' => 267.3, 'w' => 58.0, 'h' => 5.6],
            ['name' => 'on_behalf_of',          'x' => 118.0, 'y' => 267.3, 'w' => 77.0, 'h' => 5.6],

            // totals (no request mapping in old code, but still valid fields)
            ['name' => 'total_value',           'x' => 22.0, 'y' => 179.5, 'w' => 35.0, 'h' => 5.5],
            ['name' => 'total_oz',              'x' => 147.0, 'y' => 179.5, 'w' => 35.0, 'h' => 5.5],

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
        [, $basePdfPath, $finalPdfPath] = CoreDownload::buildPaths($tmpDir, $fileName);

        // IMPORTANT: write HTML in vtiger root so relative assets resolve (old behavior)
        $htmlPath = rtrim($root_directory, "/\\") . DIRECTORY_SEPARATOR . $fileName . '.html';

        CoreDownload::writeFileOrFail($htmlPath, (string)$html);
        // CoreDownload::runWkhtmltopdfOrFail($htmlPath, $basePdfPath, self::WKHTML_OPTS);
        CoreDownload::runChromePdfOrFail($htmlPath, $basePdfPath);

        @unlink($htmlPath);

        // $tmpDir = CoreDownload::getWritableTmpDir($root_directory);
        // [$htmlPath, $basePdfPath, $finalPdfPath] = CoreDownload::buildPaths($tmpDir, $fileName);

        // HTML -> base PDF
        // CoreDownload::writeFileOrFail($htmlPath, (string)$html);
        // CoreDownload::runWkhtmltopdfOrFail($htmlPath, $basePdfPath, self::WKHTML_OPTS);

        // Infer dynamic row count from HTML names: qty_1..qty_N
        $rowCount = self::inferRowCountFromHtml((string)$html, 30);

        // Import base PDF
        $pdf = CoreDownload::makeFpdi();
        CoreDownload::addPageFromBasePdf($pdf, $basePdfPath, (int)self::LAYOUT['page']);
        @unlink($basePdfPath);

        // Debug grid
        if ((string)$request->get('debug') === '1') CoreDownload::drawDebugGrid($pdf);

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
        $pdf->SetXY(81.0 + $dx, 189.0 + $dy);
        $pdf->TextField('collection_date', 39.0, $h, $fieldStyle, ['v' => (string)$request->get('collectionDateInput')]);

        $pdf->SetXY(11.0 + $dx, 205.0 + $dy);
        $pdf->TextField('passport_number', 34.0, 5.0, $fieldStyle, ['v' => (string)$request->get('passportNumberInput')]);

        $pdf->SetXY(112.0 + $dx, 212.0 + $dy);
        $pdf->TextField('company_input', 80.0, $h, $fieldStyle, ['v' => (string)$request->get('companyInput')]);

        $pdf->SetXY(52.0 + $dx, 219.8 + $dy);
        $pdf->TextField('holding_passport_number', 39.0, 5.0, $fieldStyle, ['v' => (string)$request->get('holdingPassportInput')]);
    }

    /**
     * Creates the dynamic table rows (qty_i / desc_i / serial_i / fine_oz_i).
     * This is the “complex” part (kept same geometry as your old code).
     */
    private static function applyDynamicRows($pdf, int $rowCount): void
    {
        if ($rowCount <= 0) ;

        $fieldStyle = ['border' => 0];

        // heights
        $fieldH = 5.0;   // normal inputs
        $descH  = 9.4;   // higher description only

        // row positioning
        $startY  = 90.25;
        $rowStep = 13.05;

        // table geometry
        $xTable = 9.3;
        $wTable = 189.5;

        // adjusted widths
        $ratioQty    = 0.050;
        $ratioDesc   = 0.665;
        $ratioSerial = 0.175;
        $ratioFine   = 0.110;

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

            // QTY
            $pdf->SetXY($xQty + 0.55, $y + 0.7);
            $pdf->TextField(
                "qty_$i",
                $wQty - 1.0,
                $fieldH,
                $fieldStyle
            );

            // DESCRIPTION
            $pdf->SetXY($xDesc + 0.55, $y + 0.35);
            $pdf->TextField(
                "desc_$i",
                $wDesc - 1.1,
                $descH,
                $fieldStyle + [
                    'multiline' => true,
                    'linebreak' => true,
                    'padding'   => 0,
                    'style'     => [
                        'margin'  => [0, 0, 0, 0],
                        'padding' => [0, 0, 0, 0],
                    ],
                ]
            );

            // SERIAL NUMBERS
            $pdf->SetXY($xSerial + 0.30, $y + 0.7);
            $pdf->TextField(
                "serial_$i",
                $wSerial - 0.6,
                $fieldH,
                $fieldStyle
            );

            // FINE OZ.
            $pdf->SetXY($xFine + 0.18, $y + 0.7);
            $pdf->TextField(
                "fine_oz_$i",
                $wFine - 0.45,
                $fieldH,
                $fieldStyle
            );
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

        $makeCheckbox('company_checked',   7.0, 192.5, $companyChecked);
        $makeCheckbox('id_option_checked', 8.6, 201.3, $idOptionChecked);
    }
}
