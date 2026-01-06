<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

// ini_set('display_errors', 1); error_reporting(E_ALL);

include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/HoldingsDB.php';

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
		$selected_year = $request->get('ActivtySummeryDate');

		$moduleName = $request->getModule();

		if (!$this->record) $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);

		$recordModel = $this->record->getRecord();

		// REAL CUSTOMER ID FROM RECORD
		$clientID = $recordModel->get('cf_898');

		// -------------------------------------------
		// 🔥 REAL ACTIVITY SUMMARY DATA
		// -------------------------------------------

		$activity = new dbo_db\ActivitySummary();
		$activity_data = $activity->getActivitySummary($clientID);

		// echo "<pre>";
		// print_r($activity_data);
		// echo "</pre>";

		$holdings = new dbo_db\HoldingsDB();
		$holdings_data = $holdings->getHoldings($clientID);

		$wallets = $holdings->getWalletBalances($clientID);

		$certificate_id = $this->getCertificateId($recordId);

		// Build dynamic currency list based on Activity Summary data
		$currency_list = $this->getCurrenciesFromActivitySummary($activity_data);

		// Check if selected currency is valid and filter $activity_data
		if (
			($selected_currency && in_array($selected_currency, $currency_list)) ||
			!empty($selected_year)
		) {
			$activity_data = array_filter($activity_data, function ($item) use (
				$selected_currency,
				$currency_list,
				$selected_year
			) {
				// Currency filter
				if ($selected_currency && in_array($selected_currency, $currency_list)) {
					if (($item['currency'] ?? '') !== $selected_currency) return false;
				}

				// Year filter
				if (!empty($selected_year) && !empty($item['document_date'])) {
					$itemYear = substr($item['document_date'], 0, 4);

					if ($itemYear !== (string) $selected_year) return false;
				}

				return true;
			});

			$activity_data = array_values($activity_data);
		}

		$years_array  = $this->createYearRange(2020, date('Y'));
		$years = array_reverse($years_array);

		$viewer = $this->getViewer($request);

		// Assign safely to TPL
		$viewer->assign('CLIENT_CURRENCY', $currency_list);
		$viewer->assign('ACTIVITY_SUMMERY_CURRENCY', $selected_currency);
		$viewer->assign('OROSOFT_TRANSACTION', $activity_data);
		$viewer->assign('CERTIFICATE_HOLDING', $certificate_id);
		$viewer->assign('CURRENCY', $selected_currency);
		$viewer->assign('SELECTED_YEAR', $selected_year);
		$viewer->assign('BALANCES', $wallets);
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

	protected function createYearRange($startYear, $endYear)
	{
		$years = [];
		for ($year = $startYear; $year <= $endYear; $year++) {
			$years[] = $year;
		}
		return $years;
	}
}
