<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Assets_Module_Model extends Vtiger_Module_Model
{

	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery)
	{
		if ($sourceModule == 'HelpDesk') {
			$condition = " vtiger_assets.assetsid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = ? UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = ?) ";
			$db = PearDatabase::getInstance();
			$condition = $db->convert2Sql($condition, array($record, $record));

			$pos = stripos($listQuery, 'where');
			if ($pos) {
				$split = preg_split('/where/i', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}

	/*
	 * Function to get supported utility actions for a module
	 */
	public function getUtilityActionsNames()
	{
		return array('Import', 'Export', 'DuplicatesHandling');
	}

	/**
	 * Function returns Tickets grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public static function getAllMetalsByMetalType()
	{
		$db = PearDatabase::getInstance();

		$sql = 'SELECT A.*, C.*
                FROM vtiger_assets AS A
                INNER JOIN vtiger_assetscf AS C ON A.assetsid = C.assetsid
                INNER JOIN vtiger_crmentity AS B ON A.assetsid = B.crmid
                WHERE B.deleted = 0
                ORDER BY C.cf_875 DESC'; // FineOz

		$result = $db->pquery($sql, []);

		if (!$result) return [];

		$data = [];

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);

			$metalType = $row['cf_873'];  // Type
			$id = $row['assetsid'];       // Asset ID

			$data[$metalType][$id] = [
				"0" => (string)$id,
				"1" => $row["assetname"],        // product_name
				"2" => $row["cf_873"],           // metal type
				"3" => $row["cf_875"],           // fine oz

				"gpmmetalid"     => (string)$id,
				"product_name"   => $row["assetname"],
				"gpm_metal_type" => $row["cf_873"],
				"fine_oz"        => $row["cf_875"],
			];
		}

		return $data;
	}
}
