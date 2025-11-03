<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class GPMCryptoTrx extends Vtiger_CRMEntity {
    var $table_name = 'vtiger_gpmcryptotrx';
    var $table_index = 'gpmcryptotrxid';

    // Custom field table
    var $customFieldTable = array('vtiger_gpmcryptotrxcf', 'gpmcryptotrxid');

    // Base tables
    var $tab_name = array('vtiger_crmentity', 'vtiger_gpmcryptotrx', 'vtiger_gpmcryptotrxcf');
    var $tab_name_index = array(
        'vtiger_crmentity'      => 'crmid',
        'vtiger_gpmcryptotrx'   => 'gpmcryptotrxid',
        'vtiger_gpmcryptotrxcf' => 'gpmcryptotrxid'
    );

    // List view
    var $list_fields = array(
        'Wallet Address' => array('vtiger_gpmcryptotrx', 'wallet_address'),
        'Assigned To'    => array('vtiger_crmentity', 'smownerid'),
    );
    var $list_fields_name = array(
        'Wallet Address' => 'wallet_address',
        'Assigned To'    => 'assigned_user_id',
    );

    var $list_link_field = 'wallet_address';

    // Search and popup support
    var $search_fields = array(
        'Wallet Address' => array('vtiger_gpmcryptotrx', 'wallet_address'),
        'Assigned To'    => array('vtiger_crmentity', 'smownerid'),
    );
    var $search_fields_name = array(
        'Wallet Address' => 'wallet_address',
        'Assigned To'    => 'assigned_user_id',
    );

    var $popup_fields = array('wallet_address');
    var $def_basicsearch_col = 'wallet_address';
    var $def_detailview_recname = 'wallet_address';

    var $mandatory_fields = array('wallet_address', 'assigned_user_id', 'contact_id');
    var $default_order_by = 'wallet_address';
    var $default_sort_order = 'ASC';

    function vtlib_handler($moduleName, $eventType) {
        global $adb;
        if ($eventType == 'module.postinstall') {
            // Handle post-install actions (e.g., default relations, sharing rules)
        } elseif ($eventType == 'module.disabled') {
            // Before module is disabled
        } elseif ($eventType == 'module.preuninstall') {
            // Before module is uninstalled
        } elseif ($eventType == 'module.preupdate') {
            // Before module update
        } elseif ($eventType == 'module.postupdate') {
            // After module update
        }
    }
}
