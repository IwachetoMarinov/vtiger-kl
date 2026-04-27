<?php

class Potentials_AutoLinkContact_Handler extends VTEventHandler
{

    public function handleEvent($eventName, $entityData)
    {

        if ($eventName !== 'vtiger.entity.beforesave')  return;

        $moduleName = $entityData->getModuleName();

        if ($moduleName !== 'Potentials')  return;

        $data = $entityData->getData();

        // Get ERP number from Opportunity
        $potentialErpField = $this->getFieldNameByLabel('Potentials', 'Client ERP Number');

        if (empty($potentialErpField)) return;

        $erp = $data[$potentialErpField] ?? $data['cf_1181'] ?? null;

        if (empty($erp)) return;

        global $adb;

        // Find matching Contact
        $result = $adb->pquery(
            "SELECT vtiger_contactscf.contactid 
             FROM vtiger_contactscf
             INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactscf.contactid
             WHERE vtiger_crmentity.deleted = 0
             AND vtiger_contactscf.cf_898 = ?",
            [$erp]
        );

        if ($adb->num_rows($result) == 1) {
            $contactId = $adb->query_result($result, 0, 'contactid');

            // Set relation automatically
            $entityData->set('contact_id', $contactId);
        }
    }

    private function getFieldNameByLabel($moduleName, $fieldLabel)
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
            [$moduleName, $fieldLabel]
        );

        if ($adb->num_rows($result) === 0) {
            return null;
        }

        return $adb->query_result($result, 0, 'fieldname');
    }
}
