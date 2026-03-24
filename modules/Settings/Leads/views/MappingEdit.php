<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Leads_MappingEdit_View extends Settings_Vtiger_Index_View
{

	public function requiresPermission(\Vtiger_Request $request)
	{
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView');
		return $permissions;
	}

	function checkPermission(Vtiger_Request $request)
	{
		return parent::checkPermission($request);
	}

	public function process(Vtiger_Request $request)
	{
		echo "START<br>";
		flush();
		echo "1<br>";
		flush();
		$qualifiedModuleName = $request->getModule(false);

		echo "2<br>";
		flush();
		$viewer = $this->getViewer($request);

		echo "3<br>";
		flush();
		var_dump(Settings_Leads_Mapping_Model::getInstance());

		echo "4<br>";
		flush();
		var_dump(Settings_Leads_Module_Model::getInstance('Leads'));

		echo "5<br>";
		flush();
		var_dump(Settings_Leads_Module_Model::getInstance('Accounts'));

		echo "6<br>";
		flush();
		var_dump(Settings_Leads_Module_Model::getInstance('Contacts'));

		echo "7<br>";
		flush();
		var_dump(Settings_Leads_Module_Model::getInstance('Potentials'));

		echo "8<br>";
		flush();
		var_dump(Settings_Leads_Mapping_Model::getRestrictedFieldIdsList());

		die("STOP HERE");
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.LeadMapping"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
