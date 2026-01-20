<?php

$Vtiger_Utils_Log = true;
require_once __DIR__ . '/vendor/autoload.php';
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

$module = Vtiger_Module::getInstance('BankAccount');

if (!$module) {
    $module = new Vtiger_Module();
    $module->name = 'BankAccount';
    $module->parent = 'Support';

    $module->save();
    $module->initTables();
    $module->initWebservice();
}

//     info block
$infoBlock = Vtiger_Block::getInstance('Bank Details', $module);
if (!$infoBlock) {
    $infoBlock = new Vtiger_Block();
    $infoBlock->label = 'Bank Details';
    $module->addBlock($infoBlock);
}

$bankNameField = Vtiger_Field::getInstance('bank_name', $module);
if (!$bankNameField) {
    $bankNameField = new Vtiger_Field();
    $bankNameField->name = 'bank_name';
    $bankNameField->label = 'Beneficiary Bank';
    $bankNameField->table = $module->basetable;
    $bankNameField->column = 'bank_name';
    $bankNameField->columntype = 'VARCHAR(100)';
    $bankNameField->uitype = 1;
    $bankNameField->typeofdata = 'V~M';
    $infoBlock->addField($bankNameField);
    /** Creates the field and adds to block */
    $module->setEntityIdentifier($bankNameField);
}

$bankAliasNameField = Vtiger_Field::getInstance('bank_alias_name', $module);
if (!$bankAliasNameField) {
    $bankAliasNameField = new Vtiger_Field();
    $bankAliasNameField->name = 'bank_alias_name';
    $bankAliasNameField->label = 'Bank Alias Name';
    $bankAliasNameField->table = $module->basetable;
    $bankAliasNameField->column = 'bank_alias_name';
    $bankAliasNameField->columntype = 'VARCHAR(100)';
    $bankAliasNameField->uitype = 1;
    $bankAliasNameField->typeofdata = 'V~M';
    $infoBlock->addField($bankAliasNameField);
}

$bankAddressField = Vtiger_Field::getInstance('bank_address', $module);
if (!$bankAddressField) {
    $bankAddressField = new Vtiger_Field();
    $bankAddressField->name = 'bank_address';
    $bankAddressField->label = 'Beneficiary Bank Address';
    $bankAddressField->table = $module->basetable;
    $bankAddressField->column = 'bank_address';
    $bankAddressField->columntype = 'VARCHAR(250)';
    $bankAddressField->uitype = 21;
    $bankAddressField->typeofdata = 'V~M';
    $infoBlock->addField($bankAddressField);
}



$swiftCodeField = Vtiger_Field::getInstance('swift_code', $module);
if (!$swiftCodeField) {
    $swiftCodeField = new Vtiger_Field();
    $swiftCodeField->name = 'swift_code';
    $swiftCodeField->label = 'SWIFT Code';
    $swiftCodeField->table = $module->basetable;
    $swiftCodeField->column = 'swift_code';
    $swiftCodeField->columntype = 'VARCHAR(16)';
    $swiftCodeField->uitype = 1;
    $swiftCodeField->typeofdata = 'V~M';
    $infoBlock->addField($swiftCodeField);
}

$bankCodeField = Vtiger_Field::getInstance('bank_code', $module);
if (!$bankCodeField) {
    $bankCodeField = new Vtiger_Field();
    $bankCodeField->name = 'bank_code';
    $bankCodeField->label = 'Bank Code';
    $bankCodeField->table = $module->basetable;
    $bankCodeField->column = 'bank_code';
    $bankCodeField->columntype = 'VARCHAR(16)';
    $bankCodeField->uitype = 1;
    $bankCodeField->typeofdata = 'V~M';
    $infoBlock->addField($bankCodeField);
}

$branchCodeField = Vtiger_Field::getInstance('branch_code', $module);
if (!$branchCodeField) {
    $branchCodeField = new Vtiger_Field();
    $branchCodeField->name = 'branch_code';
    $branchCodeField->label = 'Branch Code';
    $branchCodeField->table = $module->basetable;
    $branchCodeField->column = 'branch_code';
    $branchCodeField->columntype = 'VARCHAR(16)';
    $branchCodeField->uitype = 1;
    $branchCodeField->typeofdata = 'V~M';
    $infoBlock->addField($branchCodeField);
}


$bankAccountField = Vtiger_Field::getInstance('account_no', $module);
if (!$bankAccountField) {
    $bankAccountField = new Vtiger_Field();
    $bankAccountField->name = 'account_no';
    $bankAccountField->label = 'Beneficiary Account No';
    $bankAccountField->table = $module->basetable;
    $bankAccountField->column = 'account_no';
    $bankAccountField->columntype = 'VARCHAR(100)';
    $bankAccountField->uitype = 1;
    $bankAccountField->typeofdata = 'V~M';
    $infoBlock->addField($bankAccountField);
}

$accountCurrencyField = Vtiger_Field::getInstance('account_currency', $module);
if (!$accountCurrencyField) {
    $accountCurrencyField = new Vtiger_Field();
    $accountCurrencyField->name = 'account_currency';
    $accountCurrencyField->label = 'Account Currency';
    $accountCurrencyField->table = $module->basetable;
    $accountCurrencyField->column = 'account_currency';
    $accountCurrencyField->columntype = 'VARCHAR(12)';
    $accountCurrencyField->uitype = 16;
    $accountCurrencyField->typeofdata = 'V~M';
    $infoBlock->addField($accountCurrencyField);
    $accountCurrencyField->setPicklistValues(array('USD', 'EUR', 'GBP', 'SGD', 'CHF', 'MYR', 'HKD'));
}

$beneficiaryNameField = Vtiger_Field::getInstance('beneficiary_name', $module);
if (!$beneficiaryNameField) {
    $beneficiaryNameField = new Vtiger_Field();
    $beneficiaryNameField->name = 'beneficiary_name';
    $beneficiaryNameField->label = 'Beneficiary Name';
    $beneficiaryNameField->table = $module->basetable;
    $beneficiaryNameField->column = 'beneficiary_name';
    $beneficiaryNameField->columntype = 'VARCHAR(100)';
    $beneficiaryNameField->uitype = 1;
    $beneficiaryNameField->typeofdata = 'V~M';
    $infoBlock->addField($beneficiaryNameField);
}

$beneficiaryAddressField = Vtiger_Field::getInstance('beneficiary_address', $module);
if (!$beneficiaryAddressField) {
    $beneficiaryAddressField = new Vtiger_Field();
    $beneficiaryAddressField->name = 'beneficiary_address';
    $beneficiaryAddressField->label = 'Beneficiary Address';
    $beneficiaryAddressField->table = $module->basetable;
    $beneficiaryAddressField->column = 'beneficiary_address';
    $beneficiaryAddressField->columntype = 'VARCHAR(250)';
    $beneficiaryAddressField->uitype = 21;
    $beneficiaryAddressField->typeofdata = 'V~M';
    $infoBlock->addField($beneficiaryAddressField);
}

$relatedEntity = Vtiger_Field::getInstance('related_entity', $module);
if (!$relatedEntity) {
    $relatedEntity = new Vtiger_Field();
    $relatedEntity->name = 'related_entity';
    $relatedEntity->label = 'Related Entity';
    $relatedEntity->column = 'related_entity';
    $relatedEntity->columntype = 'VARCHAR(12)';
    $relatedEntity->uitype = 16;
    $relatedEntity->typeofdata = 'V~M';
    $infoBlock->addField($relatedEntity);
    $relatedEntity->setPicklistValues(array('GPM', 'GPS', 'GPM-DB', 'GPM-HK'));
}
$ERPAccountField = Vtiger_Field::getInstance('erp_account_no', $module);
if (!$ERPAccountField) {
    $ERPAccountField = new Vtiger_Field();
    $ERPAccountField->name = 'erp_account_no';
    $ERPAccountField->label = 'OROSoft Account No';
    $ERPAccountField->table = $module->basetable;
    $ERPAccountField->column = 'erp_account_no';
    $ERPAccountField->columntype = 'VARCHAR(100)';
    $ERPAccountField->uitype = 1;
    $ERPAccountField->typeofdata = 'V~M';
    $infoBlock->addField($ERPAccountField);
}
/*
 * Field For Assigned To
 */
$assignedToField = Vtiger_Field::getInstance('assigned_user_id', $module);
if (!$assignedToField) {
    $assignedToField = new Vtiger_Field();
    $assignedToField->name = 'assigned_user_id';
    $assignedToField->label = 'Assigned To';
    $assignedToField->table = 'vtiger_crmentity';
    $assignedToField->column = 'smownerid';
    $assignedToField->uitype = 53;
    $assignedToField->typeofdata = 'V~M';
    $infoBlock->addField($assignedToField);
}

$createdTimeField = Vtiger_Field::getInstance('createdtime', $module);
if (!$createdTimeField) {
    $createdTimeField = new Vtiger_Field();
    $createdTimeField->name = 'createdtime';
    $createdTimeField->label = 'Created Time';
    $createdTimeField->table = 'vtiger_crmentity';
    $createdTimeField->column = 'createdtime';
    $createdTimeField->uitype = 70;
    $createdTimeField->typeofdata = 'T~O';
    $createdTimeField->displaytype = 2;
    $infoBlock->addField($createdTimeField);
}
/*
 * Modified Time
 */
$modifiedTimeField = Vtiger_Field::getInstance('modifiedtime', $module);
if (!$modifiedTimeField) {
    $modifiedTimeField = new Vtiger_Field();
    $modifiedTimeField->name = 'modifiedtime';
    $modifiedTimeField->label = 'Modified Time';
    $modifiedTimeField->table = 'vtiger_crmentity';
    $modifiedTimeField->column = 'modifiedtime';
    $modifiedTimeField->uitype = 70;
    $modifiedTimeField->typeofdata = 'T~O';
    $modifiedTimeField->displaytype = 2;
    $infoBlock->addField($modifiedTimeField);
}



$IntermediaryBlock = Vtiger_Block::getInstance('Intermediary Bank Details', $module);
if (!$IntermediaryBlock) {
    $IntermediaryBlock = new Vtiger_Block();
    $IntermediaryBlock->label = 'Intermediary Bank Details';
    $module->addBlock($IntermediaryBlock);
}

$intermediaryBankField = Vtiger_Field::getInstance('intermediary_bank', $module);
if (!$intermediaryBankField) {
    $intermediaryBankField = new Vtiger_Field();
    $intermediaryBankField->name = 'intermediary_bank';
    $intermediaryBankField->label = 'Intermediary Bank';
    $intermediaryBankField->table = $module->basetable;
    $intermediaryBankField->column = 'intermediary_bank';
    $intermediaryBankField->columntype = 'VARCHAR(100)';
    $intermediaryBankField->uitype = 1;
    $intermediaryBankField->typeofdata = 'V~O';
    $IntermediaryBlock->addField($intermediaryBankField);
}

$intermediarySwiftCodeField = Vtiger_Field::getInstance('intermediary_swift_code', $module);
if (!$intermediarySwiftCodeField) {
    $intermediarySwiftCodeField = new Vtiger_Field();
    $intermediarySwiftCodeField->name = 'intermediary_swift_code';
    $intermediarySwiftCodeField->label = 'SWIFT Code';
    $intermediarySwiftCodeField->table = $module->basetable;
    $intermediarySwiftCodeField->column = 'intermediary_swift_code';
    $intermediarySwiftCodeField->columntype = 'VARCHAR(16)';
    $intermediarySwiftCodeField->uitype = 1;
    $intermediarySwiftCodeField->typeofdata = 'V~O';
    $IntermediaryBlock->addField($intermediarySwiftCodeField);
}

$allFilter = Vtiger_Filter::getInstance('All', $module);
if (!$allFilter) {
    $allFilter = new Vtiger_Filter();
    $allFilter->name = 'All';
    $allFilter->isdefault = true;
    $module->addFilter($allFilter);
    // Add fields to the filter created
    $allFilter->addField($bankNameField)
        ->addField($bankAliasNameField, 1)
        ->addField($bankAddressField, 2)
        ->addField($swiftCodeField, 3)
        ->addField($bankCodeField, 4)
        ->addField($branchCodeField, 5)
        ->addField($bankAccountField, 6)
        ->addField($accountCurrencyField, 7)
        ->addField($relatedEntity, 8);
}

/** Set sharing access of this module */
$module->setDefaultSharing('Public_ReadOnly');

$module->enableTools(array('Merge', 'Export', 'Import'));

global $adb;
$adb->pquery(
    'DELETE FROM vtiger_app2tab WHERE tabid=? AND appname=?',
    array(getTabid('BankAccount'), 'Support')
);
$adb->pquery(
    'INSERT INTO vtiger_app2tab(tabid,appname,sequence,visible) values(?,?,?,?)',
    array(getTabid('BankAccount'), 'Support', 30, 1)
);


echo "BankAccount Module Installed Successfully";
