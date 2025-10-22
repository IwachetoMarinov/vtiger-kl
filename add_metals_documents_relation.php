<?php
// Composer dependencies (Monolog, etc.)
require_once __DIR__ . '/vendor/autoload.php';
// include_once 'config.inc.php';
// include_once 'include/utils/utils.php';
// include_once 'vtlib/Vtiger/Module.php';
// include_once 'modules/Users/Users.php';

// global $current_user;
// $current_user = Users::getActiveAdminUser();

// $metals = Vtiger_Module::getInstance('Metals');
// $documents = Vtiger_Module::getInstance('Documents');

// if ($metals && $documents) {
//     $metals->setRelatedList($documents, 'Documents', ['ADD','SELECT']);
//     echo "✅ Metals ↔ Documents relation recreated successfully.";
// } else {
//     echo "❌ Could not load module instances.";
// }

include_once 'vtlib/Vtiger/Module.php';

$moduleInstance = Vtiger_Module::getInstance('Contacts');
$field = new Vtiger_Field();

$field->name = 'introducer_id';
$field->label = 'Introducer';
$field->uitype = 10; // Reference type
$field->column = 'introducer_id';
$field->columntype = 'INT(11)';
$field->typeofdata = 'V~O';
$field->displaytype = 1;
$field->quickcreate = 1;

$blockInstance = Vtiger_Block::getInstance('Introducer Details', $moduleInstance);
if (!$blockInstance) {
    $blockInstance = new Vtiger_Block();
    $blockInstance->label = 'Introducer Details';
    $moduleInstance->addBlock($blockInstance);
}

$blockInstance->addField($field);
$field->setRelatedModules(['Contacts']);

echo "✅ One-to-One / Many-to-One Introducer reference field added successfully.";
