<?php

class Assets_GetAllMetals_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $data = Assets_Module_Model::getAllMetalsByMetalType();
        echo json_encode($data);
        exit;
    }
}
