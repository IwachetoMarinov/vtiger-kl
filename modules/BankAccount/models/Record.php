<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class BankAccount_Record_Model extends Vtiger_Record_Model
{

    public static function getInstancesByCompanyID($companyId)
    {
        $db = PearDatabase::getInstance();

        $relatedModuleResult = $db->pquery(
            "SELECT rel.relcrmid AS bankaccountid FROM vtiger_crmentityrel AS rel INNER JOIN vtiger_crmentity AS crm ON crm.crmid = rel.relcrmid WHERE crm.deleted = 0 
            AND rel.crmid = ? AND rel.module = 'GPMCompany' AND rel.relmodule = 'BankAccount'",
            [$companyId]
        );

        $rows = $db->num_rows($relatedModuleResult);

        $instances = [];

        for ($i = 0; $i < $rows; $i++) {
            $recordId = $db->query_result($relatedModuleResult, $i, 'bankaccountid');

            $focus = CRMEntity::getInstance('BankAccount');
            $focus->id = $recordId;
            $focus->retrieve_entity_info($recordId, 'BankAccount');

            $modelClassName = Vtiger_Loader::getComponentClassName(
                'Model',
                'Record',
                'BankAccount'
            );

            $instance = new $modelClassName();

            $instances[] = $instance
                ->setData($focus->column_fields)
                ->set('id', $recordId)
                ->setModule('BankAccount')
                ->setEntity($focus);
        }

        return $instances;
    }

    public static function getAllInstances()
    {
        $db = PearDatabase::getInstance();

        $result = $db->pquery(
            "SELECT crm.crmid AS bankaccountid
         FROM vtiger_crmentity crm
         WHERE crm.deleted = 0
         AND crm.setype = 'BankAccount'",
            []
        );

        $rows = $db->num_rows($result);
        $instances = [];

        for ($i = 0; $i < $rows; $i++) {
            $recordId = $db->query_result($result, $i, 'bankaccountid');

            $focus = CRMEntity::getInstance('BankAccount');
            $focus->id = $recordId;
            $focus->retrieve_entity_info($recordId, 'BankAccount');

            $modelClassName = Vtiger_Loader::getComponentClassName(
                'Model',
                'Record',
                'BankAccount'
            );

            $instance = new $modelClassName();

            $instances[] = $instance
                ->setData($focus->column_fields)
                ->set('id', $recordId)
                ->setModule('BankAccount')
                ->setEntity($focus);
        }

        return $instances;
    }

    public function getIban()
    {
        $fieldName = $this->getFieldNameByLabel('Beneficiary IBAN No');

        if (empty($fieldName)) {
            return $this->get('iban_no') ?: $this->get('cf_1149');
        }

        return $this->get($fieldName);
    }

    private function getFieldNameByLabel($fieldLabel)
    {
        global $adb;

        $result = $adb->pquery(
            "SELECT vtiger_field.fieldname
             FROM vtiger_field
             INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid
             WHERE vtiger_tab.name = ?
             AND vtiger_field.fieldlabel = ?
             AND vtiger_field.presence IN (0, 2)
             LIMIT 1",
            ['BankAccount', $fieldLabel]
        );

        if ($adb->num_rows($result) === 0) {
            return null;
        }

        return $adb->query_result($result, 0, 'fieldname');
    }
}
