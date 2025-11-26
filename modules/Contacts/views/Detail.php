<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/HoldingsDB.php';
// include_once 'modules/HoldingCertificate/CertificateHandler.php';

class Contacts_Detail_View extends Accounts_Detail_View
{

	protected $record = null;

	function __construct()
	{
		parent::__construct();
	}

	public function showModuleDetailView(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		// Getting model to reuse it in parent 
		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$viewer = $this->getViewer($request);
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		return parent::showModuleDetailView($request);
	}

	public function showModuleSummaryView($request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if (!$this->record) $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);

		$recordModel = $this->record->getRecord();

		// REAL CUSTOMER ID FROM RECORD
		$clientID = $recordModel->get('cf_898');

		// $certificateHandler = new GPM_CertificateHandler();
		// $result = $certificateHandler->generateCertificate(13);

		// echo '<pre>';
		// var_dump($result);
		// echo '</pre>';
		// $recordModelData = $recordModel->getData();

		// echo "<pre>";
		// // var_dump($recordModelData);
		// var_dump($clientID);
		// echo "</pre>";

		// HARDCODED DATA FOR NOW
		$erpData = [
			'BALANCES' => [
				'available' => 12500.55,
				'pending'   => 300.00,
			],
		];

		// -------------------------------------------
		// 🔥 REAL ACTIVITY SUMMARY DATA
		// -------------------------------------------
		$fieldModel = Vtiger_Field_Model::getInstance('package_currency', Vtiger_Module_Model::getInstance('GPMIntent'));
		$values = $fieldModel->getPicklistValues();
		$currency_list = array_keys($values);

		$activity = new dbo_db\ActivitySummary();
		$activity_data = $activity->getActivitySummary($clientID);

		$holdings = new dbo_db\HoldingsDB();
		$holdings_data = $holdings->getHoldingsData($clientID);

		$certificate_id = $this->getCertificateId($recordId);

		$years = [];
		for ($i = 0; $i <= 5; $i++) {
			$years[] = date('Y', strtotime("-$i year"));
		}



		echo '<pre>';
		echo "\n Data fetched from ActivitySummary: " . date('Y-m-d H:i:s') . PHP_EOL;
		var_dump($activity_data);
		echo '</pre>';

		// -------------------------------------------
		// 🔥 HARDCODED ACTIVITY SUMMARY DATA
		// -------------------------------------------
		$activityData = [
			'CURRENCY_LIST' => $currency_list,
			'CURRENCY_SELECTED' => 'USD',

			'TRANSACTIONS' => $activity_data,
			// 'TRANSACTIONS' => [
			// 	[
			// 		'voucher_no' => 'SAL/2025/001',
			// 		'voucher_type' => 'Sales Invoice',
			// 		'doctype' => 'Sales Invoice',
			// 		'posting_date' => '2025-01-10',
			// 		'amount_in_account_currency' => 25000.00
			// 	],
			// 	[
			// 		'voucher_no' => 'PUR/2025/002',
			// 		'voucher_type' => 'Purchase Invoice',
			// 		'doctype' => 'Purchase Invoice',
			// 		'posting_date' => '2025-01-15',
			// 		'amount_in_account_currency' => -12000.00
			// 	],
			// 	[
			// 		'voucher_no' => 'DEP/2025/003',
			// 		'voucher_type' => 'Deposit',
			// 		'doctype' => '',
			// 		'posting_date' => '2025-02-01',
			// 		'amount_in_account_currency' => 5000.00
			// 	]
			// ]
		];

		$viewer = $this->getViewer($request);

		// Assign safely to TPL
		$viewer->assign('CLIENT_CURRENCY', $activityData['CURRENCY_LIST']);
		$viewer->assign('ACTIVITY_SUMMERY_CURRENCY', $activityData['CURRENCY_SELECTED']);
		$viewer->assign('OROSOFT_TRANSACTION', $activityData['TRANSACTIONS']);
		$viewer->assign('CERTIFICATE_HOLDING', $certificate_id);
		$viewer->assign('CURRENCY', $activityData['CURRENCY_SELECTED']);
		$viewer->assign('BALANCES', $erpData['BALANCES']);
		$viewer->assign('HOLDINGS', $holdings_data);
		$viewer->assign('YEARS', $years);

		// RENDER NEW CUSTOM BLOCK HERE
		// $viewer->view('HoldingsWalletSummary.tpl', $moduleName);

		// Continue normal summary
		return parent::showModuleSummaryView($request);
	}


	function getCertificateId($recordId)
	{
		$db = PearDatabase::getInstance();
		$sql = "select notes_id from vtiger_holdingcertificate AS A join vtiger_crmentity AS B ON (A.holdingcertificateid = B.crmid) where A.contact_id = ? AND A.certificate_status = 'Active'  order by holdingcertificateid DESC limit 1";
		$result = $db->pquery($sql, array($recordId));

		return $db->query_result($result, 0, 'notes_id');
	}
}
