<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class GPMIntent_ViewProformaInvoice_View extends GPMIntent_DocView_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$intent = $this->record->getRecord();
		$recordId = $request->get('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($intent->get('contact_id'), 'Contacts');
		$comId = $recordModel->get('related_entity');

		if(!Users_Privileges_Model::isPermitted('Contacts', 'DetailView', $intent->get('contact_id'))) {
			throw new AppException('You are not permitted to view the Lead or the Contact information associated with this Intent!');
		}

		$allBankAccounts = BankAccount_Record_Model::getInstancesByCompanyID($comId);
		$bankAccountId = $request->get('bank');
		if (empty($bankAccountId)) {
			$bankAccountId = $allBankAccounts[0]->getId();
		}


		$downloadLink = "index.php?module=GPMIntent&view=ViewProformaInvoice&record=$recordId&PDFDownload=true&bank=$bankAccountId";

		$products = GPMIntent_Line_Model::getInstanceByIntent($recordId);
		$selectedBank = BankAccount_Record_Model::getInstanceById($bankAccountId);
		//print_r($products);exit;
		$viewer = $this->getViewer($request);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('INTENT', $intent);
		$viewer->assign('RELATED_PRODUCTS', $products);
		$viewer->assign('DOWNLOAD_LINK', $downloadLink);
		$viewer->assign('ALL_BANK_ACCOUNTS', $allBankAccounts);
		$viewer->assign('SELECTED_BANK', $selectedBank);
		$viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($comId));
		if ($request->get('PDFDownload')) {
			$html = $viewer->view("PInvoice.tpl", $moduleName, true);
			$this->downloadPDF($html, $request);
		} else {
			$viewer->assign('ENABLE_DOWNLOAD_BUTTON', true);
			$viewer->view("PInvoice.tpl", $moduleName);
		}
	}
}
