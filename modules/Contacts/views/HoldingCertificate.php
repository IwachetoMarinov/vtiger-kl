<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/HoldingsDB.php';

class Contacts_HoldingCertificate_View extends Vtiger_Index_View
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

        $contactID = 78; // $recordModel->getId();
        $guid = $this->guidv4(openssl_random_pseudo_bytes(16));
        $clientID = $recordModel->get('cf_898');

        var_dump("Contact Record ID: " . $clientID);

        $holdings = new dbo_db\HoldingsDB();
        $holdings_data = $holdings->getHoldingsData($clientID);

        // var_dump("Holdings Data: ");
        // echo '<pre>';
        // var_dump($holdings_data);
        // echo '</pre>';

        $viewer = new Vtiger_Viewer();
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('ERP_HOLDINGS', $this->processHoldingData($holdings_data));
        $viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($recordModel->get('related_entity')));
        $viewer->assign('GUID', $guid);

        $viewer->view("HoldingCertificate.tpl", $moduleName);
    }

    public function postProcess(Vtiger_Request $request) {}

    protected function guidv4($data)
    {
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    protected

    function processHoldingData($datas)
    {
        $newData = [];

        foreach ($datas as $data) {

            if (!is_array($data) || !isset($data['location'])) continue;

            $location = $data['location'];

            $oldLocations = [
                'Brinks Singapore',
                'Fineart',
                'Malca-Amit Singapore',
                'Malca-Amit Singapore (Singapore)'
            ];

            if (in_array($location, $oldLocations)) $location = 'LFPSG';

            $data['location'] = $location;

            $newData[$location][] = $data;
        }

        return $newData;
    }
}
