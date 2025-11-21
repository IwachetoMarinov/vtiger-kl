<?php

class Contacts_HoldingPrintPreview_View extends Vtiger_Index_View
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

    // public function fetchOROSOft()
    // {
    //     include_once 'modules/Settings/OROSoft/api.php';
    //     $recordModel = $this->record->getRecord();
    //     $clientID = $recordModel->get('cf_898');
    //     $comId = $recordModel->get('related_entity');
    //     return array(
    //         'Holdings' => getOROSoftHolding($clientID, $comId),
    //         'MetalPrice' => MetalPrice_Record_Model::getLatestPriceOfAllMetal()
    //     );
    // }

    // $oroSOftData = $this->fetchOROSOft();

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        // $oroSOftData = $this->fetchOROSOft();

        // Temporary hardcoded data for testing
        $oroSOftData = [
            'Holdings' => [
                (object)[
                    'metal' => 'Gold',
                    'location' => 'Brinks Singapore',
                    'pureOz' => 25.45,
                    'serials' => ['G1001', 'G1002', 'G1003']
                ],
                (object)[
                    'metal' => 'Silver',
                    'location' => 'Brinks Hong Kong',
                    'pureOz' => 320.10,
                    'serials' => ['S5001', 'S5002']
                ]
            ],
            'MetalPrice' => [
                'XAU' => ['price_date' => date('Y-m-d'), 'price' => 2300],
                'XAG' => ['price_date' => date('Y-m-d'), 'price' => 28.5],
            ]
        ];



        $recordModel = $this->record->getRecord();
        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('LBMA_DATE', date('d-M-y', strtotime($oroSOftData['MetalPrice']['XAU']['price_date'])));
        // SHOULD  be checked this data
        // $viewer->assign('OROSOFT_HOLDINGS', $this->processHoldingData($oroSOftData['Holdings']));
        // HARDCODED OROSOFT_HOLDINGS
        $viewer->assign('OROSOFT_HOLDINGS', []);
        $viewer->assign('OROSOFT_METALPRICE', $oroSOftData['MetalPrice']);
        $viewer->assign('OROSOFT_HOLDINGMETALS', array_unique(array_column($oroSOftData['Holdings'], 'metal')));
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
        //print_r(array_values($newData));exit();
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
