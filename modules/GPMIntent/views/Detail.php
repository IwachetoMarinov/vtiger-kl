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

	public function showModuleDetailView(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		// $moduleName = $request->getModule();
		$products = GPMIntent_Line_Model::getInstanceByIntent($recordId);
		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_PRODUCTS', $products);
		return parent::showModuleDetailView($request);
	}

	public function showModuleBasicView(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		// $moduleName = $request->getModule();
		$products = GPMIntent_Line_Model::getInstanceByIntent($recordId);
		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_PRODUCTS', $products);
		return parent::showModuleBasicView($request);
	}
}
