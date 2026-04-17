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

// include_once 'modules/Contacts/models/MetalsAPI.php';

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
		$start_date = $request->get('start_date');
		$end_date = $request->get('end_date');
		$order_by = "desc";
		$order_by_params = $request->get('orderBy');

		// Test for Metal Prices 
		// $metalsAPI = new MetalsAPI();
		// $metals = $metalsAPI->getMetalTypes();

		// REAL CUSTOMER ID FROM RECORD
		$recordModel = $this->record->getRecord();
		$clientID = $recordModel->get('cf_898');

		$activity = new dbo_db\ActivitySummary();

		$years_array  = $activity->getActivityYears($clientID);
		$years = array_reverse($years_array);

		// Check if there is no selected year set current year
		if (empty($selected_year)) {
			if (is_array($years) && !empty($years)) {
				$selected_year = max($years);
			} else {
				$selected_year = date('Y');
			}
		}

		if (isset($start_date) && !empty($start_date)) $selected_year = '';

		if (!empty($order_by_params) && $order_by_params === 'asc') $order_by = "asc";

		$moduleName = $request->getModule();

		if (!$this->record) $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);

		// $activity_data = $activity->getActivitySummary($clientID);

		// Get PI activity data and merge with old activity data only for DEV server
		$activity_data = $activity->getPIActivitySummary($clientID);

		$holdings = new dbo_db\HoldingsDB();

		$holdings_data = $holdings->getHoldings($clientID);

		$wallets = $holdings->getWalletBalances($clientID);

		$certificate_id = $this->getCertificateId($recordId);
		// Get currencies from ERP database
		$currency_list = $activity->getTransactionCurrencies($clientID);

		if (!$selected_currency && is_array($wallets)) {
			$selected_currency = $wallets[0]['Curr_Code'] ?? '';
		} elseif (!$selected_currency && is_array($currency_list)) {
			$selected_currency = $currency_list[0] ?? '';
		}

		if (
			($selected_currency && in_array($selected_currency, $currency_list)) ||
			!empty($selected_year) ||
			!empty($start_date) ||
			!empty($end_date)
		) {
			$startTs = !empty($start_date) ? strtotime($start_date . ' 00:00:00') : null;
			$endTs   = !empty($end_date)   ? strtotime($end_date . ' 23:59:59') : null;

			$activity_data = array_values(array_filter($activity_data, function ($item) use (
				$selected_currency,
				$currency_list,
				$selected_year,
				$startTs,
				$endTs,
			) {
				// Year filter
				if (!empty($selected_year) && !empty($item['document_date'])) {
					$itemYear = date('Y', strtotime($item['document_date']));
					if ($itemYear !== (string) $selected_year) return false;
				}

				// Currency filter
				if ($selected_currency && in_array($selected_currency, $currency_list)) {

					$itemCurrency = $item['currency'] ?? '';
					$voucherType  = $item['voucher_type'] ?? '';

					// Allow MRD / MPD with empty currency
					if (empty($itemCurrency) && in_array($voucherType, ['MRD', 'MPD'])) return true;

					// Normal currency filtering
					if ($itemCurrency !== $selected_currency) return false;
				}

				// Date range filter
				if (!empty($item['document_date'])) {
					$itemTs = strtotime($item['document_date']);

					if ($startTs && $itemTs < $startTs) return false;

					if ($endTs && $itemTs > $endTs) return false;
				}

				return true;
			}));
		}

		$viewer = $this->getViewer($request);

		// New order by document_date ascending/descending based on order_by param
		if ($order_by === 'asc') {
			usort($activity_data, function ($a, $b) {
				$dateA = isset($a['document_date']) ? strtotime($a['document_date']) : 0;
				$dateB = isset($b['document_date']) ? strtotime($b['document_date']) : 0;
				return $dateA <=> $dateB;
			});
		} elseif ($order_by === 'desc') {
			usort($activity_data, function ($a, $b) {
				$dateA = isset($a['document_date']) ? strtotime($a['document_date']) : 0;
				$dateB = isset($b['document_date']) ? strtotime($b['document_date']) : 0;
				return $dateB <=> $dateA;
			});
		}

		// Assign safely to TPL
		$viewer->assign('CLIENT_CURRENCY', $currency_list);
		$viewer->assign('ACTIVITY_SUMMERY_CURRENCY', $selected_currency);
		$viewer->assign('OROSOFT_TRANSACTION', $activity_data);
		$viewer->assign('CERTIFICATE_HOLDING', $certificate_id);
		$viewer->assign('CURRENCY', $selected_currency);
		$viewer->assign('ORDER_BY', $order_by);
		$viewer->assign('SELECTED_YEAR', $selected_year);
		$viewer->assign('BALANCES', $wallets);
		$viewer->assign('HOLDINGS', $holdings_data);
		$viewer->assign('YEARS', $years);

		// Continue normal summary
		return parent::showModuleSummaryView($request);
	}

	public function getHeaderScripts(Vtiger_Request $request)
	{
		$headerScripts = parent::getHeaderScripts($request);

		$jsFileNames = array(
			'modules.Contacts.resources.MultiDocUpload'
		);

		$jsScripts = $this->checkAndConvertJsScripts($jsFileNames);
		return array_merge($headerScripts, $jsScripts);
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCss = parent::getHeaderCss($request);

		$cssFileNames = array(
			'~/layouts/v7/modules/Contacts/resources/custom.css',
		);

		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		return array_merge($headerCss, $cssInstances);
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
