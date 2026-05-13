<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_CompanyDetailsSave_Action extends Settings_Vtiger_Basic_Action
{

	public function process(Vtiger_Request $request)
	{
		$moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
		$reloadUrl = $moduleModel->getIndexViewUrl();

		try {
			$this->Save($request);
		} catch (Exception $e) {
			if ($e->getMessage() == "LBL_INVALID_IMAGE") {
				$reloadUrl .= '&error=LBL_INVALID_IMAGE';
			} else if ($e->getMessage() == "LBL_FIELDS_INFO_IS_EMPTY") {
				$reloadUrl = $moduleModel->getEditViewUrl() . '&error=LBL_FIELDS_INFO_IS_EMPTY';
			}
		}
		header('Location: ' . $reloadUrl);
	}

	public function SaveDebug(Vtiger_Request $request)
	{
		echo '<pre>';

		echo "SAVE ACTION HIT\n\n";

		echo "_FILES:\n";
		print_r($_FILES);

		echo "\n_REQUEST:\n";
		print_r($_REQUEST);

		$moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();

		echo "\norganizationname = ";
		var_dump($request->get('organizationname'));

		echo "\nlogo name = ";
		var_dump($_FILES['logo']['name'] ?? null);

		if (!empty($_FILES['logo']['name'])) {
			$logoDetails = $_FILES['logo'];

			echo "\nvalidateImage = ";
			$saveLogo = Vtiger_Functions::validateImage($logoDetails);
			var_dump($saveLogo);

			global $upload_badext;
			echo "\nupload_badext:\n";
			print_r($upload_badext);

			$binFileName = sanitizeUploadFileName($logoDetails['name'], $upload_badext);

			echo "\nbinFileName = ";
			var_dump($binFileName);

			echo "\nextension = ";
			var_dump(pathinfo($binFileName, PATHINFO_EXTENSION));

			echo "\nCalling saveLogo now...\n";

			$moduleModel->saveLogo($binFileName);

			echo "\nsaveLogo finished\n";
		} else {
			echo "\nNO LOGO FILE RECEIVED\n";
		}

		echo '</pre>';
		exit;
	}

	public function Save(Vtiger_Request $request)
	{
		$moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
		$status = false;
		if ($request->get('organizationname')) {
			$saveLogo = $status = true;
			$binFileName = false;
			if (!empty($_FILES['logo']['name'])) {
				$logoDetails = $_FILES['logo'];
				$saveLogo = Vtiger_Functions::validateImage($logoDetails);
				global $upload_badext; // from config.inc.php
				$binFileName = sanitizeUploadFileName($logoDetails['name'], $upload_badext);
				if ($saveLogo && pathinfo($binFileName, PATHINFO_EXTENSION) != 'txt') {
					$moduleModel->saveLogo($binFileName);
				} else {
					throw new Exception(vtranslate('LBL_INVALID_IMAGE'), 103);
				}
			} else {
				$saveLogo = true;
			}
			$fields = $moduleModel->getFields();
			foreach ($fields as $fieldName => $fieldType) {
				$fieldValue = $request->get($fieldName);
				if ($fieldName === 'logoname') {
					if (!empty($logoDetails['name']) && $binFileName) {
						$fieldValue = decode_html(ltrim(basename(" " . $binFileName)));
					} else {
						$fieldValue = decode_html($moduleModel->get($fieldName));
					}
				} else {
					$fieldValue = strip_tags(decode_html($fieldValue));
				}
				// In OnBoard company detail page we will not be sending all the details
				if ($request->has($fieldName) || ($fieldName == "logoname")) {
					$moduleModel->set($fieldName, $fieldValue);
				}
			}
			$moduleModel->save();
		}
		if ($saveLogo && $status) {
			return;
		} else if (!$saveLogo) {
			throw new Exception('LBL_INVALID_IMAGE', 103);
			//$reloadUrl .= '&error=';
		} else {
			throw new Exception('LBL_FIELDS_INFO_IS_EMPTY', 103);
			//$reloadUrl = $moduleModel->getEditViewUrl() . '&error=';
		}
		return;
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
