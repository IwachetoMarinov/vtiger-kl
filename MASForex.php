<?php

$Vtiger_Utils_Log = true;
require_once __DIR__ . '/vendor/autoload.php';
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');


$module = Vtiger_Module::getInstance('MASForex');
if (!$module) {
    $module = new Vtiger_Module();
    $module->name = 'MASForex';
    $module->parent = 'Tools';

    $module->save();
    $module->initTables();
    $module->initWebservice();
}
$infoBlock = Vtiger_Block::getInstance('Exchange Rates', $module);
if (!$infoBlock) {
    $infoBlock = new Vtiger_Block();
    $infoBlock->label = 'Exchange Rates';
    $module->addBlock($infoBlock);
}
$priceDateField = Vtiger_Field::getInstance('price_date', $module);
if (!$priceDateField) {
    $priceDateField = new Vtiger_Field();
    $priceDateField->name = 'price_date';
    $priceDateField->label = 'Date';
    $priceDateField->table = $module->basetable;
    $priceDateField->column = 'price_date';
    $priceDateField->columntype = 'DATE';
    $priceDateField->uitype = 5;
    $priceDateField->typeofdata = 'D~M';
    $infoBlock->addField($priceDateField);
    $module->setEntityIdentifier($priceDateField);
}
$usdSgd = Vtiger_Field::getInstance('usd_sgd', $module);
if (!$usdSgd) {
    $usdSgd = new Vtiger_Field();
    $usdSgd->name = 'usd_sgd';
    $usdSgd->label = 'USD/SGD';
    $usdSgd->table = $module->basetable;
    $usdSgd->column = 'usd_sgd';
    $usdSgd->columntype = 'DECIMAL(8, 4)';
    $usdSgd->uitype = 71;
    $usdSgd->typeofdata = 'V~O';
    $infoBlock->addField($usdSgd);
}

$eurSgd = Vtiger_Field::getInstance('eur_sgd', $module);
if (!$eurSgd) {
    $eurSgd = new Vtiger_Field();
    $eurSgd->name = 'eur_sgd';
    $eurSgd->label = 'EUR/SGD';
    $eurSgd->table = $module->basetable;
    $eurSgd->column = 'eur_sgd';
    $eurSgd->columntype = 'DECIMAL(8, 4)';
    $eurSgd->uitype = 71;
    $eurSgd->typeofdata = 'V~O';
    $infoBlock->addField($eurSgd);
}

$cadSgd = Vtiger_Field::getInstance('cad_sgd', $module);
if (!$cadSgd) {
    $cadSgd = new Vtiger_Field();
    $cadSgd->name = 'cad_sgd';
    $cadSgd->label = 'CAD/SGD';
    $cadSgd->table = $module->basetable;
    $cadSgd->column = 'cad_sgd';
    $cadSgd->columntype = 'DECIMAL(8, 4)';
    $cadSgd->uitype = 71;
    $cadSgd->typeofdata = 'V~O';
    $infoBlock->addField($cadSgd);
}


$chfSgd = Vtiger_Field::getInstance('chf_sgd', $module);
if (!$chfSgd) {
    $chfSgd = new Vtiger_Field();
    $chfSgd->name = 'chf_sgd';
    $chfSgd->label = 'CHF/SGD';
    $chfSgd->table = $module->basetable;
    $chfSgd->column = 'chf_sgd';
    $chfSgd->columntype = 'DECIMAL(8, 4)';
    $chfSgd->uitype = 71;
    $chfSgd->typeofdata = 'V~O';
    $infoBlock->addField($chfSgd);
}


$hkdSgd = Vtiger_Field::getInstance('hkd_sgd', $module);
if (!$hkdSgd) {
    $hkdSgd = new Vtiger_Field();
    $hkdSgd->name = 'hkd_sgd';
    $hkdSgd->label = 'HKD/SGD';
    $hkdSgd->table = $module->basetable;
    $hkdSgd->column = 'hkd_sgd';
    $hkdSgd->columntype = 'DECIMAL(8, 4)';
    $hkdSgd->uitype = 71;
    $hkdSgd->typeofdata = 'V~O';
    $infoBlock->addField($hkdSgd);
}

$myrSgd = Vtiger_Field::getInstance('myr_sgd', $module);
if (!$myrSgd) {
    $myrSgd = new Vtiger_Field();
    $myrSgd->name = 'myr_sgd';
    $myrSgd->label = 'MYR/SGD';
    $myrSgd->table = $module->basetable;
    $myrSgd->column = 'myr_sgd';
    $myrSgd->columntype = 'DECIMAL(8, 4)';
    $myrSgd->uitype = 71;
    $myrSgd->typeofdata = 'V~O';
    $infoBlock->addField($myrSgd);
}


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
    $allFilter->addField($priceDateField)
            ->addField($usdSgd, 1)
            ->addField($eurSgd, 2)
            ->addField($cadSgd, 3)
            ->addField($chfSgd, 4)
            ->addField($hkdSgd, 5)
            ->addField($myrSgd, 6);
}

$module->setDefaultSharing('Public_ReadOnly');

global $adb;
$adb->pquery('INSERT INTO vtiger_app2tab(tabid,appname,sequence,visible) values(?,?,?,?)', array(getTabid('MASForex'), 'TOOLS', 32, 1));
