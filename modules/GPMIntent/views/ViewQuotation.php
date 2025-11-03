<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

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
		$recordModelModule = (empty($intent->get('contact_id'))) ? 'Leads' :'Contacts';
		
		if(!Users_Privileges_Model::isPermitted($recordModelModule, 'DetailView', $recordModel->getId())) {
			throw new AppException('You are not permitted to view the Lead or the Contact information associated with this Intent!');
		}

		$comId = $recordModel->get('related_entity');

		$docType =  ($request->get('type') == 'full') ? 'QuoteFull' : 'QuoteSimple';

		$downloadLink = "index.php?module=GPMIntent&view=ViewQuotation&record=$recordId&type=" . (($docType == 'QuoteFull') ? 'full' : 'simple') . '&PDFDownload=true';

		$products = GPMIntent_Line_Model::getInstanceByIntent($recordId);
		//print_r($products);exit;
		$viewer = $this->getViewer($request);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('INTENT', $intent);
		$viewer->assign('RELATED_PRODUCTS', $products);
		$viewer->assign('DOWNLOAD_LINK', $downloadLink);
		$viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($comId));
		if ($request->get('PDFDownload')) {
			$html = $viewer->view("$docType.tpl", $moduleName, true);
			$this->downloadPDF($html, $request);
		} else {
			$viewer->assign('ENABLE_DOWNLOAD_BUTTON', true);
			$viewer->view("$docType.tpl", $moduleName);
		}
	}
}
