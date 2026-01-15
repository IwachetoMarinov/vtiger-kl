<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License.
 * The Original Code is: vtiger CRM Open Source.
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class GPMCompany extends Vtiger_CRMEntity
{
    /** Main table */
    public $table_name = 'vtiger_gpmcompany';
    public $table_index = 'gpmcompanyid';

    /** Custom fields table */
    public $customFieldTable = ['vtiger_gpmcompanycf', 'gpmcompanyid'];

    /** Tables to include when saving */
    public $tab_name = [
        'vtiger_crmentity',
        'vtiger_gpmcompany',
        'vtiger_gpmcompanycf'
    ];

    /** Table index keys */
    public $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'vtiger_gpmcompany' => 'gpmcompanyid',
        'vtiger_gpmcompanycf' => 'gpmcompanyid'
    ];

    /** List view configuration */
    public $list_fields = [
        'Company Name' => ['gpmcompany', 'company_name'],
        'Assigned To' => ['crmentity', 'smownerid']
    ];

    public $list_fields_name = [
        'Company Name' => 'company_name',
        'Assigned To' => 'assigned_user_id'
    ];

    /** Field linking listview → detailview */
    public $list_link_field = 'company_name';

    /** Search view configuration */
    public $search_fields = [
        'Company Name' => ['gpmcompany', 'company_name'],
        'Assigned To' => ['vtiger_crmentity', 'assigned_user_id']
    ];

    public $search_fields_name = [
        'Company Name' => 'company_name',
        'Assigned To' => 'assigned_user_id'
    ];

    /** Popup / lookup configuration */
    public $popup_fields = ['company_name'];

    /** Alphabetical search */
    public $def_basicsearch_col = 'company_name';

    /** Record label in detail view */
    public $def_detailview_recname = 'company_name';

    /** Mandatory fields */
    public $mandatory_fields = ['company_name', 'assigned_user_id'];

    /** Default list sorting */
    public $default_order_by = 'company_name';
    public $default_sort_order = 'ASC';

    /**
     * Lifecycle handler for vtlib events
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        global $adb;

        if ($eventType == 'module.postinstall') {
            $moduleInstance = Vtiger_Module::getInstance($moduleName);
            $moduleInstance->enableTools(['Import', 'Export']);
            $moduleInstance->setDefaultSharing('Public_ReadOnly');
        } elseif ($eventType == 'module.disabled') {
            // Handle module being disabled
        } elseif ($eventType == 'module.preuninstall') {
            // Handle cleanup before uninstall
        } elseif ($eventType == 'module.preupdate') {
            // Before update
        } elseif ($eventType == 'module.postupdate') {
            // After update
        }
    }

    /**
     * Called on every record save
     */
    public function save_module($module)
    {
        $this->updateRelatedEntityPicklist();
    }

    /**
     * Custom: Ensure related_entity picklist is up-to-date with company_orosoft_code
     */
    protected function updateRelatedEntityPicklist()
    {
        global $adb;

        $picklistValue = trim($this->column_fields['company_orosoft_code']);

        // Guard: skip if no value set
        if (empty($picklistValue)) {
            return;
        }

        // Check if value already exists (case-insensitive)
        $result = $adb->pquery(
            'SELECT 1 FROM vtiger_related_entity WHERE LOWER(related_entity) = LOWER(?)',
            [$picklistValue]
        );

        if ($adb->num_rows($result) == 0) {
            // Get next sort order
            $maxSortIdResult = $adb->pquery(
                'SELECT COALESCE(MAX(sortorderid), 0) + 1 AS maxsortid FROM vtiger_related_entity',
                []
            );
            $sortid = $adb->query_result($maxSortIdResult, 0, 'maxsortid');

            // Insert new entry
            $newId = $adb->getUniqueID('vtiger_related_entity');
            $adb->pquery(
                'INSERT INTO vtiger_related_entity (related_entityid, related_entity, sortorderid, presence)
                 VALUES (?, ?, ?, 1)',
                [$newId, $picklistValue, $sortid]
            );
        }
    }
}
