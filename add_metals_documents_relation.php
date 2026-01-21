<?php
// Composer dependencies (Monolog, etc.)
require_once __DIR__ . '/vendor/autoload.php';
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
