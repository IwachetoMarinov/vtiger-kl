<?php


class Contacts_TCPrintPreview_View extends Vtiger_Index_View
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
        // include_once 'modules/Settings/OROSoft/api.php';
        include_once 'modules/Contacts/models/MetalsAPI.php';
        $docNo = $request->get('docNo');
        $docType = substr($docNo, 0, 3);
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();
        $comId = $recordModel->get('related_entity');
        // $oroSOftDoc = ($docType == 'STI') ? getOROSoftSTIDoc($docNo, $comId) : getOROSoftDoc($docNo, $comId);

        $metalsAPI = new MetalsAPI();
        $metals = $metalsAPI->getMetals();

        // ------------------------------------------------------
        // FINAL, FULLY COMPATIBLE OFFLINE OROSOFT MOCK FOR TC.PHP
        // ------------------------------------------------------
        $oroSOftDoc = (object) [
            'docNo' => $docNo,
            'docType' => $docType,
            'documentDate' => '2024-01-15',
            'postingDate' => '2024-01-15',
            'voucherType' => 'SAL',
            'companyName' => 'Global Precious Metals',
            'currency' => 'USD',
            'grandTotal' => 80000.00,
            'totalusdVal' => 80000.00,

            // REQUIRED BY TEMPLATE (missing in your version)
            'balanceAmount' => 0,
            'value' => 0,

            // THIS IS CRITICAL: template iterates beyond length
            // so we add 30 items (enough for page count)
            'barItems' => []
        ];

        // ------------------------------------------
        // Populate barItems with 30 fully valid items
        // ------------------------------------------
        for ($i = 1; $i <= 10; $i++) {
            $oroSOftDoc->barItems[] = (object)[
                'quantity' => ($i % 3) + 1,
                'serials' => ["SERIAL-$i-A", "SERIAL-$i-B"],
                'itemCode' => 'GOLD999',
                'longDesc' => "Gold Bar $i (999.9 Fine)",
                'weight' => 1000,
                'pureOz' => 32.151,
                'price' => 2020.25,
                'otherCharge' => 500.00,
                'unitPrice' => 62000.00,
                'amount' => 62000.00,
                'usdVal' => 62000.00,
                'barNumber' => "B$i-GPM",
                'purity' => '999.9',
                'voucherType' => 'SAL',
                'value' => 62000.00,
            ];
        }


        // echo '<pre>';
        // echo($oroSOftDoc);
        // echo '</pre>';
        $recordModel = $this->record->getRecord();
        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('OROSOFT_DOCUMENT', $oroSOftDoc);
        $viewer->assign('METALS_DATA', $metals);
        $viewer->assign('HIDE_BP_INFO', $request->get('hideCustomerInfo'));
        $viewer->assign('OROSOFT_DOCTYPE', $docType);
        $viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($comId));
        $viewer->assign('PAGES', $this->makeDataPage($oroSOftDoc->barItems, $docType));
        if ($request->get('PDFDownload')) {
            $html = $viewer->view("TC.tpl", $moduleName, true);
            $this->downloadPDF($html, $request);
        } else {
            $viewer->view("TC.tpl", $moduleName);
        }
    }

    function makeDataPage($transaction, $docType)
    {
        $totalPage = 1;
        $recordCount = 26;
        if (count($transaction) > $recordCount) {
            $totaldataAfterFirstPage = count($transaction) - $recordCount;
            $totalPage = ceil($totaldataAfterFirstPage / $recordCount) + 1;
        }
        return $totalPage;
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_950');

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
