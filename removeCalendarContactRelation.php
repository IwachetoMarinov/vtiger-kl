<?php
// ini_set('display_errors', 1); error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once('vtlib/Vtiger/Module.php');
include_once 'config.inc.php';

global $adb;

$calendarModule = Vtiger_Module::getInstance('Calendar');
$contactsModule = Vtiger_Module::getInstance('Contacts');


if ($calendarModule && $contactsModule) {
    $calendarModule->unsetRelatedList(
        $contactsModule,
        'Contacts',
        'get_contacts'
    );
    echo "✅ Contact Name relation removed from Calendar.";
} else {
    echo "❌ Calendar or Contacts module not found.";
}

if ($calendarModule) {
    $field = Vtiger_Field::getInstance('location', $calendarModule);
    if ($field) {
        $field->delete();
        echo "✅ Location field removed from Calendar.";
    } else {
        echo "⚠️ Location field not found.";
    }
} else {
    echo "❌ Calendar module not found.";
}


exit;



/**
 * Ensure DB column exists
 */
$col = $adb->pquery(
    "SHOW COLUMNS FROM vtiger_activity LIKE 'assigned_by'",
    []
);
if ($adb->num_rows($col) === 0) {
    $adb->pquery(
        "ALTER TABLE vtiger_activity ADD COLUMN assigned_by INT(11)",
        []
    );
    echo "✅ DB column vtiger_activity.assigned_by created<br>";
} else {
    echo "ℹ️ DB column vtiger_activity.assigned_by already exists<br>";
}

/**
 * Add field to module
 */
function addAssignedBy($moduleName, $blockLabel)
{
    $module = Vtiger_Module::getInstance($moduleName);
    if (!$module) {
        echo "❌ Module not found: $moduleName<br>";
        return;
    }

    // Field already exists?
    if (Vtiger_Field::getInstance('assigned_by', $module)) {
        echo "ℹ️ assigned_by already exists in $moduleName<br>";
        return;
    }

    $block = Vtiger_Block::getInstance($blockLabel, $module);
    if (!$block) {
        echo "❌ Block $blockLabel not found in $moduleName<br>";
        return;
    }

    $field = new Vtiger_Field();
    $field->name = 'assigned_by';
    $field->label = 'Assigned By';
    $field->table = 'vtiger_activity';
    $field->column = 'assigned_by';
    $field->uitype = 52;      // Users (Groups allowed)
    $field->typeofdata = 'V~O';
    $field->presence = 0;

    $block->addField($field);

    // Allow Groups selection
    $field->setRelatedModules(['Users', 'Groups']);

    echo "✅ Assigned By added to $moduleName ($blockLabel)<br>";
}

// Add to Events (your screenshot)
addAssignedBy('Events', 'LBL_EVENT_INFORMATION');

// Optional: add to Tasks
addAssignedBy('Calendar', 'LBL_TASK_INFORMATION');

echo "<br>Done.";
