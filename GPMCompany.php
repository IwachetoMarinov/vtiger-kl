<?php

$Vtiger_Utils_Log = true;
require_once __DIR__ . '/vendor/autoload.php';
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');


$module = Vtiger_Module::getInstance('GPMCompany');

if (!$module) {
    $module = new Vtiger_Module();
    $module->name = 'GPMCompany';
    $module->parent = 'Support';
    $module->save();
    $module->initTables();
    $module->initWebservice();
}

$infoBlock = Vtiger_Block::getInstance('Company Details', $module);
if (!$infoBlock) {
    $infoBlock = new Vtiger_Block();
    $infoBlock->label = 'Company Details';
    $module->addBlock($infoBlock);
}

$CompanyNameField = Vtiger_Field::getInstance('company_name', $module);
if (!$CompanyNameField) {
    $CompanyNameField = new Vtiger_Field();
    $CompanyNameField->name = 'company_name';
    $CompanyNameField->label = 'Company name';
    $CompanyNameField->table = $module->basetable;
    $CompanyNameField->column = 'company_name';
    $CompanyNameField->columntype = 'VARCHAR(256)';
    $CompanyNameField->uitype = 1;
    $CompanyNameField->typeofdata = 'V~M';
    $infoBlock->addField($CompanyNameField);
    $module->setEntityIdentifier($CompanyNameField);
}

$OROSoftCodeField = Vtiger_Field::getInstance('company_orosoft_code', $module);
if (!$OROSoftCodeField) {
    $OROSoftCodeField = new Vtiger_Field();
    $OROSoftCodeField->name = 'company_orosoft_code';
    $OROSoftCodeField->label = 'OROSoft Code';
    $OROSoftCodeField->table = $module->basetable;
    $OROSoftCodeField->column = 'company_orosoft_code';
    $OROSoftCodeField->columntype = 'VARCHAR(256)';
    $OROSoftCodeField->uitype = 1;
    $OROSoftCodeField->typeofdata = 'V~M';
    $infoBlock->addField($OROSoftCodeField);
}

$GSTNOField = Vtiger_Field::getInstance('company_gst_no', $module);
if (!$GSTNOField) {
    $GSTNOField = new Vtiger_Field();
    $GSTNOField->name = 'company_gst_no';
    $GSTNOField->label = 'GST Number';
    $GSTNOField->table = $module->basetable;
    $GSTNOField->column = 'company_gst_no';
    $GSTNOField->columntype = 'VARCHAR(256)';
    $GSTNOField->uitype = 1;
    $GSTNOField->typeofdata = 'V~O';
    $infoBlock->addField($GSTNOField);
}

$CompanyAddressField = Vtiger_Field::getInstance('company_address', $module);
if (!$CompanyAddressField) {
    $CompanyAddressField = new Vtiger_Field();
    $CompanyAddressField->name = 'company_address';
    $CompanyAddressField->label = 'Company Address';
    $CompanyAddressField->table = $module->basetable;
    $CompanyAddressField->column = 'company_address';
    $CompanyAddressField->columntype = 'VARCHAR(512)';
    $CompanyAddressField->uitype = 21;
    $CompanyAddressField->typeofdata = 'V~M';
    $infoBlock->addField($CompanyAddressField);
}

$CompanyPhoneField = Vtiger_Field::getInstance('company_phone', $module);
if (!$CompanyPhoneField) {
    $CompanyPhoneField = new Vtiger_Field();
    $CompanyPhoneField->name = 'company_phone';
    $CompanyPhoneField->label = 'Company Phone';
    $CompanyPhoneField->table = $module->basetable;
    $CompanyPhoneField->column = 'company_phone';
    $CompanyPhoneField->columntype = 'VARCHAR(64)';
    $CompanyPhoneField->uitype = 1;
    $CompanyPhoneField->typeofdata = 'V~M';
    $infoBlock->addField($CompanyPhoneField);
}
$CompanyFaxField = Vtiger_Field::getInstance('company_fax', $module);
if (!$CompanyFaxField) {
    $CompanyFaxField = new Vtiger_Field();
    $CompanyFaxField->name = 'company_fax';
    $CompanyFaxField->label = 'Company Fax';
    $CompanyFaxField->table = $module->basetable;
    $CompanyFaxField->column = 'company_fax';
    $CompanyFaxField->columntype = 'VARCHAR(64)';
    $CompanyFaxField->uitype = 1;
    $CompanyFaxField->typeofdata = 'V~O';
    $infoBlock->addField($CompanyFaxField);
}
$CompanyWebsiteField = Vtiger_Field::getInstance('company_website', $module);
if (!$CompanyWebsiteField) {
    $CompanyWebsiteField = new Vtiger_Field();
    $CompanyWebsiteField->name = 'company_website';
    $CompanyWebsiteField->label = 'Company Website';
    $CompanyWebsiteField->table = $module->basetable;
    $CompanyWebsiteField->column = 'company_website';
    $CompanyWebsiteField->columntype = 'VARCHAR(128)';
    $CompanyWebsiteField->uitype = 1;
    $CompanyWebsiteField->typeofdata = 'V~M';
    $infoBlock->addField($CompanyWebsiteField);
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

$allFilter = Vtiger_Filter::getInstance('All', $module);
if (!$allFilter) {
    $allFilter = new Vtiger_Filter();
    $allFilter->name = 'All';
    $allFilter->isdefault = true;
    $module->addFilter($allFilter);
    // Add fields to the filter created
    $allFilter->addField($CompanyNameField)
        ->addField($OROSoftCodeField, 1)
        ->addField($CompanyAddressField, 2)
        ->addField($CompanyPhoneField, 3)
        ->addField($CompanyFaxField, 4)
        ->addField($CompanyWebsiteField, 5);
}

/** Set sharing access of this module */
$module->setDefaultSharing('Public_ReadOnly');
global $adb;

$seqid = $adb->getUniqueID('vtiger_settings_field');

$adb->pquery('INSERT INTO vtiger_settings_field(fieldid,blockid,name,sequence,description,linkto) values(?,?,?,?,?,?)', array($seqid, 8, 'Companies', 10, 'Modules to Configure Company information', 'index.php?module=GPMCompany&view=List'));
$adb->pquery("delete from vtiger_field  where fieldname like ?", array('footer_company%'));

echo "Module GPMCompany created successfully.\n";

echo "Please log out and log in again to see the changes.\n";
