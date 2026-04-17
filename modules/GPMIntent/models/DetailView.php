<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class GPMIntent_DetailView_Model extends Vtiger_DetailView_Model
{

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams)
	{

		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();
		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();

		// get default links
		$linkModelList = parent::getDetailViewLinks($linkParams);

		/* =========================================================
         * 🔥 REMOVE SUMMARY TAB (KEEP DETAILS + UPDATES)
         * ========================================================= */
		if (isset($linkModelList['DETAILVIEWTAB']) && is_array($linkModelList['DETAILVIEWTAB'])) {
			foreach ($linkModelList['DETAILVIEWTAB'] as $index => $linkModel) {
				if (
					$linkModel instanceof Vtiger_Link_Model &&
					($linkModel->get('linklabel') === 'LBL_SUMMARY' || $linkModel->get('linklabel') === 'Summary')
				) {
					unset($linkModelList['DETAILVIEWTAB'][$index]);
				}
			}
		}

		if ($this->hasCustomToolPermission($moduleName, 'ViewQuotation')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'View Quotation',
				'linkurl' => 'index.php?module=' . $moduleName . '&view=ViewQuotation&record=' . $recordId . '&type=full',
				'linkicon' => '',
				'linktarget' => '_blank',
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}

		// if (Users_Privileges_Model::isPermitted($moduleName, 'ViewProformaInvoice', $recordId)) {
		if ($this->hasCustomToolPermission($moduleName, 'ViewProformaInvoice')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'View Proforma Invoice',
				'linkurl' => 'index.php?module=' . $moduleName . '&view=ViewProformaInvoice&record=' . $recordId,
				'linkicon' => '',
				'linktarget' => '_blank',
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}

		// if (Users_Privileges_Model::isPermitted($moduleName, 'ViewInvoice', $recordId) && !empty($recordModel->get('document_no'))) {
		// 	$basicActionLink = array(
		// 		'linktype' => 'DETAILVIEWBASIC',
		// 		'linklabel' => 'View Invoice',
		// 		'linkurl' => 'index.php?module=Contacts&view=DocumentPrintPreview&record=' . $recordModel->get('contact_id') . '&docNo=' . $recordModel->get('document_no') . '&fromIntent=' . $recordId,
		// 		'linkicon' => ''
		// 	);
		// 	$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		// }

		// if (Users_Privileges_Model::isPermitted($moduleName, 'ViewTradeConfirmation', $recordId) && !empty($recordModel->get('document_no'))) {
		// 	$basicActionLink = array(
		// 		'linktype' => 'DETAILVIEWBASIC',
		// 		'linklabel' => 'View TC',
		// 		'linkurl' => 'index.php?module=Contacts&view=TCPrintPreview&record=' . $recordModel->get('contact_id') . '&docNo=' . $recordModel->get('document_no'),
		// 		'linkicon' => ''
		// 	);
		// 	$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		// }



		return $linkModelList;
	}

	protected function hasCustomToolPermission($moduleName, $actionName, $userId = null)
	{
		global $adb, $current_user;

		$userId = $userId ?: $current_user->id;
		$tabId = getTabid($moduleName);

		if (!$tabId) {
			return false;
		}

		$sql = "
		SELECT 1
		FROM vtiger_user2role ur
		INNER JOIN vtiger_role2profile rp
			ON rp.roleid = ur.roleid
		INNER JOIN vtiger_actionmapping am
			ON am.actionname = ?
		INNER JOIN vtiger_profile2utility pu
			ON pu.profileid = rp.profileid
			AND pu.tabid = ?
			AND pu.activityid = am.actionid
		WHERE ur.userid = ?
		  AND pu.permission = 0
		LIMIT 1
	";

		$result = $adb->pquery($sql, array($actionName, $tabId, $userId));

		return ($result && $adb->num_rows($result) > 0);
	}
}
