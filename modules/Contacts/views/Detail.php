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
class Contacts_Detail_View extends Accounts_Detail_View
{

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

	// public function fetchOROSOft(Vtiger_Request $request)
	// {
	// 	include_once 'modules/Settings/OROSoft/api.php';
	// 	$recordModel = $this->record->getRecord();
	// 	$clientID = $recordModel->get('cf_950');
	// 	$comId = $recordModel->get('related_entity');
	// 	$year =  $request->get('ActivtySummeryDate');
	// 	return array(
	// 		'Transactions' => getOROSoftTransaction($clientID, $year, $comId),
	// 		'Holdings' => getOROSoftHolding($clientID, $comId)
	// 	);
	// }

	// public function fetchFrappe(Vtiger_Request $request)
	// {

	// 	include_once 'modules/Settings/Frappe/api.php';
	// 	$recordModel = $this->record->getRecord();
	// 	$clientID = $recordModel->get('cf_950');
	// 	$comId = $recordModel->get('related_entity');
	// 	$year =  $request->get('ActivtySummeryDate');

	// 	$currency =  empty($request->get('ActivtySummeryCurrency')) ? 'USD' : $request->get('ActivtySummeryCurrency');

	// 	$balancesBywallet = getClientWalletBalance($clientID);

	// 	return array(
	// 		'Transactions' => getFrappeTransaction($clientID, $currency, $year, $comId),
	// 		'Holdings' => getFrappeHolding($clientID, $comId),
	// 		'CURRENCY' => getUniqueCurrencies($clientID),
	// 		'BALANCES' => $balancesBywallet

	// 	);
	// }


	// function showModuleSummaryView($request)
	// {
	//     $recordId = $request->get('record');
	//     $moduleName = $request->getModule();

	//     // Getting model to reuse it in parent 
	//     if (!$this->record) {
	//         $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
	//     }
	//     $year =  $request->get('ActivtySummeryDate');
	//     $viewer = $this->getViewer($request);

	//     if ($year < 2023 && $year > 2000) {
	// 		// TODO: Remove fetchOROSOft method if not needed
	//         // $oroSOftData = $this->fetchOROSOft($request);
	//     } else {
	//         $activtySummeryCurrency = $request->get('ActivtySummeryCurrency');
	//         $oroSOftData = $this->fetchFrappe($request);
	//         $viewer->assign('CLIENT_CURRENCY', $oroSOftData['CURRENCY']);
	//         $viewer->assign('CLIENT_BALANCES', $oroSOftData['BALANCES']);
	//         $viewer->assign('ACTIVITY_SUMMERY_CURRENCY', $activtySummeryCurrency);
	//     }

	//     $viewer->assign('OROSOFT_TRANSACTION', $oroSOftData['Transactions']);
	//     $viewer->assign('OROSOFT_HOLDINGS', $oroSOftData['Holdings']);
	//     $viewer->assign('CERTIFICATE_HOLDING', $this->getCertificateId($recordId));
	//     return parent::showModuleSummaryView($request);
	// }

	public function showModuleSummaryView($request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		// Get model (same as core logic)
		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}

		// --- HARDCODED DATA FOR TESTING ---
		$oroSOftData = [
			'CURRENCY' => 'USD',
			'BALANCES' => [
				'available' => 12500.55,
				'pending'   => 300.00,
			],
			'Transactions' => [
				['date' => '2025-11-07', 'desc' => 'Deposit', 'amount' => 5000],
				['date' => '2025-11-06', 'desc' => 'Purchase Gold', 'amount' => -2000],
			],
			'Holdings' => [
				['metal' => 'Gold', 'oz' => 12.5, 'location' => 'Brinks SG'],
				['metal' => 'Silver', 'oz' => 320.1, 'location' => 'Brinks HK'],
			],
		];
		// ----------------------------------

		$viewer = $this->getViewer($request);

		// Assign dummy data
		$viewer->assign('CLIENT_CURRENCY', $oroSOftData['CURRENCY']);
		$viewer->assign('CLIENT_BALANCES', $oroSOftData['BALANCES']);
		$viewer->assign('OROSOFT_TRANSACTION', $oroSOftData['Transactions']);
		$viewer->assign('OROSOFT_HOLDINGS', $oroSOftData['Holdings']);

		// Inline display (no tpl, no ajax)
		echo "<div style='border:2px solid #008ECA;padding:12px;margin:8px 0;background:#f7fcff;'>
                <h3 style='color:#008ECA;margin-top:0;'>Holdings Summary (Hardcoded)</h3>
                <p><strong>Currency:</strong> {$oroSOftData['CURRENCY']}</p>
                <p><strong>Balances:</strong> Available {$oroSOftData['BALANCES']['available']} / Pending {$oroSOftData['BALANCES']['pending']}</p>
                <hr>
                <h4>Holdings:</h4>
                <ul>";
		foreach ($oroSOftData['Holdings'] as $h) {
			echo "<li>{$h['metal']} — {$h['oz']} oz @ {$h['location']}</li>";
		}
		echo "  </ul>
                <hr>
                <h4>Recent Transactions:</h4>
                <ul>";
		foreach ($oroSOftData['Transactions'] as $t) {
			echo "<li>{$t['date']} — {$t['desc']} — {$t['amount']}</li>";
		}
		echo "  </ul>
              </div>";

		// Keep rest of page working
		return parent::showModuleSummaryView($request);
	}

	function getCertificateId($recordId)
	{
		$db = PearDatabase::getInstance();
		//$recordId = $this->record->getId();
		$sql = "select notes_id from vtiger_holdingcertificate AS A join vtiger_crmentity AS B ON (A.holdingcertificateid = B.crmid) where A.contact_id = ? AND A.certificate_status = 'Active'  order by holdingcertificateid DESC limit 1";
		$result = $db->pquery($sql, array($recordId));
		return $db->query_result($result, 0, 'notes_id');
	}
}
