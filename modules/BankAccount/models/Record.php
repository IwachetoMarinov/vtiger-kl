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

    public static function OLDgetInstancesByCompanyID($comId)
    {
        $db = PearDatabase::getInstance();

        $relatedModuleResult = $db->pquery('select bankaccountid from vtiger_bankaccount AS A JOIN vtiger_crmentity AS B ON (A.bankaccountid = B.crmid )  where B.deleted = 0 and related_entity = ? ', array($comId));
        $rows = $db->num_rows($relatedModuleResult);
        $instances = [];
        for ($i = 0; $i < $rows; $i++) {
            $recordId = $db->query_result($relatedModuleResult, $i, 'bankaccountid');
            $focus = CRMEntity::getInstance('BankAccount');
            $focus->id = $recordId;
            $focus->retrieve_entity_info($recordId, 'BankAccount');
            $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', 'BankAccount');
            $instance = new $modelClassName();
            $instances[] = $instance->setData($focus->column_fields)->set('id', $recordId)->setModule('BankAccount')->setEntity($focus);
        }
        return $instances;
    }
}
