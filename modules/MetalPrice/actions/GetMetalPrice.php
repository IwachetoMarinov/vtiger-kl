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
        include_once 'modules/Contacts/models/MetalsAPI.php';

        $metal = $request->get('metal');
        $currency = $request->get('currency');

        $metalAPI = new MetalsAPI();
        $record = $metalAPI->getLatestPriceByName($metal, $currency);

        if ($record) {
            echo json_encode(array('price' => $record['SpotPriceCurr']));
            return;
        } else {
            echo json_encode(array('price' => null));
        }
    }
}
