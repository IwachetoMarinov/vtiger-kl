<?php

include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/HoldingsDB.php';

class Contacts_StockTransferOrderView_View extends Vtiger_Index_View
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
        $country_type = $request->get('countryOption');
        $custom_country = $request->get('customCountry');
        $client_type = $recordModel->get('cf_927');
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
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('PAGES', 1);
        $viewer->assign('HIDE_BP_INFO', false);
        $viewer->assign('COMPANY', $companyRecord);
        $viewer->assign('CLIENT_TYPE', $client_type);
        $viewer->assign('COUNTRY_OPTION', $country_type ?? null);
        $viewer->assign('CUSTOM_COUNTRY', $custom_country ?? "");
        $viewer->assign('ALL_BANK_ACCOUNTS', $allBankAccounts);
        $viewer->assign('SELECTED_BANK', $selectedBank ?? null);
        $viewer->assign('DOCNO', $request->get('docNo'));
        $viewer->assign('PDFDownload', $request->get('PDFDownload'));
        $viewer->assign('hideCustomerInfo', $request->get('hideCustomerInfo'));

        if ($request->get('PDFDownload')) {
            $html = $viewer->view("STO.tpl", $moduleName, true);
            $this->downloadPDF($html, $request);
        } else {
            $viewer->view("STO.tpl", $moduleName);
        }
    }

    public function postProcess(Vtiger_Request $request) {}

    // function downloadPDF($html, Vtiger_Request $request)
    // {
    //     global $root_directory;
    //     $recordModel = $this->record->getRecord();
    //     $clientID = $recordModel->get('cf_898');
    //     $year  = date('Y');

    //     $fileName = $clientID . '-' . $year . "-STO";
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
        $fileName = $clientID . '-' . $year . "-STO";

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
        $fieldStyle = [
            'border'    => 0,
            'font'      => 'helvetica',
            'fontsize'  => 8,
            'textcolor' => [0, 0, 0],
        ];

        // ---- ONLY ONE INPUT: serial_numbers ----
        $x = 18.0;
        $y = 63.0;
        $h = 5.5;
        $full_width = 145.0;

        // description input  field
        $pdf->SetXY(32, 127.5);
        $pdf->TextField(
            'description',
            $full_width,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('description')]
        );

        // from_location input field
        $pdf->SetXY(32, 142.0);
        $pdf->TextField(
            'from_location',
            $full_width,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('from_location')]
        );

        // to_location input field
        $pdf->SetXY(32, 154.0);
        $pdf->TextField(
            'to_location',
            $full_width,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('to_location')]
        );

        // country input field
        $pdf->SetXY(47, 189.0);
        $pdf->TextField(
            'country',
            41,
            $h,
            $fieldStyle,
            ['v' => (string)$request->get('country')]
        );

        // ---- Save final ----
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
