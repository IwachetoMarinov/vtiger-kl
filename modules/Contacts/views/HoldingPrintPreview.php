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

        // SHOULD BE REMOVED after testing
        $dummy_holdings = [
            [
                'spot_date'  => '2026-01-12',
                'spot_price' => 4612.95,
                'location'   => 'Malca-Amit Hong Kong (Hong Kong)',
                'description' => 'G_100_CB_P49',
                'quantity'   => 3,
                'serial_no'  => `25387, A49728, A49730, AJ74724-AJ74729, AK13604-AK13605,
AK13652, AK13679-AK13685, AK13693-AK13695, AK13698-AK13725,
AK13729-AK13730, AK13929-AK13932, AK13999-AK14000, AM20524-
AM20527, B42008, P96036, R58902`,
                'fine_oz'    => 9.6444,
                'total'      => 44489.13
            ],
            [
                'spot_date'  => '2026-01-12',
                'spot_price' => 4612.95,
                'location'   => 'Malca-Amit Hong Kong (Hong Kong)',
                'description' => 'Gold 1KG Cast Bar 99.99 UBS',
                'quantity'   => 3,
                'serial_no'  => `B33458-B33463, B33708-B33732, B941233-B941235, C514185-
C514186, C514490, C514494, C605706, C605805, C824747, C906082-
C906090, C910829, C964028-C964029, C968027-C968028, C969322,
D208711-D208714, D256910, D283712, D455260, D455675,
D455851-D455852`,
                'fine_oz'    => 9.6444,
                'total'      => 44489.13
            ],
            [
                'spot_date'  => '2026-01-13',
                'spot_price' => 4598.40,
                'location'   => 'Malca-Amit Zurich (Switzerland)',
                'description' => 'G_50_CB_P21',
                'quantity'   => 2,
                'serial_no'  => 'C977877-C977878, H281422-H281423, N281346-N281347',
                'fine_oz'    => 3.2150,
                'total'      => 14778.62
            ],
            [
                'spot_date'  => '2026-01-14',
                'spot_price' => 4630.75,
                'location'   => 'Malca-Amit Dubai (UAE)',
                'description' => 'G_100_CB_P55',
                'quantity'   => 5,
                'serial_no'  => 'AB79740-AB79741, AB92321-AB92325, AD7546, AD99175, AG53160-AG53161, AG53294, AG53296',
                'fine_oz'    => 16.0740,
                'total'      => 74421.30
            ],
            [
                'spot_date'  => '2026-01-13',
                'spot_price' => 4598.40,
                'location'   => 'Malca-Amit Zurich (Switzerland)',
                'description' => 'G_50_CB_P21',
                'quantity'   => 2,
                'serial_no'  => '10401, 14168-14169, 67802-67807, AH21943, AJ70681-AJ70682,
AK11554-AK11586, AK13541, AK13765-AK13776, AK13865-AK13875,
AK13948-AK13953, AK13958-AK13960, AK15843-AK15845, D70638-
D70642
2,764.728 12,753,552.03',
                'fine_oz'    => 3.2150,
                'total'      => 14778.62
            ],
            [
                'spot_date'  => '2026-01-12',
                'spot_price' => 4612.95,
                'location'   => 'Malca-Amit Hong Kong (Hong Kong)',
                'description' => 'Gold 1KG Cast Bar 99.99 UBS',
                'quantity'   => 3,
                'serial_no'  => `B33458-B33463, B33708-B33732, B941233-B941235, C514185-
C514186, C514490, C514494, C605706, C605805, C824747, C906082-
C906090, C910829, C964028-C964029, C968027-C968028, C969322,
D208711-D208714, D256910, D283712, D455260, D455675,
D455851-D455852`,
                'fine_oz'    => 9.6444,
                'total'      => 44489.13
            ],
            [
                'spot_date'  => '2026-01-12',
                'spot_price' => 4612.95,
                'location'   => 'Malca-Amit Hong Kong (Hong Kong)',
                'description' => 'G_100_CB_P49',
                'quantity'   => 3,
                'serial_no'  => '5341021-5341022, 5341032-5341035, 5341071-5341080, 5341121-
5341135, 5383016-5383025, 5846038-5846040',
                'fine_oz'    => 9.6444,
                'total'      => 44489.13
            ],
             [
                'spot_date'  => '2026-01-12',
                'spot_price' => 4612.95,
                'location'   => 'Malca-Amit Hong Kong (Hong Kong)',
                'description' => 'Gold 1KG Cast Bar 99.99 UBS',
                'quantity'   => 3,
                'serial_no'  => `B33458-B33463, B33708-B33732, B941233-B941235, C514185-D208711-D208714, D256910, D283712, D455260, D455675,
D455851-D455852`,
                'fine_oz'    => 9.6444,
                'total'      => 44489.13
            ],
            [
                'spot_date'  => '2026-01-13',
                'spot_price' => 4598.40,
                'location'   => 'Malca-Amit Zurich (Switzerland)',
                'description' => 'G_50_CB_P21',
                'quantity'   => 2,
                'serial_no'  => 'C977877-C977878, H281422-H281423, N281346-N281347, C977877-C977878, H281422-H281423, N281346-N281347',
                'fine_oz'    => 3.2150,
                'total'      => 14778.62
            ],
            [
                'spot_date'  => '2026-01-14',
                'spot_price' => 4630.75,
                'location'   => 'Malca-Amit Dubai (UAE)',
                'description' => 'G_100_CB_P55',
                'quantity'   => 5,
                'serial_no'  => 'AB79740-AB79741, AB92321-AB92325, AD7546, AD99175, AG53160-AG53161, AG53294, AG53296, AB79740-AB79741, AB92321-AB92325, AD7546, AD99175, AG53160-AG53161, AG53294, AG53296',
                'fine_oz'    => 16.0740,
                'total'      => 74421.30
            ]
        ];

        $holdings_data = array_merge($holdings_data, $dummy_holdings);

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
