<?php

include_once 'dbo_db/HoldingsDB.php';

class Contacts_HoldingPrintPreview_View extends Vtiger_Index_View
{

    protected $record = null;

    public function preProcess(Vtiger_Request $request, $display = false)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        // Getting model to reuse it in parent
        if (!$this->record) $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();
        $companyId = $recordModel->get('company_id');

        $companyRecord = null;

        if (!empty($companyId))
            $companyRecord = Vtiger_Record_Model::getInstanceById($companyId, 'GPMCompany');

        // REAL CUSTOMER ID FROM RECORD
        $clientID = $recordModel->get('cf_898');

        $holdings = new dbo_db\HoldingsDB();
        $holdings_data = $holdings->getHoldings($clientID);
        $total = $this->calculateSpotTotal($holdings_data);

        $LBMA_DATE = isset($holdings_data[0]['spot_date']) && is_array($holdings_data) ? date('d-M-y', strtotime($holdings_data[0]['spot_date'])) : '';

        $grouped = [];
        foreach ($holdings_data as $item) {
            $location = $item['location'];

            $grouped[$location][] = (object) [
                'metal' => $item['description'] ?? '',
                'location' => $item['location'] ?? '',
                'serials' => $item['serial_no'] ?? '',
                'pureOz' => isset($item['fine_oz']) ? (float) $item['fine_oz'] : 0.00,
                'total' => isset($item['total']) ? (float) $item['total'] : 0.00,
                'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : 0,
                'spot_price' => isset($item['spot_price']) ? (int) $item['spot_price'] : 0,
                'longDesc' => $item['description'],
            ];
        }

        $recordModel = $this->record->getRecord();
        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('LBMA_DATE', $LBMA_DATE);
        $viewer->assign('TOTAL', $total);
        $viewer->assign('ERP_HOLDINGS', $grouped);
        $viewer->assign('ERP_HOLDINGMETALS', $holdings_data);
        $viewer->assign('COMPANY', $companyRecord);

        if ($request->get('PDFDownload')) {
            $viewer->assign('ENABLE_DOWNLOAD_BUTTON', false);
            $html = $viewer->view('HoldingPrintPreview.tpl', $moduleName, true);
            $this->downloadPDF($html);
        } else {
            $viewer->assign('ENABLE_DOWNLOAD_BUTTON', true);
            $viewer->view('HoldingPrintPreview.tpl', $moduleName);
        }
    }

    protected function calculateSpotTotal($holdings_data)
    {
        $total = 0.00;
        foreach ($holdings_data as $item) {
            $total += isset($item['total']) ? (float) $item['total'] : 0.00;
        }
        return $total;
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');

        $fileName = "statement_of_holdings_and_valuation_$clientID";
        $handle = fopen($root_directory . $fileName . '.html', 'a') or die('Cannot open file:  ');
        fwrite($handle, $html);
        fclose($handle);

        exec("wkhtmltopdf --enable-local-file-access  -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking " . $root_directory . "$fileName.html " . $root_directory . "$fileName.pdf");
        unlink($root_directory . $fileName . '.html');

        header("Content-type: application/pdf");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=" . $clientID . " - SH&V as of " . date('d M Y') . ".pdf");
        header("Content-Description: PHP Generated Data");
        ob_clean();
        flush();
        readfile($root_directory . "$fileName.pdf");
        unlink($root_directory . "$fileName.pdf");
        exit;
    }
}
