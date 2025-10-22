<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$Vtiger_Utils_Log = true;

// Composer dependencies (Monolog, etc.)
require_once __DIR__ . '/vendor/autoload.php';

// vtiger core
require_once 'config.inc.php';
require_once 'include/utils/utils.php';
require_once 'vtlib/Vtiger/Module.php';
require_once 'vtlib/Vtiger/Package.php';
require_once 'include/Webservices/Utils.php';

// set current user = admin
global $current_user;
require_once 'modules/Users/Users.php';
$current_user = Users::getActiveAdminUser();

echo "▶ Bootstrapped OK<br>";

// check if already exists
$moduleInstance = Vtiger_Module::getInstance('Metals');
if ($moduleInstance) {
    echo "⚠ Metals module already exists!<br>";
    exit;
}

// create module
$module = new Vtiger_Module();
$module->name   = 'Metals';   // internal name
$module->label  = 'Assets';   // display label
$module->parent = 'INVENTORY';
$module->save();
$module->initTables();

echo "✅ Metals (Assets) module base created<br>";

// create block
$block = new Vtiger_Block();
$block->label = 'LBL_METALS_INFORMATION';
$module->addBlock($block);

// fields

// (1) Name
$field1 = new Vtiger_Field();
$field1->name = 'name';
$field1->label = 'Name';
$field1->uitype = 2; // text
$field1->column = $field1->name;
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~M';
$block->addField($field1);
$module->setEntityIdentifier($field1);

// (2) FineOz
$field2 = new Vtiger_Field();
$field2->name = 'fineoz';
$field2->label = 'FineOz';
$field2->uitype = 7; // number
$field2->column = $field2->name;
$field2->columntype = 'DECIMAL(10,2)';
$field2->typeofdata = 'NN~O';
$block->addField($field2);

// (3) Created Time (readonly)
$field3 = new Vtiger_Field();
$field3->name = 'createdtime';
$field3->label = 'Created Time';
$field3->uitype = 70; // date/time
$field3->column = $field3->name;
$field3->columntype = 'DATETIME';
$field3->typeofdata = 'DT~O';
$field3->displaytype = 2; // read-only
$block->addField($field3);

// (4) Type (Picklist)
$field4 = new Vtiger_Field();
$field4->name = 'metal_type';
$field4->label = 'Type';
$field4->uitype = 15; // picklist
$field4->column = $field4->name;
$field4->columntype = 'VARCHAR(50)';
$field4->typeofdata = 'V~M';
$block->addField($field4);
$field4->setPicklistValues(array('CRYPTO','XAU','XAG','XPT','XPD','XRH'));

// (5) Assigned To
$field5 = new Vtiger_Field();
$field5->name = 'assigned_user_id';
$field5->label = 'Assigned To';
$field5->uitype = 53; // assigned to (user/group)
$field5->column = $field5->name;
$field5->columntype = 'INT(11)';
$field5->typeofdata = 'V~M';
$block->addField($field5);

// default filter
$filter = new Vtiger_Filter();
$filter->name = 'All';
$filter->isdefault = true;
$module->addFilter($filter);
$filter->addField($field1, 1);
$filter->addField($field4, 2);
$filter->addField($field2, 3);

// sharing + webservice
$module->setDefaultSharing('Public');
$module->initWebservice();

echo "🎉 Metals (Assets) module created successfully under Inventory<br>";
