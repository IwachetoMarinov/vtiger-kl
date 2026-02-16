<?php
/* Creates a reference (lookup) field on Potentials (Opportunities) pointing to Leads */

require_once __DIR__ . '/vendor/autoload.php';
include_once 'vtlib/Vtiger/Module.php';

$potentials = Vtiger_Module::getInstance('Potentials'); // Opportunities
$leads      = Vtiger_Module::getInstance('Leads');

if (!$potentials) { echo "ERROR: Potentials (Opportunities) module not found\n"; exit; }
if (!$leads)      { echo "ERROR: Leads module not found\n"; exit; }

// Try to attach to the main “Opportunity Information” block.
// Different vtiger builds may have slightly different block labels.
$block =
  Vtiger_Block::getInstance('LBL_OPPORTUNITY_INFORMATION', $potentials)
  ?: Vtiger_Block::getInstance('LBL_POTENTIAL_INFORMATION', $potentials);

if (!$block) {
  $blocks = Vtiger_Block::getAllForModule($potentials);
  $block = $blocks ? $blocks[0] : null;
}
if (!$block) { echo "ERROR: No block found for Potentials\n"; exit; }

$fieldName = 'lead_id'; // choose a "custom-safe" name (must not collide with existing columns)
$existing = Vtiger_Field::getInstance($fieldName, $potentials);
if ($existing) { echo "OK: Field already exists: $fieldName\n"; exit; }

/**
 * IMPORTANT:
 * - If you set table=vtiger_potential, vtlib will add the column to that table (if supported).
 * - Some setups prefer custom fields table: vtiger_potentialcf
 *   but then you must use a column name like cf_XXXX or create a proper custom field.
 *
 * Your old script uses a base table (vtiger_leaddetails). We'll mirror that approach:
 * Use vtiger_potential for core table, because it already stores most opportunity fields.
 */
$field = new Vtiger_Field();
$field->name        = $fieldName;
$field->label       = 'Lead';
$field->table       = 'vtiger_potential';
$field->column      = $fieldName;
$field->columntype  = 'INT(11)';
$field->uitype      = 10;        // Reference (lookup)
$field->typeofdata  = 'V~O';     // Optional
$field->summaryfield = 1;

$block->addField($field);
$field->setRelatedModules(['Leads']);

echo "OK: Added Potentials.Lead lookup to Leads ($fieldName)\n";
