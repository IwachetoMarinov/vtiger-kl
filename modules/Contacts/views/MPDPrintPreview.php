<?php

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/HoldingsDB.php';

class Contacts_MPDPrintPreview_View extends Vtiger_Index_View
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

    public function process(Vtiger_Request $request)
    {

        $docNo = $request->get('docNo');
        $docType = substr($docNo, 0, 3);
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();
        $comId = $recordModel->get('related_entity');
        $tableName = $request->get('tableName');

        // $accountId = $recordModel->get('account_id');
        $activity = new dbo_db\ActivitySummary();
        // $activity_data = $activity->getTCPrintPreviewData($docNo, $tableName);
        $activity_data = $activity->getDocumentPrintPreviewData($docNo, $tableName);

        echo '<pre>';
        var_dump($activity_data);
        echo '</pre>';

        // OROSOFT DOCUMENT (CUSTOM OBJECT)
        $oroDoc = [
            'documentDate' => '2025-01-20',
            'deliveryDate' => '2025-01-21',
            'barItems' => [
                [
                    'quantity' => 2,
                    'longDesc' => 'Gold Bar 100g',
                    'serials' => ['G100-44521', 'G100-44522'],
                    'pureOz' => 3.215,
                    'price' => 2100,
                    'location' => 'DUBAI'
                ],
                [
                    'quantity' => 1,
                    'longDesc' => 'Gold Bar 50g',
                    'serials' => ['G50-78451'],
                    'pureOz' => 1.607,
                    'price' => 1050,
                    'location' => 'DUBAI'
                ]
            ]
        ];

        // echo '<pre>';
        // print_r($activity_data);
        // echo '</pre>';


        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('PAGES', 1);
        $viewer->assign('HIDE_BP_INFO', false);
        $viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($comId));
        $viewer->assign('ERP_DOCUMENT', $oroDoc);

        // REQUEST VALUES PASSED BY CONTROLLER
        $viewer->assign('DOCNO', $request->get('docNo'));
        $viewer->assign('PDFDownload', $request->get('PDFDownload'));
        $viewer->assign('hideCustomerInfo', $request->get('hideCustomerInfo'));


        if ($request->get('PDFDownload')) {
            $html = $viewer->view("MPD.tpl", $moduleName, true);
            $this->downloadPDF($html, $request);
        } else {
            $viewer->view("MPD.tpl", $moduleName);
        }
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');

        $fileName = $clientID . '-' . str_replace('/', '-', $request->get('docNo')) . "-TC";
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
