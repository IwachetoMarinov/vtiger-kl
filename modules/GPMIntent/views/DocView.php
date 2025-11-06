<?php

class GPMIntent_DocView_View extends Vtiger_Index_View
{

    /** @var Vtiger_DetailView_Model */
    protected $record;

    public function preProcess(Vtiger_Request $request, $display = false)
    {
        parent::preProcess($request, $display); // 🔥 ensures standard behavior

        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        // Getting model to reuse it in parent
        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
        }
    }


    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;

        $fileName = md5(rand());

        // 1) write HTML exactly as before
        $htmlPath = $root_directory . $fileName . '.html';
        $pdfPath  = $root_directory . $fileName . '.pdf';

        $handle = fopen($htmlPath, 'a') or die('Cannot open file:  ');
        fwrite($handle, $html);
        fclose($handle);

        // 2) QUOTE the paths + capture output/status
        $in  = escapeshellarg($htmlPath);
        $out = escapeshellarg($pdfPath);
        $cmd = "wkhtmltopdf --enable-local-file-access -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking $in $out 2>&1";

        $output = [];
        $status = 0;
        exec($cmd, $output, $status);

        // 3) If wkhtmltopdf failed, log why (don’t change the rest yet)
        if ($status !== 0) {
            error_log("wkhtmltopdf failed (status $status):\n" . implode("\n", $output));
        }

        unlink($htmlPath);

        // 4) serve the PDF exactly as you did
        header("Content-type: application/pdf");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=$fileName.pdf");
        header("Content-Description: Global Precious Metals CRM Data");
        ob_clean();
        flush();
        readfile($pdfPath);
        unlink($pdfPath);
        exit;
    }


    function downloadPDF2($html, Vtiger_Request $request)
    {
        global $root_directory;


        $fileName = md5(rand());
        $handle = fopen($root_directory . $fileName . '.html', 'a') or die('Cannot open file:  ');
        fwrite($handle, $html);
        fclose($handle);

        exec("wkhtmltopdf --enable-local-file-access  -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking " . $root_directory . "$fileName.html " . $root_directory . "$fileName.pdf");
        unlink($root_directory . $fileName . '.html');

        header("Content-type: application/pdf");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=$fileName.pdf");
        header("Content-Description: Global Precious Metals CRM Data");
        ob_clean();
        flush();
        readfile($root_directory . "$fileName.pdf");
        unlink($root_directory . "$fileName.pdf");
        exit;
    }
}
