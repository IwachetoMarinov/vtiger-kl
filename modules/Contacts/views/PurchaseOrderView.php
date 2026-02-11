<?php

class Contacts_PurchaseOrderView_View extends Vtiger_Index_View
{

    protected $record = null;

    public function preProcess(Vtiger_Request $request, $display = false)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        if (!$this->record) $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();
        $client_type = $recordModel->get('cf_927');
        $pricingOption = $request->get('pricing_option');

        $allBankAccounts = [];

        $companyRecord = Contacts_DefaultCompany_View::process();

        $allBankAccounts = BankAccount_Record_Model::getAllInstances();
        $bankAccountId   = $request->get('bank');

        $bankAccountId = $request->get('bank');
        if (empty($bankAccountId) && !empty($allBankAccounts)) {
            $firstAccount  = reset($allBankAccounts);
            $bankAccountId = $firstAccount->getId();
        }

        // ✅ Handle no bank accounts gracefully
        if (empty($bankAccountId)) $bankAccountId = null;

        $selectedBank = null;
        if (!empty($bankAccountId)) $selectedBank = BankAccount_Record_Model::getInstanceById($bankAccountId);

        if (empty($selectedBank)) {
            // fallback dummy object to prevent template fatal
            $selectedBank = new Vtiger_Record_Model();
            $selectedBank->set('beneficiary_name', '');
            $selectedBank->set('account_no', '');
            $selectedBank->set('account_currency', '');
            $selectedBank->set('iban_no', '');
            $selectedBank->set('bank_name', '');
            $selectedBank->set('bank_address', '');
            $selectedBank->set('swift_code', '');
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('PRICING_OPTION', $pricingOption);
        $viewer->assign('CLIENT_TYPE', $client_type);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('PAGES', 1);
        $viewer->assign('ALL_BANK_ACCOUNTS', $allBankAccounts);
        $viewer->assign('SELECTED_BANK', $selectedBank ?? null);
        $viewer->assign('HIDE_BP_INFO', false);
        $viewer->assign('COMPANY', $companyRecord);
        $viewer->assign('DOCNO', $request->get('docNo'));
        $viewer->assign('PDFDownload', $request->get('PDFDownload'));
        $viewer->assign('hideCustomerInfo', $request->get('hideCustomerInfo'));
        $viewer->assign('COUNTRY_OPTION', $request->get('countryOption') ?? null);
        $viewer->assign('ADDRESS_OPTION', $request->get('addressOption') ?? null);

        if ($request->get('PDFDownload')) {
            $html = $viewer->view("PO.tpl", $moduleName, true);
            $this->downloadPDF($html, $request);
        } else {
            $viewer->view("PO.tpl", $moduleName);
        }
    }

    public function postProcess(Vtiger_Request $request) {}

    // function downloadPDF($html, Vtiger_Request $request)
    // {
    //     global $root_directory;
    //     $recordModel = $this->record->getRecord();
    //     $clientID = $recordModel->get('cf_898');
    //     $year  = date('Y');

    //     $fileName = $clientID . '-' . $year . "-PO";
    //     $handle = fopen($root_directory . $fileName . '.html', 'a') or die('Cannot open file:  ');
    //     fwrite($handle, $html);
    //     fclose($handle);

    //     exec("wkhtmltopdf --enable-local-file-access  -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking " . $root_directory . "$fileName.html " . $root_directory . "$fileName.pdf");
    //     unlink($root_directory . $fileName . '.html');

    //     header("Content-type: application/pdf");
    //     header("Cache-Control: private");
    //     header("Content-Disposition: attachment; filename=$fileName.pdf");
    //     header("Content-Description: Global Precious Metals CRM Data");
    //     ob_clean();
    //     flush();
    //     readfile($root_directory . "$fileName.pdf");
    //     unlink($root_directory . "$fileName.pdf");
    //     exit;
    // }

    protected function getAssets()
    {
        $moduleName = 'Assets';
        $currentUser = Users_Record_Model::getCurrentUserModel();

        // Load module and fields
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $fields = $moduleModel->getFields();

        // Build list of all fieldnames dynamically
        $fieldNames = [];
        foreach ($fields as $fieldModel) {
            $fieldName = $fieldModel->getName();
            $fieldNames[] = $fieldName;
        }

        // QueryGenerator to fetch ALL these fields
        $queryGenerator = new QueryGenerator($moduleName, $currentUser);
        $queryGenerator->setFields($fieldNames);

        $query = $queryGenerator->getQuery();

        $db = PearDatabase::getInstance();
        $result = $db->pquery($query, []);

        $assets = [];
        while ($row = $db->fetchByAssoc($result)) {
            $assets[] = $row;
        }

        return $assets;
    }

    protected function processAssetsData($assets)
    {
        $data = [];

        foreach ($assets as $asset) {
            $metal_type = $asset['cf_873'];

            if (isset($data[$metal_type])) {
                // Append to existing metal type
                $data[$metal_type][] = $asset;
            } else {
                $data[$metal_type] = [$asset];
            }
        }

        return $data;
    }


    /**
     * HTML -> PDF via wkhtmltopdf, then overlay ONE PDF form field (serial_numbers),
     * and download.
     *
     * Query param used:
     *   serial_numbers
     *
     * Use &debug=1 to draw a grid and adjust coordinates.
     */
    protected function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;

        $recordModel = $this->record->getRecord();

        // ---- Safe filename ----
        $clientID = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$recordModel->get('cf_898'));
        $year = date('Y');
        $fileName = $clientID . '-' . $year . "-PO";

        // ---- Writable temp dir ----
        $tmpDir = rtrim((string)$root_directory, '/\\') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR;
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        if (!is_writable($tmpDir)) {
            $tmpDir = rtrim((string)sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'vtiger_pdf' . DIRECTORY_SEPARATOR;
            if (!is_dir($tmpDir)) {
                @mkdir($tmpDir, 0775, true);
            }
        }
        if (!is_dir($tmpDir) || !is_writable($tmpDir)) {
            header("HTTP/1.1 500 Internal Server Error");
            die('Temp directory not writable: ' . $tmpDir);
        }

        $htmlPath     = $tmpDir . $fileName . '.html';
        $basePdfPath  = $tmpDir . $fileName . '_base.pdf';
        $finalPdfPath = $tmpDir . $fileName . '.pdf';

        // ---- Write HTML ----
        if (@file_put_contents($htmlPath, $html) === false) {
            header("HTTP/1.1 500 Internal Server Error");
            die('Cannot write HTML file: ' . $htmlPath);
        }

        // ---- HTML -> PDF ----
        $cmd = "wkhtmltopdf --enable-local-file-access "
            . "--page-size A4 --dpi 96 --zoom 1 "
            . "--margin-top 0 --margin-right 0 --margin-bottom 0 --margin-left 0 "
            . "--disable-smart-shrinking "
            . escapeshellarg($htmlPath) . " " . escapeshellarg($basePdfPath) . " 2>&1";

        $out = [];
        $code = 0;
        exec($cmd, $out, $code);
        @unlink($htmlPath);

        if ($code !== 0 || !file_exists($basePdfPath) || filesize($basePdfPath) < 2000) {
            header("HTTP/1.1 500 Internal Server Error");
            die("wkhtmltopdf failed (exit=$code):\n" . implode("\n", $out));
        }

        // ---- Import base PDF and overlay ONE field ----
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        // Ensure TCPDF sets up font resources for AcroForm
        $pdf->SetFont('helvetica', '', 6.5);

        // Set global default form appearance (creates /F1 in /AcroForm /DR)
        $pdf->setFormDefaultProp([
            'font' => 'helvetica',
            'fontsize' => 6.8,
            'textcolor' => [0, 0, 0],
        ]);

        $pageCount = $pdf->setSourceFile($basePdfPath);

        // page 1 only (your screenshot section is page 1)
        $tplId = $pdf->importPage(1);
        $size  = $pdf->getTemplateSize($tplId);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);

        // ---- DEBUG GRID MODE (call with &debug=1) ----
        $debug = (string)$request->get('debug') === '1';
        if ($debug) {
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

            // Optional: minor grid every 5mm (lighter)
            for ($x = 0; $x <= $pageW; $x += 5) {
                $pdf->Line($x, 0, $x, $pageH, ['width' => 0.05, 'color' => [220, 220, 220]]);
            }
            for ($y = 0; $y <= $pageH; $y += 5) {
                $pdf->Line(0, $y, $pageW, $y, ['width' => 0.05, 'color' => [220, 220, 220]]);
            }
        }

        // ---- Field appearance ----
        $fieldStyle = ['border' => 0];

        // IMPORTANT: use /F1 in DA
        $defaultFieldOptsBase = ['da' => '/F1 6.8 Tf 0 g',];

        // ---- ONLY ONE INPUT: serial_numbers ----
        $x = 18.0;
        $y = 63.0;
        $h = 5.5;

        // currency input  field
        $pdf->SetXY(60, 138.0);
        $pdf->TextField(
            'currency',
            38.5,
            $h,
            $fieldStyle,
            array_merge(
                $defaultFieldOptsBase,
                ['v' => (string)$request->get('currency'), 'da' => '/Helv 6.5 Tf 0 g']
            ),

        );

        // location input field
        $pdf->SetXY(102, 152.5);
        $pdf->TextField(
            'location',
            40,
            $h,
            $fieldStyle,
            array_merge(
                $defaultFieldOptsBase,
                ['v' => (string)$request->get('location'), 'da' => '/Helv 6.5 Tf 0 g']
            )
        );

        // address input field
        $pdf->SetXY(72, 159.3);
        $pdf->TextField(
            'address',
            55,
            $h,
            $fieldStyle,
            array_merge(
                $defaultFieldOptsBase,
                ['v' => (string)$request->get('address'), 'da' => '/Helv 6.5 Tf 0 g']
            )
        );

        // country input field
        $pdf->SetXY(49, 175.5);
        $pdf->TextField(
            'country',
            45,
            $h,
            $fieldStyle,
            array_merge(
                $defaultFieldOptsBase,
                ['v' => (string)$request->get('country'), 'da' => '/Helv 6.5 Tf 0 g']
            )
        );

        // place_input input
        $pdf->SetXY(41, 253.5);
        $pdf->TextField(
            'place_input',
            45,
            $h,
            $fieldStyle,
            array_merge(
                $defaultFieldOptsBase,
                ['v' => (string)$request->get('place_input'), 'da' => '/Helv 6.5 Tf 0 g']
            )
        );

        // signed_by input
        $pdf->SetXY(109, 253.5);
        $pdf->TextField(
            'signed_by',
            65,
            $h,
            $fieldStyle,
            array_merge(
                $defaultFieldOptsBase,
                ['v' => (string)$request->get('signed_by'), 'da' => '/Helv 6.5 Tf 0 g']
            )
        );

        // date_input input
        $pdf->SetXY(41, 262.0);
        $pdf->TextField(
            'date_input',
            45,
            $h,
            $fieldStyle,
            array_merge(
                $defaultFieldOptsBase,
                ['v' => (string)$request->get('date_input'), 'da' => '/Helv 6.5 Tf 0 g']
            )
        );

        $pdf->SetXY(112, 262.0);
        $pdf->TextField(
            'on_behalf_of',
            62,
            $h,
            $fieldStyle,
            array_merge($defaultFieldOptsBase, [
                'v'  => (string)$request->get('on_behalf_of'),
            ])
        );

        // ---- METALS TABLE CONFIG (ADJUSTED) ----
        $startX = 57.3;   // was ~48.0
        $startY = 111.0;  // was ~116.0
        $cellW  = 13.57;   // was ~15
        $cellH  = 6.5;    // was ~7

        $metalCount  = 4;   // Gold, Silver, Platinum, Palladium
        $weightCount = 9;   // 1000oz ... Other

        $offsetX = 0.6;   // move inside cell (right)
        $offsetY = 0.5;   // move inside cell (down)

        $fieldW  = $cellW - 1.2; // leave padding both sides
        $fieldH  = $cellH - 1.2;

        $fieldStyle = [
            'border' => 0,
        ];

        $fieldOptsBase = [
            'da' => '/Helv 5.5 Tf 0 g',   // smaller font
        ];

        // ---- METALS TABLE FIELDS ----
        for ($mi = 0; $mi < $metalCount; $mi++) {
            for ($wi = 0; $wi < $weightCount; $wi++) {

                $fieldName = "metal_{$mi}_weight_{$wi}";
                $value     = (string)($request->get($fieldName) ?? '');

                $x = $startX + ($wi * $cellW) + $offsetX;
                $y = $startY + ($mi * $cellH) + $offsetY;

                $pdf->SetXY($x, $y);
                $pdf->TextField(
                    $fieldName,
                    $fieldW,
                    $fieldH,
                    $fieldStyle,
                    array_merge($fieldOptsBase, [
                        'v' => $value, // blank allowed
                    ])
                );
            }
        }

        // Checkboxes

        $makeCheckbox = function ($name, $x, $y, $checked) use ($pdf) {
            $size = 3.4; // slightly larger improves centering visually

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
                    // IMPORTANT for consistent rendering
                    'v'  => $checked ? 'Yes' : 'Off',
                    'dv' => 'Off',

                    // This helps many viewers render a proper centered ✓
                    // ZapfDingbats check mark (AcroForm standard)
                    'da' => '/ZaDb 10 Tf 0 g',
                ]
            );
        };

        $first_pricing_checked = (string)$request->get('pricing_option') === '1';
        $second_pricing_checked = (string)$request->get('pricing_option') === '2';
        $country_checked = (string)$request->get('countryOption') === '1';
        $address_checked = (string)$request->get('addressOption') === '1';

        $makeCheckbox('country_checked',  35, 154.5, $country_checked);
        $makeCheckbox('address_checked',  35, 161.3, $address_checked);

        $makeCheckbox('pricing_option_1', 34, 226.0, $first_pricing_checked);
        $makeCheckbox('pricing_option_2', 34, 233.5, $second_pricing_checked);

        // ---- Save final --
        $pdf->Output($finalPdfPath, 'F');
        @unlink($basePdfPath);

        // ---- Stream to browser ----
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Cache-Control: private');
        header('Content-Disposition: attachment; filename="' . $fileName . '.pdf"');
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
