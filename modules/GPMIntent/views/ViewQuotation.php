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

class GPMIntent_ViewQuotation_View extends GPMIntent_DocView_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$intent = $this->record->getRecord();
		$recordId = $request->get('record');
		if (empty($intent->get('contact_id')) && empty($intent->get('lead_id'))) {
			throw new AppException('You have not associated a Lead or a Contact with this Intent!');
		}

		$recordModel = (empty($intent->get('contact_id'))) ? Vtiger_Record_Model::getInstanceById($intent->get('lead_id'), 'Leads') : Vtiger_Record_Model::getInstanceById($intent->get('contact_id'), 'Contacts');
		$recordModelModule = (empty($intent->get('contact_id'))) ? 'Leads' : 'Contacts';

		$companyRecord = Contacts_DefaultCompany_View::process();

		if (!Users_Privileges_Model::isPermitted($recordModelModule, 'DetailView', $recordModel->getId())) {
			throw new AppException('You are not permitted to view the Lead or the Contact information associated with this Intent!');
		}

		$docType =  ($request->get('type') == 'full') ? 'QuoteFull' : 'QuoteSimple';

		$downloadLink = "index.php?module=GPMIntent&view=ViewQuotation&record=$recordId&type=" . (($docType == 'QuoteFull') ? 'full' : 'simple') . '&PDFDownload=true';

		$products = GPMIntent_Line_Model::getInstanceByIntent($recordId);

		$moduleModel = Vtiger_Module_Model::getInstance('GPMIntent');
		$client_no = Vtiger_Record_Model::getInstanceById($recordId, 'GPMIntent')->get('contact_erp_no');

		$fields = $moduleModel->getFields();

		$targetLabel = 'Currency';

		$fieldName = null;
		foreach ($fields as $f) {
			if (strcasecmp($f->get('label'), $targetLabel) === 0) {
				$fieldName = $f->getName();
				break;
			}
		}

		$intent_currency = $fieldName ? $intent->get($fieldName) : '';

		$gpm_order_type = $intent->get('gpm_order_type');
		$discount  = "PREMIUM / (DISCOUNT)";

		if ($gpm_order_type == 'Purchase & Storage' || $gpm_order_type == 'Purchase & Delivery') {
			$discount = "PREMIUM";
		} else if ($gpm_order_type == 'Sale') {
			$discount = "DISCOUNT";
		}

		// echo "<pre>";
		// print_r($recordModel);
		// echo "</pre>";

		$viewer = $this->getViewer($request);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('INTENT', $intent);
		$viewer->assign('DISCOUNT', $discount);
		$viewer->assign('INTENT_CURRENCY', $intent_currency ?? "");
		$viewer->assign('RELATED_PRODUCTS', $products);
		$viewer->assign('DOWNLOAD_LINK', $downloadLink);
		$viewer->assign('COMPANY', $companyRecord);
		if ($request->get('PDFDownload')) {
			$html = $viewer->view("$docType.tpl", $moduleName, true);
			$this->downloadPDF($html, $request);
		} else {
			$viewer->assign('ENABLE_DOWNLOAD_BUTTON', true);
			$viewer->view("$docType.tpl", $moduleName);
		}
	}
}
