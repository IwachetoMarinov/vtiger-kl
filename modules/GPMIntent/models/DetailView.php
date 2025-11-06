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

		$linkModelList = parent::getDetailViewLinks($linkParams);

		if (Users_Privileges_Model::isPermitted($moduleName, 'ViewQuotation', $recordId)) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'View Quotation',
				'linkurl' => 'index.php?module=' . $moduleName . '&view=ViewQuotation&record=' . $recordId . '&type=full',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}

		if (Users_Privileges_Model::isPermitted($moduleName, 'ViewProformaInvoice', $recordId)) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'View Proforma Invoice',
				'linkurl' => 'index.php?module=' . $moduleName . '&view=ViewProformaInvoice&record=' . $recordId,
				'linkicon' => ''
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
}
