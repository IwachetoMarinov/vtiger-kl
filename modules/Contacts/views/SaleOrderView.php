<?php

class Contacts_SaleOrderView_View extends Vtiger_Index_View
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

        $companyRecord = Contacts_DefaultCompany_View::process();

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('PAGES', 1);
        $viewer->assign('HIDE_BP_INFO', false);
        $viewer->assign('COMPANY', $companyRecord);
        $viewer->assign('CLIENT_TYPE', $client_type);
        $viewer->assign('DOCNO', $request->get('docNo'));
        $viewer->assign('PDFDownload', $request->get('PDFDownload'));
        $viewer->assign('CLIENT_NAME', $request->get('clientName') ?? '');
        $viewer->assign('hideCustomerInfo', $request->get('hideCustomerInfo'));

        if ($request->get('PDFDownload')) {
            $html = $viewer->view("SO.tpl", $moduleName, true);
            $this->downloadPDF($html, $request);
        } else {
            $viewer->view("SO.tpl", $moduleName);
        }
    }

    public function postProcess(Vtiger_Request $request) {}

    protected function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;

        // --- Build safe paths ---
        $recordModel = $this->record->getRecord();
        $clientID = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$recordModel->get('cf_898'));
        $year = date('Y');

        $fileName = $clientID . '-' . $year . '-SO';

        // Ensure root_directory ends with /
        $dir = rtrim($root_directory, '/\\') . DIRECTORY_SEPARATOR;

        // IMPORTANT: better to store temp files under cache/ not the project root
        // If you want root folder anyway, keep $dir as is.
        $tmpDir = $dir . 'cache' . DIRECTORY_SEPARATOR;
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $htmlPath = $tmpDir . $fileName . '.html';
        $pdfPath  = $tmpDir . $fileName . '.pdf';

        // --- Write HTML ---
        $bytes = @file_put_contents($htmlPath, $html);
        if ($bytes === false) {
            header("HTTP/1.1 500 Internal Server Error");
            die("Cannot write HTML file: {$htmlPath}");
        }

        // --- Generate PDF (forms enabled) ---
        // NOTE: --enable-forms only works if your HTML inputs have NAME attributes.
        // e.g. <input type="text" name="serials" ...>
        $cmd = sprintf(
            'wkhtmltopdf --enable-local-file-access --enable-forms --print-media-type -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking %s %s 2>&1',
            escapeshellarg($htmlPath),
            escapeshellarg($pdfPath)
        );

        $output = shell_exec($cmd);

        // Remove html always
        @unlink($htmlPath);

        // --- Validate PDF creation ---
        if (!file_exists($pdfPath) || filesize($pdfPath) < 5000) {
            header("HTTP/1.1 500 Internal Server Error");
            $msg = "wkhtmltopdf failed.\n\nCMD:\n{$cmd}\n\nOUTPUT:\n{$output}\n";
            die(nl2br(htmlspecialchars($msg, ENT_QUOTES, 'UTF-8')));
        }

        // --- Stream PDF to browser ---
        // Clean any output buffer before headers
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Cache-Control: private');
        header('Content-Disposition: attachment; filename="' . $fileName . '.pdf"');
        header('Content-Length: ' . filesize($pdfPath));

        // Stream
        $fp = fopen($pdfPath, 'rb');
        if ($fp === false) {
            header("HTTP/1.1 500 Internal Server Error");
            die("Cannot open PDF for reading: {$pdfPath}");
        }

        fpassthru($fp);
        fclose($fp);

        // Cleanup
        @unlink($pdfPath);
        exit;
    }
}
