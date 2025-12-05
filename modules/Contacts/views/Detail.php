<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/HoldingsDB.php';
include_once 'modules/HoldingCertificate/CertificateHandler.php';

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
		if (!$this->record) $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);

		$recordModel = $this->record->getRecord();
		$viewer = $this->getViewer($request);
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		return parent::showModuleDetailView($request);
	}

	public function showModuleSummaryView($request)
	{
		$recordId = $request->get('record');
		$selected_currency = $request->get('ActivtySummeryCurrency');
		// if (!$selected_currency) $selected_currency = '';

		$moduleName = $request->getModule();

		if (!$this->record) $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);

		$recordModel = $this->record->getRecord();

		// REAL CUSTOMER ID FROM RECORD
		$clientID = $recordModel->get('cf_898');

		// $certificateHandler = new GPM_CertificateHandler();
		// $result = $certificateHandler->generateCertificate(78);

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

		$activity = new dbo_db\ActivitySummary();
		$activity_data = $activity->getActivitySummary($clientID);

		$holdings = new dbo_db\HoldingsDB();
		// $holdings_data = $holdings->getHoldingsData($clientID);
		$holdings_data = $holdings->getHoldings($clientID);

		$certificate_id = $this->getCertificateId($recordId);

		// Build dynamic currency list based on Activity Summary data
		$currency_list = $this->getCurrenciesFromActivitySummary($activity_data);

		// Check if selected currency is valid and filter $activity_data
		if ($selected_currency && in_array($selected_currency, $currency_list)) {
			$activity_data = array_filter($activity_data, function ($item) use ($selected_currency) {
				return ($item['currency'] ?? '') === $selected_currency;
			});

			$activity_data = array_values($activity_data);
		}

		$years = [];
		for ($i = 0; $i <= 5; $i++) {
			$years[] = date('Y', strtotime("-$i year"));
		}

		// echo '<pre>';
		// echo "\n Data fetched from holdings: " . date('Y-m-d H:i:s') . PHP_EOL;
		// // echo "\n Data fetched from ActivitySummary: " . date('Y-m-d H:i:s') . PHP_EOL;
		// var_dump($holdings_data);
		// echo '</pre>';

		$viewer = $this->getViewer($request);

		// Assign safely to TPL
		$viewer->assign('CLIENT_CURRENCY', $currency_list);
		$viewer->assign('ACTIVITY_SUMMERY_CURRENCY', $selected_currency);
		$viewer->assign('OROSOFT_TRANSACTION', $activity_data);
		$viewer->assign('CERTIFICATE_HOLDING', $certificate_id);
		$viewer->assign('CURRENCY', $selected_currency);
		$viewer->assign('BALANCES', $erpData['BALANCES']);
		$viewer->assign('HOLDINGS', $holdings_data);
		$viewer->assign('YEARS', $years);

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

	protected function getCurrenciesFromActivitySummary($activity_data)
	{
		$currency_list = [];
		foreach ($activity_data as $item) {
			$currency = $item['currency'] ?? '';
			if ($currency && !in_array($currency, $currency_list)) {
				$currency_list[] = $currency;
			}
		}
		return $currency_list;
	}
}
