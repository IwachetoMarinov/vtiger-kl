<?php
include_once 'modules/Vtiger/CRMEntity.php';

class MetalPrice extends Vtiger_CRMEntity
{
    var $table_name  = 'vtiger_metalprice';
    var $table_index = 'metalpriceid';

    var $customFieldTable = array('vtiger_metalpricecf', 'metalpriceid');

    var $tab_name = array('vtiger_crmentity', 'vtiger_metalprice', 'vtiger_metalpricecf');

    var $tab_name_index = array(
        'vtiger_crmentity'      => 'crmid',
        'vtiger_metalprice'     => 'metalpriceid',
        'vtiger_metalpricecf'   => 'metalpriceid',
    );

    // List view columns
    var $list_fields = array(
        'Metal'       => array('metalprice', 'type_of_metal'),
        'Assigned To' => array('crmentity', 'smownerid'),
    );
    var $list_fields_name = array(
        'Metal'       => 'type_of_metal',
        'Assigned To' => 'assigned_user_id',
    );
    var $list_link_field = 'type_of_metal';

    // Search (popup & basic search use this map)
    var $search_fields = array(
        'Metal'       => array('metalprice', 'type_of_metal'),
        'Assigned To' => array('crmentity', 'smownerid'),   // <-- fixed
    );
    var $search_fields_name = array(
        'Metal'       => 'type_of_metal',
        'Assigned To' => 'assigned_user_id',
    );

    var $popup_fields           = array('type_of_metal');
    var $def_basicsearch_col    = 'type_of_metal';
    var $def_detailview_recname = 'type_of_metal';

    // Ensure saves won’t fail; add price_date if you want to force it
    var $mandatory_fields = array('type_of_metal', 'assigned_user_id'/*, 'price_date'*/);

    // Sort newest first is usually better for pricing history
    var $default_order_by   = 'price_date';
    var $default_sort_order = 'DESC';

    function vtlib_handler($moduleName, $eventType)
    {
        if ($eventType == 'module.postinstall') {
            // hook for post-install actions if needed
        }
    }
}
