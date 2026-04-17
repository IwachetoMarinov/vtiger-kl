<?php

$Vtiger_Utils_Log = true;
require_once __DIR__ . '/vendor/autoload.php';
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

$module = Vtiger_Module::getInstance('GPMIntent');

global $adb;
$adb->pquery('update vtiger_field set uitype = 4 where fieldname = ? and tablename = ?', array('intent_no', 'vtiger_gpmintent'));
$adb->pquery("INSERT INTO vtiger_actionmapping values (14,'ViewQuotation',1),(15,'ViewProformaInvoice',1)", array());

// Print the result to verify
echo "Module 'GPMIntent' updated successfully with new uitype and action mappings.";

$module->enableTools(array('Import', 'Export', 'ViewQuotation', 'ViewProformaInvoice'));
$module->setDefaultSharing('private');

echo "Module 'GPMIntent' has been updated and tools enabled successfully.";
