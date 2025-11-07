<?php

class Contacts_ActivtySummeryPrintPreview_View extends Vtiger_Index_View
{

    public function preProcess(Vtiger_Request $request, $display = false)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        // Getting model to reuse it in parent
        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
        }
    }

    function fetchOROSOft(Vtiger_Request $request)
    {
        include_once 'modules/Settings/OROSoft/api.php';
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_950');
        $year = $request->get('ActivtySummeryDate');
        $comId = $recordModel->get('related_entity');
        return array(
            'Transactions' => getOROSoftTransaction($clientID, $year, $comId)
        );
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $oroSOftData = $this->fetchOROSOft($request);
        $recordModel = $this->record->getRecord();
        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('PAGES', $this->makeDataPage($oroSOftData['Transactions']));
        $viewer->assign('OROSOFT_TRANSACTION', $oroSOftData['Transactions']);
        $viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($recordModel->get('related_entity')));
        if ($request->get('PDFDownload')) {
            $viewer->assign('ENABLE_DOWNLOAD_BUTTON', false);
            $html = $viewer->view('ActivtySummeryPrintPreview.tpl', $moduleName, true);
            $this->downloadPDF($html);
        } else {
            $viewer->assign('ENABLE_DOWNLOAD_BUTTON', true);
            $viewer->view('ActivtySummeryPrintPreview.tpl', $moduleName);
        }
    }

    function makeDataPage($transaction)
    {
        $totalPage = 1;
        if (count($transaction) > 25) {
            $totaldataAfterFirstPage = count($transaction) - 25;
            $totalPage = ceil($totaldataAfterFirstPage / 30) + 1;
        }
        return $totalPage;
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_950');

        $fileName = $clientID . "_activity-summary";
        $handle = fopen($root_directory . $fileName . '.html', 'a') or die('Cannot open file:  ');
        fwrite($handle, $html);
        fclose($handle);

        exec("wkhtmltopdf --enable-local-file-access  -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking " . $root_directory . "$fileName.html " . $root_directory . "$fileName.pdf");
        unlink($root_directory . $fileName . '.html');

        header("Content-type: application/pdf");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=" . $clientID . " - AS as of " . date('d M Y') . ".pdf");
        header("Content-Description: PHP Generated Data");
        ob_clean();
        flush();
        readfile($root_directory . "$fileName.pdf");
        unlink($root_directory . "$fileName.pdf");
        exit;
    }
}

#First Page 18
#from Second page onwards 25 
