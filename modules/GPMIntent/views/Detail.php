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
class GPMIntent_Detail_View extends Vtiger_Detail_View
{
	public function getDetailViewLinks($linkParams)
	{
		$links = parent::getDetailViewLinks($linkParams);

		if (isset($links['DETAILVIEWTAB']) && is_array($links['DETAILVIEWTAB'])) {
			foreach ($links['DETAILVIEWTAB'] as $index => $linkModel) {
				if (
					$linkModel instanceof Vtiger_Link_Model &&
					$linkModel->get('linklabel') === 'LBL_SUMMARY'
				) {
					unset($links['DETAILVIEWTAB'][$index]);
				}
			}
		}

		return $links;
	}

	public function showModuleDetailView(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$products = GPMIntent_Line_Model::getInstanceByIntent($recordId);

		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_PRODUCTS', $products);

		return parent::showModuleDetailView($request);
	}

	public function showModuleBasicView(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$products = GPMIntent_Line_Model::getInstanceByIntent($recordId);

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$moduleName  = $recordModel->getModuleName();

		// Get module model properly
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currencyValue = null;

		// THIS is correct place to call getFields()
		foreach ($moduleModel->getFields() as $fieldModel) {

			if (strcasecmp(trim($fieldModel->get('label')), 'Currency') === 0) {

				$realFieldName = $fieldModel->get('name');
				$currencyValue = $recordModel->get($realFieldName);
				break;
			}
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('CURRENCY', $currencyValue);
		$viewer->assign('RELATED_PRODUCTS', $products);

		return parent::showModuleBasicView($request);
	}
}
