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

class HoldingCertificate extends Vtiger_CRMEntity
{
	var $table_name = 'vtiger_holdingcertificate';
	var $table_index = 'holdingcertificateid';

	// Optional but nice to have
	var $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = array('vtiger_holdingcertificatecf', 'holdingcertificateid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = array('vtiger_crmentity', 'vtiger_holdingcertificate', 'vtiger_holdingcertificatecf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = array(
		'vtiger_crmentity'          => 'crmid',
		'vtiger_holdingcertificate' => 'holdingcertificateid',
		'vtiger_holdingcertificatecf' => 'holdingcertificateid',
	);

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = array(
		'GUID'        => array('holdingcertificate', 'guid'),
		'Assigned To' => array('crmentity', 'smownerid'),
	);
	var $list_fields_name = array(
		'GUID'        => 'guid',
		'Assigned To' => 'assigned_user_id',
	);

	// Make the field link to detail view
	var $list_link_field = 'guid';

	// For Popup listview and UI type support
	var $search_fields = array(
		'GUID'        => array('holdingcertificate', 'guid'),
		'Assigned To' => array('crmentity', 'smownerid'),
	);
	var $search_fields_name = array(
		'GUID'        => 'guid',
		'Assigned To' => 'assigned_user_id',
	);

	// For Popup window record selection
	var $popup_fields = array('guid');

	// For Alphabetical search
	var $def_basicsearch_col = 'guid';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'guid';

	// Used when enabling/disabling the mandatory fields for the module.
	// Make sure typeofdata for these fields is V~M in vtiger_field if you really want them mandatory.
	var $mandatory_fields = array('guid', 'assigned_user_id');

	var $default_order_by = 'guid';
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
			// Handle actions after this module is installed.
		} elseif ($eventType == 'module.disabled') {
			// Handle actions before this module is disabled.
		} elseif ($eventType == 'module.preuninstall') {
			// Handle actions when this module is about to be deleted.
		} elseif ($eventType == 'module.preupdate') {
			// Handle actions before this module is updated.
		} elseif ($eventType == 'module.postupdate') {
			// Handle actions after this module is updated.
		}
	}

	function save_module($module)
	{
		global $CERTIFICATE_APP_KEY, $log;

		$guid               = $this->column_fields['guid'];
		$certificate_hash   = $this->column_fields['certificate_hash'];
		$certificate_status = $this->column_fields['certificate_status'];
		$certificate_date   = date('Y-m-d');

		if (empty($CERTIFICATE_APP_KEY) || empty($guid) || empty($certificate_hash)) {
			// Optionally log and bail out quietly
			$log->debug('HoldingCertificate: missing APP_KEY / guid / hash, skipping remote call');
			return;
		}

		$sig = hash_hmac(
			'sha256',
			$guid . $certificate_hash . $certificate_date . $certificate_status,
			$CERTIFICATE_APP_KEY
		);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://certificates.global-precious-metals.com/create/' . $sig);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
			'guid'             => $guid,
			'hash'             => $certificate_hash,
			'certificate_date' => $certificate_date,
			'status'           => $certificate_status,
		)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		$server_output = curl_exec($ch);

		if ($server_output === false) {
			$error = curl_error($ch);
			$log->fatal("HoldingCertificate: CURL error: $error");
		}

		curl_close($ch);
	}
}
