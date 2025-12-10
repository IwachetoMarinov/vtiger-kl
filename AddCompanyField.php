<?php

require_once __DIR__ . '/vendor/autoload.php';

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

echo "<pre>";

try {

    $module = Vtiger_Module::getInstance('Contacts');
    if (!$module) {
        echo "Contacts module not found.\n";
        exit;
    }

    // ------------------------------
    // 1. DETECT CORRECT BLOCK
    // ------------------------------
    $possibleBlocks = [
        'LBL_CONTACT_INFORMATION',
        'Contact Information',
        'Basic Information',
        'LBL_BASIC_INFORMATION'
    ];

    $block = null;

    foreach ($possibleBlocks as $label) {
        $test = Vtiger_Block::getInstance($label, $module);
        if ($test) {
            $block = $test;
            echo "Using block: $label\n";
            break;
        }
    }

    // Create block if not found (rare)
    if (!$block) {
        $block = new Vtiger_Block();
        $block->label = 'LBL_CONTACT_INFORMATION';
        $module->addBlock($block);
        echo "Created block LBL_CONTACT_INFORMATION\n";
    }

    // ------------------------------
    // 2. CHECK IF FIELD ALREADY EXISTS
    // ------------------------------
    $field = Vtiger_Field::getInstance('company_id', $module);

    if ($field) {
        echo "Field company_id already exists in Contacts.\n";
        exit;
    }

    // ------------------------------
    // 3. CREATE THE RELATION FIELD
    // ------------------------------
    $field = new Vtiger_Field();
    $field->name        = 'company_id';
    $field->label       = 'Company';
    $field->table       = $module->basetable;
    $field->column      = 'company_id';
    $field->uitype      = 10;
    $field->typeofdata  = 'V~O';
    $field->columntype  = 'INT(11)';

    $block->addField($field);

    // RELATE TO GPMCompany MODULE
    $field->setRelatedModules(['GPMCompany']);

    echo "Successfully added company_id field to Contacts module.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
