<?php
require_once 'modules/Vtiger/actions/Save.php';

class Metals_Save_Action extends Vtiger_Save_Action
{
    public function process(Vtiger_Request $request)
    {
        // Let core save the record
        $recordModel = $this->saveRecord($request);
        $recordId    = $recordModel->getId();

        // Safety: make sure crmentity.setype is Metals
        $db = PearDatabase::getInstance();
        $db->pquery('UPDATE vtiger_crmentity SET setype=? WHERE crmid=?', ['Metals', $recordId]);

        // Force redirect to Detail view under Inventory app
        header('Location: index.php?module=Metals&view=Detail&record=' . $recordId . '&app=INVENTORY');
        exit;
    }
}
