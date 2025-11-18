<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class MetalPrice_Record_Model extends Vtiger_Record_Model
{

    /**
     * Function to get the Display Name for the record
     * @return <String> - Entity Display Name for the record
     */
    // static function getLatestPriceByName($metal)
    // {
    //     $adb = PearDatabase::getInstance();

    //     $sql = "select metalpriceid from vtiger_metalprice where type_of_metal = ? order by price_date desc limit 1";
    //     $sqlResult = $adb->pquery($sql, array($metal));
    //     $recordId = $adb->query_result($sqlResult, 0, 'metalpriceid');
    //     Vtiger_Record_Model::getInstanceById($recordId, 'MetalPrice');
    // }

    static function getLatestPriceByName($metal)
    {
        $adb = PearDatabase::getInstance();

        $sql = "SELECT metalpriceid FROM vtiger_metalprice WHERE type_of_metal = ? ORDER BY price_date DESC LIMIT 1";
        $sqlResult = $adb->pquery($sql, array($metal));

        if ($adb->num_rows($sqlResult) > 0) {
            $recordId = $adb->query_result($sqlResult, 0, 'metalpriceid');
            return Vtiger_Record_Model::getInstanceById($recordId, 'MetalPrice');
        }

        return null;
    }


    static function getLatestPriceOfAllMetal()
    {
        $adb = PearDatabase::getInstance();
        $sql = "select * from vtiger_metalprice where type_of_metal = ? order by price_date desc  limit 1";
        $response = array();
        foreach (array('XAU', 'XAG', 'XPT', 'XPD', 'MBTC') as $metal) {
            $sqlResult = $adb->pquery($sql, array($metal));
            $response[$metal] = array(
                'price_date' => $adb->query_result($sqlResult, 0, 'price_date'),
                'am_rate' => $adb->query_result($sqlResult, 0, 'am_rate'),
                'pm_rate' => $adb->query_result($sqlResult, 0, 'pm_rate'),
            );
        }
        return $response;
    }
}
