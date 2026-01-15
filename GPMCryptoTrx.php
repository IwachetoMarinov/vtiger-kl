<?php
$Vtiger_Utils_Log = true;
require_once __DIR__ . '/vendor/autoload.php';
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

$module = Vtiger_Module::getInstance('GPMCryptoTrx');

if (!$module) {
    $module = new Vtiger_Module();
    $module->name = 'GPMCryptoTrx';
    $module->parent = 'Tools';
    $module->save();
    $module->initTables();
}

$TransactionDetails = Vtiger_Block::getInstance('LBL_TRANSACTION_DETAILS', $module);
if (!$TransactionDetails) {
    $TransactionDetails = new Vtiger_Block();
    $TransactionDetails->label = 'LBL_TRANSACTION_DETAILS';
    $module->addBlock($TransactionDetails);
}

// Client (Contact lookup)
$clientField = Vtiger_Field::getInstance('contact_id', $module);
if (!$clientField) {
    $clientField = new Vtiger_Field();
    $clientField->name = 'contact_id';
    $clientField->label = 'Client';
    $clientField->table = $module->basetable;
    $clientField->column = 'contact_id';
    $clientField->columntype = 'INT(11)';
    $clientField->uitype = 10;
    $clientField->typeofdata = 'V~M';
    $TransactionDetails->addField($clientField);
    $clientField->setRelatedModules(['Contacts']);
}

// Wallet Address
$walletAddessField = Vtiger_Field::getInstance('wallet_address', $module);
if (!$walletAddessField) {
    $walletAddessField = new Vtiger_Field();
    $walletAddessField->name = 'wallet_address';
    $walletAddessField->label = 'Wallet Address';
    $walletAddessField->table = $module->basetable;
    $walletAddessField->column = 'wallet_address';
    $walletAddessField->columntype = 'VARCHAR(100)';
    $walletAddessField->uitype = 1;
    $walletAddessField->typeofdata = 'V~M';
    $TransactionDetails->addField($walletAddessField);
    $module->setEntityIdentifier($walletAddessField);
}

// Asset (Picklist)
$assetField = Vtiger_Field::getInstance('asset', $module);
if (!$assetField) {
    $assetField = new Vtiger_Field();
    $assetField->name = 'asset';
    $assetField->label = 'Asset';
    $assetField->table = $module->basetable;
    $assetField->column = 'asset';
    $assetField->columntype = 'VARCHAR(255)';
    $assetField->uitype = 15;
    $assetField->typeofdata = 'V~M';
    $TransactionDetails->addField($assetField);
    $assetField->setPicklistValues(['BTC', 'Ether', 'LTC']);
}

// Transaction Date
$trxDate = Vtiger_Field::getInstance('trx_date', $module);
if (!$trxDate) {
    $trxDate = new Vtiger_Field();
    $trxDate->name = 'trx_date';
    $trxDate->label = 'Date';
    $trxDate->table = $module->basetable;
    $trxDate->column = 'trx_date';
    $trxDate->columntype = 'DATE';
    $trxDate->uitype = 5;
    $trxDate->typeofdata = 'D~M';
    $TransactionDetails->addField($trxDate);
}

// Assigned To
$assignedTo = Vtiger_Field::getInstance('assigned_user_id', $module);
if (!$assignedTo) {
    $assignedTo = new Vtiger_Field();
    $assignedTo->name = 'assigned_user_id';
    $assignedTo->label = 'Assigned To';
    $assignedTo->table = 'vtiger_crmentity';
    $assignedTo->column = 'smownerid';
    $assignedTo->uitype = 53;
    $assignedTo->typeofdata = 'V~M';
    $TransactionDetails->addField($assignedTo);
}

// Created Time
$createdTime = Vtiger_Field::getInstance('createdtime', $module);
if (!$createdTime) {
    $createdTime = new Vtiger_Field();
    $createdTime->name = 'createdtime';
    $createdTime->label = 'Created Time';
    $createdTime->table = 'vtiger_crmentity';
    $createdTime->column = 'createdtime';
    $createdTime->uitype = 70;
    $createdTime->typeofdata = 'T~O';
    $createdTime->displaytype = 2;
    $TransactionDetails->addField($createdTime);
}

// Modified Time
$modifiedTime = Vtiger_Field::getInstance('modifiedtime', $module);
if (!$modifiedTime) {
    $modifiedTime = new Vtiger_Field();
    $modifiedTime->name = 'modifiedtime';
    $modifiedTime->label = 'Modified Time';
    $modifiedTime->table = 'vtiger_crmentity';
    $modifiedTime->column = 'modifiedtime';
    $modifiedTime->uitype = 70;
    $modifiedTime->typeofdata = 'T~O';
    $modifiedTime->displaytype = 2;
    $TransactionDetails->addField($modifiedTime);
}

// Default filter
$filter1 = Vtiger_Filter::getInstance('All', $module);
if (!$filter1) {
    $filter1 = new Vtiger_Filter();
    $filter1->name = 'All';
    $filter1->isdefault = true;
    $module->addFilter($filter1);
    $filter1->addField($clientField)
            ->addField($walletAddessField, 1)
            ->addField($assetField, 2)
            ->addField($assignedTo, 3)
            ->addField($createdTime, 4)
            ->addField($modifiedTime, 5);
}

// Relation: Contacts → GPMCryptoTrx
$contactsModule = Vtiger_Module::getInstance('Contacts');
$contactsModule->setRelatedList($module, 'Crypto Transactions', ['ADD'], 'get_dependents_list');

// Sharing and webservice
$module->setDefaultSharing('Public_ReadOnly');
$module->initWebservice();

echo "Module GPMCryptoTrx created successfully.\n";
