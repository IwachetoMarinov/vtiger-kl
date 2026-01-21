<?php
/* Run once: creates a Leads reference field "Introducer" pointing to Contacts */

require_once __DIR__ . '/vendor/autoload.php';
include_once 'vtlib/Vtiger/Module.php';

$leads = Vtiger_Module::getInstance('Leads');
$contacts = Vtiger_Module::getInstance('Contacts');

if (!$leads) {
    echo "ERROR: Leads module not found\n";
    exit;
}
if (!$contacts) {
    echo "ERROR: Contacts module not found\n";
    exit;
}

/**
 * Choose a block to place the field into.
 * Prefer the standard Lead info block; fallback to first available block.
 */
$block = Vtiger_Block::getInstance('LBL_LEAD_INFORMATION', $leads);
if (!$block) {
    $blocks = Vtiger_Block::getAllForModule($leads);
    $block = $blocks ? $blocks[0] : null;
}
if (!$block) {
    echo "ERROR: No block found for Leads\n";
    exit;
}

/**
 * Avoid duplicates: if field already exists, stop.
 */
$fieldName = 'introducer_contact_id';
$existing = Vtiger_Field::getInstance($fieldName, $leads);
if ($existing) {
    echo "OK: Field already exists: {$fieldName}\n";
    exit;
}

/**
 * Create a uitype 10 reference field on vtiger_leaddetails.
 * NOTE: vtiger_leaddetails may already exist; addField will add the column if needed.
 */
$field = new Vtiger_Field();
$field->name = $fieldName;
$field->label = 'Introducer';
$field->table = 'vtiger_leaddetails';
$field->column = $fieldName;
$field->columntype = 'INT(11)';
$field->uitype = 10;          // Reference
$field->typeofdata = 'V~O';   // Optional
$field->summaryfield = 1;

$block->addField($field);

// Link the reference to Contacts
$field->setRelatedModules(['Contacts']);

echo "OK: Created Leads.Introducer reference field to Contacts ({$fieldName})\n";
