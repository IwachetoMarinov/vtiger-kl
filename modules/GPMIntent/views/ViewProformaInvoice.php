<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
// ini_set('display_errors', 1);error_reporting(E_ALL);

class GPMIntent_ViewProformaInvoice_View extends GPMIntent_DocView_View
{
	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId   = $request->get('record');

		// ✅ Ensure record model is loaded even if preProcess() didn't run
		if (empty($this->record)) $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);

		if (!$this->record) throw new AppException("Intent record not found for ID: $recordId");

		$intent = $this->record->getRecord();
		if (!$intent) throw new AppException("Unable to get Record Model for Intent ID: $recordId");

		// ✅ Get Contact related to Intent
		$contactId = $intent->get('contact_id');
		$intent_currency = $intent->get('cf_1132');

		if (empty($contactId)) throw new AppException("Intent has no related Contact ID.");

		$recordModel = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');

		if (!$recordModel) throw new AppException("Contact not found for ID: $contactId");

		$companyId = $recordModel->get('company_id');

		$companyRecord = null;
		$allBankAccounts = [];

		// ✅ Permission check
		if (!Users_Privileges_Model::isPermitted('Contacts', 'DetailView', $contactId)) {
			throw new AppException('You are not permitted to view the Lead or the Contact information associated with this Intent!');
		}

		if (!empty($companyId)) {
			// ✅ Company record
			$companyRecord = Vtiger_Record_Model::getInstanceById($companyId, 'GPMCompany');
			// ✅ Bank accounts
			$allBankAccounts = BankAccount_Record_Model::getInstancesByCompanyID($companyId);
			$bankAccountId   = $request->get('bank');
		}

		if (empty($bankAccountId) && !empty($allBankAccounts)) {
			$firstAccount  = reset($allBankAccounts);
			$bankAccountId = $firstAccount->getId();
		}

		// ✅ Handle no bank accounts gracefully
		if (empty($bankAccountId)) $bankAccountId = null;

		$downloadLink = "index.php?module=GPMIntent&view=ViewProformaInvoice&record=$recordId&PDFDownload=true&bank=$bankAccountId";

		// ✅ Products
		$products     = GPMIntent_Line_Model::getInstanceByIntent($recordId);
		$selectedBank = null;
		if (!empty($bankAccountId)) $selectedBank = BankAccount_Record_Model::getInstanceById($bankAccountId);

		if (empty($selectedBank)) {
			// fallback dummy object to prevent template fatal
			$selectedBank = new Vtiger_Record_Model();
			$selectedBank->set('beneficiary_name', '');
			$selectedBank->set('account_no', '');
			$selectedBank->set('account_currency', '');
			$selectedBank->set('iban_no', '');
			$selectedBank->set('bank_name', '');
			$selectedBank->set('bank_address', '');
			$selectedBank->set('swift_code', '');
		}

		// ✅ Prepare viewer
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('INTENT', $intent);
		$viewer->assign('INTENT_CURRENCY', $intent_currency ?? "");
		$viewer->assign('RELATED_PRODUCTS', $products);
		$viewer->assign('DOWNLOAD_LINK', $downloadLink);
		$viewer->assign('ALL_BANK_ACCOUNTS', $allBankAccounts);
		$viewer->assign('SELECTED_BANK', $selectedBank);
		$viewer->assign('COMPANY', $companyRecord);

		// ✅ Render / download
		if ($request->get('PDFDownload')) {
			$html = $viewer->view("PInvoice.tpl", $moduleName, true);
			$this->downloadPDF($html, $request);
		} else {
			$viewer->assign('ENABLE_DOWNLOAD_BUTTON', true);
			$viewer->view("PInvoice.tpl", $moduleName);
		}
	}
}
