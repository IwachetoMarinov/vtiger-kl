<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once 'dbo_db/HoldingsDB.php';
include_once 'modules/Contacts/models/MetalsAPI.php';

class Contacts_HoldingPrintPreview_View extends Vtiger_Index_View
{

    protected $record = null;

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
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();

        // REAL CUSTOMER ID FROM RECORD
        $clientID = $recordModel->get('cf_898');

        $holdings = new dbo_db\HoldingsDB();
        $holdings_data = $holdings->getHoldingsData($clientID);

        // echo '<pre>';
        // echo 'Holdings Data: ';
        // var_dump($holdings_data);
        // echo '</pre>';

        $metalsAPI = new MetalsAPI();
        $metals_data = $metalsAPI->getMetals();

        $grouped = [];
        foreach ($holdings_data as $item) {
            $location = $item['location'];
            $serials = isset($item['serial_numbers']) && is_array($item['serial_numbers']) ? $item['serial_numbers'] : [];
            $Serial_string = isset($item['serial_numbers']) && is_array($item['serial_numbers']) ? implode(", ", $item['serial_numbers']) : '';

            $grouped[$location][] = (object) [
                // 'metal' => $item['metal'],
                'metal' => $item['description'],
                'location' => $item['location'],
                'pureOz' => isset($item['fine_oz']) ? (float) $item['fine_oz'] : 0.00,
                'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : 0,
                'modiefiedSerials' =>  $serials,
                'longDesc' => $item['description'] . " - " . $Serial_string
            ];
        }

        // echo '<pre>';
        // echo 'Grouped Data: ';
        // var_dump($grouped);
        // echo '</pre>';

        // Metals data formatting
        $metals = [];
        foreach ($metals_data as $data) {
            $item = [
                $data['MT_Code'] => [
                    'price_date' => $data['Date'],
                    'price' => $data['SpotPriceCurr'] ?? 0.00
                ]
            ];
            $metals = array_merge($metals, $item);
        }

        $erpData = [
            'Holdings' => $grouped,
            'MetalPrice' => $metals
        ];

        // echo '<pre>';
        // echo 'Formatted Holdings Data: ';
        // var_dump($metals);
        // echo '</pre>';

        $recordModel = $this->record->getRecord();
        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('LBMA_DATE', date('d-M-y', strtotime($erpData['MetalPrice']['XAU']['price_date'])));
        $viewer->assign('ERP_HOLDINGS', $grouped);
        $viewer->assign('ERP_METALPRICE', $erpData['MetalPrice']);
        $viewer->assign('ERP_HOLDINGMETALS', array_unique(array_column($holdings_data, 'brand')));
        $viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($recordModel->get('related_entity')));

        if ($request->get('PDFDownload')) {
            $viewer->assign('ENABLE_DOWNLOAD_BUTTON', false);
            $html = $viewer->view('HoldingPrintPreview.tpl', $moduleName, true);
            $this->downloadPDF($html);
        } else {
            $viewer->assign('ENABLE_DOWNLOAD_BUTTON', true);
            $viewer->view('HoldingPrintPreview.tpl', $moduleName);
        }
    }

    function processHoldingData($datas)
    {
        $newData = [];
        foreach ($datas as $data) {
            $modifiedSerials = $this->sumSerials($data->serials);
            $data->modiefiedSerials = $modifiedSerials;
            if (in_array($data->location, array('Brinks Singapore', 'Fineart', 'Malca-Amit Singapore'))) {
                $data->location = 'LFPSG';
            }
            if (in_array($data->location, array('Brinks Hong Kong', 'Malca-Amit Hong Kong'))) {
                $data->location = 'Brinks Hong Kong';
            }
            if (strtolower($data->metal) == 'mbtc') {
                $data->pureOz = number_format($data->pureOz / 100000, 8);;
            }
            $newData[$data->location][] = $data;
        }
        return $newData;
    }

    function splitAndOrder($values)
    {
        $b = [];
        $subList = [];
        $prev_n = -1;
        foreach ($values as $n) {
            if ($prev_n + 1 != $n && !empty($subList)) {
                $b[] = $subList;
                $subList = [];
            }
            $subList[] = $n;
            $prev_n = $n;
        }
        $b[] = $subList;
        return $b;
    }

    function genSerialSequance($prefix, $serialsSequance)
    {
        $serialStringrray = [];
        foreach ($serialsSequance as $serials) {
            if (count($serials) > 1) {
                $serialGroup = $prefix . $serials[0] . '-' . $prefix . end($serials);
            } else {
                $serialGroup = $prefix . $serials[0];
            }

            $serialStringrray[] = str_replace('single', '', $serialGroup);
        }
        return $serialStringrray;
    }

    function sumSerials($serials)
    {
        $splitList = array();
        foreach ($serials as $serial) {
            $part = preg_split('/(?<=\D)(?=\d)/', $serial);
            if (count($part) > 1) {
                $splitList[$part[0]][] = $part[1];
            } else {
                $splitList['single'][] = $part[0];
            }
        }
        $lsitOfSerials = [];
        foreach ($splitList as $key => $value) {
            sort($value);
            $lsitOfSerials = array_merge($lsitOfSerials, $this->genSerialSequance($key, $this->splitAndOrder($value)));
        }
        return $lsitOfSerials;
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
