<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class MetalPrice_GetMetalPrice_Action extends Vtiger_Action_Controller
{

    function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $metal = $request->get('metal');
        $record = MetalPrice_Record_Model::getLatestPriceByName($metal);
        if ($record) {
            echo json_encode (array('price'=>$record->get('pm_rate')));
        } else {
            echo json_encode (array('price'=>null));
        }
    }
}
