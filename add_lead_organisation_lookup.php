<?php
/* Creates a reference field on Leads pointing to Accounts (Organisations) */

require_once __DIR__ . '/vendor/autoload.php';
include_once 'vtlib/Vtiger/Module.php';

$leads = Vtiger_Module::getInstance('Leads');
$accounts = Vtiger_Module::getInstance('Accounts'); // Organisations

if (!$leads) { echo "ERROR: Leads module not found\n"; exit; }
if (!$accounts) { echo "ERROR: Accounts module not found\n"; exit; }

$block = Vtiger_Block::getInstance('LBL_LEAD_INFORMATION', $leads);
if (!$block) {
  $blocks = Vtiger_Block::getAllForModule($leads);
  $block = $blocks ? $blocks[0] : null;
}
if (!$block) { echo "ERROR: No block found for Leads\n"; exit; }

$fieldName = 'organisation_id'; // use a custom-safe name
$existing = Vtiger_Field::getInstance($fieldName, $leads);
if ($existing) { echo "OK: Field already exists: $fieldName\n"; exit; }

$field = new Vtiger_Field();
$field->name = $fieldName;
$field->label = 'Organisation';
$field->table = 'vtiger_leaddetails';
$field->column = $fieldName;
$field->columntype = 'INT(11)';
$field->uitype = 10;          // Reference
$field->typeofdata = 'V~O';   // Optional
$field->summaryfield = 1;

$block->addField($field);
$field->setRelatedModules(['Accounts']);

echo "OK: Added Leads.Organisation lookup to Accounts ($fieldName)\n";
