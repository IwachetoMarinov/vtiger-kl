<?php

require_once __DIR__ . '/vendor/autoload.php';

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

echo "<pre>";

try {
    $db = PearDatabase::getInstance();

    // -------------------------
    // Helper: delete company_id field from a module (if exists)
    // -------------------------
    function deleteCompanyField($moduleName) {
        $module = Vtiger_Module::getInstance($moduleName);
        if (!$module) {
            echo "Module $moduleName not found (skip delete)\n";
            return;
        }

        $field = Vtiger_Field::getInstance('company_id', $module);
        if ($field) {
            $field->delete();
            echo "Deleted field company_id from $moduleName\n";
        } else {
            echo "Field company_id not found in $moduleName (no delete)\n";
        }
    }

    // -------------------------
    // 1) DELETE FROM ALL MODULES FIRST
    // -------------------------
    // deleteCompanyField('GPMIntent');
    // deleteCompanyField('Leads');
    // deleteCompanyField('Contacts');

    // Also clean any old convertleadmapping rows for company_id
    $db->pquery("
        DELETE clm FROM vtiger_convertleadmapping clm
        INNER JOIN vtiger_field lf ON clm.leadfid = lf.fieldid
        LEFT JOIN vtiger_field cf ON clm.contactfid = cf.fieldid
        WHERE lf.fieldname = 'company_id' OR cf.fieldname = 'company_id'
    ", []);
    echo "Cleaned old Lead → Contact mapping for company_id (if any)\n";

    // -------------------------
    // Helper: add company_id field to a module/block
    // -------------------------
    function addCompanyFieldToModule($moduleName, $blockLabel) {
        $module = Vtiger_Module::getInstance($moduleName);
        if (!$module) {
            echo "Module $moduleName not found, cannot add field\n";
            return null;
        }

        // Get or create block
        $block = Vtiger_Block::getInstance($blockLabel, $module);
        if (!$block) {
            $block = new Vtiger_Block();
            $block->label = $blockLabel;
            $module->addBlock($block);
            echo "Created block $blockLabel in $moduleName\n";
        }

        // Safety: ensure no leftover field
        $existing = Vtiger_Field::getInstance('company_id', $module);
        if ($existing) {
            echo "company_id already exists in $moduleName (unexpected)\n";
            return $existing;
        }

        $field = new Vtiger_Field();
        $field->name        = 'company_id';
        $field->label       = 'Company';
        $field->table       = $module->basetable;
        $field->column      = 'company_id';
        $field->uitype      = 10;
        $field->typeofdata  = 'V~O';
        $field->columntype  = 'INT(11)';

        $block->addField($field);
        $field->setRelatedModules(['GPMCompany']);

        echo "Added field company_id to $moduleName in block $blockLabel\n";

        return $field;
    }

    // -------------------------
    // 2) ADD TO CONTACTS (default block: LBL_CONTACT_INFORMATION)
    // -------------------------
    $contactField = addCompanyFieldToModule('Contacts', 'LBL_CONTACT_INFORMATION');

    // -------------------------
    // 3) ADD TO LEADS (also in LBL_CONTACT_INFORMATION - create if needed)
    // -------------------------
    $leadField = addCompanyFieldToModule('Leads', 'LBL_CONTACT_INFORMATION');

    // -------------------------
    // 4) CREATE LEAD → CONTACT MAPPING
    // -------------------------
    if ($leadField && $contactField) {
        $leadFieldId    = $leadField->id;
        $contactFieldId = $contactField->id;

        // Insert mapping (accountfid & potentialfid = 0)
        $db->pquery(
            "INSERT INTO vtiger_convertleadmapping (leadfid, contactfid, accountfid, potentialfid)
             VALUES (?, ?, 0, 0)",
            [$leadFieldId, $contactFieldId]
        );
        echo "Created Lead → Contact mapping: Leads.company_id → Contacts.company_id\n";
    } else {
        echo "Skipping mapping because one of the fields is missing\n";
    }

    echo "\nDONE.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
