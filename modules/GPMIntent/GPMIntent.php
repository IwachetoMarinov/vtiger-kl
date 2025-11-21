<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class GPMIntent extends Vtiger_CRMEntity
{
	var $table_name = 'vtiger_gpmintent';
	var $table_index = 'gpmintentid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = array('vtiger_gpmintentcf', 'gpmintentid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = array('vtiger_crmentity', 'vtiger_gpmintent', 'vtiger_gpmintentcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_gpmintent' => 'gpmintentid',
		'vtiger_gpmintentcf' => 'gpmintentid'
	);

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Intent No' => array('gpmintent', 'intent_no'),
		'Assigned To' => array('crmentity', 'smownerid')
	);
	var $list_fields_name = array(
		/* Format: Field Label => fieldname */
		'Intent No' => 'intent_no',
		'Assigned To' => 'assigned_user_id',
	);

	// Make the field link to detail view
	var $list_link_field = 'intent_no';

	// For Popup listview and UI type support
	var $search_fields = array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Intent No' => array('gpmintent', 'intent_no'),
		'Assigned To' => array('vtiger_crmentity', 'assigned_user_id'),
	);
	var $search_fields_name = array(
		/* Format: Field Label => fieldname */
		'Intent No' => 'intent_no',
		'Assigned To' => 'assigned_user_id',
	);

	// For Popup window record selection
	var $popup_fields = array('intent_no');

	// For Alphabetical search
	var $def_basicsearch_col = 'intent_no';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'intent_no';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = array('intent_no', 'assigned_user_id');

	var $default_order_by = 'intent_no';
	var $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	function vtlib_handler($moduleName, $eventType)
	{
		global $adb;
		if ($eventType == 'module.postinstall') {
			// TODO Handle actions after this module is installed.
		} else if ($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
		} else if ($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if ($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if ($eventType == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
	}

	function save_module($module)
	{
		if ($this->mode == 'edit') {
			if ($_REQUEST['action'] != 'SaveAjax' && $_REQUEST['action'] != 'MassSave') {
				//Edit Mode
				$this->saveItemLine();
				$this->addClientIdFromERPNumber();
				$this->addIntroducer();
			} else {
				if ($_REQUEST['action'] == 'MassSave') {
					//Massediting
				} else {
					// //Ajax Edit
				}
			}
		} else {
			//Create Mode
			$this->saveItemLine();
			$this->addClientIdFromERPNumber();
			$this->addIntroducer();
		}
	}

	function saveItemLine()
	{
		GPMIntent_Line_Model::deleteByIntent($this->id);
		$itemLines = GPMIntent_Line_Model::getInstencesFromRequest(new Vtiger_Request($_REQUEST, $_REQUEST), $this->id);
		foreach ($itemLines as $item) {
			$item->save();
		}
	}

	function addClientIdFromERPNumber()
	{
		$db = PearDatabase::getInstance();
		$erpNumber = $this->column_fields['contact_erp_no'];
		$db->pquery("update vtiger_gpmintent set contact_id = (select contactid from vtiger_contactscf AS A LEFT JOIN vtiger_crmentity AS B ON (A.contactid = B.crmid)  where A.cf_898 = ? AND B.deleted = 0  limit 1) where gpmintentid = ? ", array($erpNumber, $this->id));
	}

	function addIntroducer()
	{
		$db = PearDatabase::getInstance();
		$erpNumber = $this->column_fields['contact_erp_no'];
		$db->pquery("update vtiger_gpmintent set introducer_id = (select introducer_id from vtiger_contactdetails where contactid = (select contactid from vtiger_contactscf AS A LEFT JOIN vtiger_crmentity AS B ON (A.contactid = B.crmid)  where A.cf_898 = ? AND B.deleted = 0  limit 1)) where gpmintentid = ? ", array($erpNumber, $this->id));
	}
}
