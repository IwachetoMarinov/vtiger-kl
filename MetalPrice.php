<?php

$Vtiger_Utils_Log = true;

require_once __DIR__ . '/vendor/autoload.php';
include_once 'vtlib/Vtiger/Menu.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/utils.php';

global $adb;

/**
 * CREATE OR UPDATE MODULE
 */
$module = Vtiger_Module::getInstance('MetalPrice');
if (!$module) {
    $module = new Vtiger_Module();
    $module->name   = 'MetalPrice';
    $module->label  = 'Metal Price';
    $module->parent = 'Trades'; // <-- TRADES menu group
    $module->save();
    $module->initTables();
    $module->initWebservice();
} else {
    // Ensure parent is correct even if module already exists
    if (empty($module->parent) || $module->parent !== 'Trades') {
        $module->parent = 'Trades';
        $module->save();
    }
}

/**
 * BLOCK
 */
$infoBlock = Vtiger_Block::getInstance('Price Details', $module);
if (!$infoBlock) {
    $infoBlock        = new Vtiger_Block();
    $infoBlock->label = 'Price Details';
    $module->addBlock($infoBlock);
}

/**
 * FIELDS
 */

/** Metal (picklist) */
$typeOfMetalField = Vtiger_Field::getInstance('type_of_metal', $module);
if (!$typeOfMetalField) {
    $typeOfMetalField             = new Vtiger_Field();
    $typeOfMetalField->name       = 'type_of_metal';
    $typeOfMetalField->label      = 'Metal';
    $typeOfMetalField->table      = $module->basetable;
    $typeOfMetalField->column     = 'type_of_metal';
    $typeOfMetalField->columntype = 'VARCHAR(100)';
    $typeOfMetalField->uitype     = 16;
    $typeOfMetalField->typeofdata = 'V~M';
    $infoBlock->addField($typeOfMetalField);

    $typeOfMetalField->setPicklistValues(['XAU', 'XAG', 'XPT', 'XPD', 'MBTC']);

    // Use this as the entity identifier (record label)
    $module->setEntityIdentifier($typeOfMetalField);
}

/** Date */
$priceDateField = Vtiger_Field::getInstance('price_date', $module);
if (!$priceDateField) {
    $priceDateField             = new Vtiger_Field();
    $priceDateField->name       = 'price_date';
    $priceDateField->label      = 'Date';
    $priceDateField->table      = $module->basetable;
    $priceDateField->column     = 'price_date';
    $priceDateField->columntype = 'DATE';
    $priceDateField->uitype     = 5;
    $priceDateField->typeofdata = 'D~M';
    $infoBlock->addField($priceDateField);
}

/** AM rate (primary) */
$amRateField = Vtiger_Field::getInstance('am_rate', $module);
if (!$amRateField) {
    $amRateField             = new Vtiger_Field();
    $amRateField->name       = 'am_rate';
    $amRateField->label      = 'Primary Rate(AM)';
    $amRateField->table      = $module->basetable;
    $amRateField->column     = 'am_rate';
    $amRateField->columntype = 'DECIMAL(13,7)';
    $amRateField->uitype     = 71;       // Currency
    $amRateField->typeofdata = 'N~M';    // Mandatory numeric
    $amRateField->displaytype = 1;
    $infoBlock->addField($amRateField);
}

/** PM rate (secondary) */
$pmRateField = Vtiger_Field::getInstance('pm_rate', $module);
if (!$pmRateField) {
    $pmRateField             = new Vtiger_Field();
    $pmRateField->name       = 'pm_rate';
    $pmRateField->label      = 'Secondary Rate(PM)';
    $pmRateField->table      = $module->basetable;
    $pmRateField->column     = 'pm_rate';
    $pmRateField->columntype = 'DECIMAL(13,7)';
    $pmRateField->uitype     = 71;       // Currency
    $pmRateField->typeofdata = 'N~O';    // Optional numeric
    $pmRateField->displaytype = 1;
    $infoBlock->addField($pmRateField);
}

/** Assigned To */
$assignedToField = Vtiger_Field::getInstance('assigned_user_id', $module);
if (!$assignedToField) {
    $assignedToField             = new Vtiger_Field();
    $assignedToField->name       = 'assigned_user_id';
    $assignedToField->label      = 'Assigned To';
    $assignedToField->table      = 'vtiger_crmentity';
    $assignedToField->column     = 'smownerid';
    $assignedToField->uitype     = 53;
    $assignedToField->typeofdata = 'V~M';
    $infoBlock->addField($assignedToField);
}

/** Created Time */
$createdTimeField = Vtiger_Field::getInstance('createdtime', $module);
if (!$createdTimeField) {
    $createdTimeField              = new Vtiger_Field();
    $createdTimeField->name        = 'createdtime';
    $createdTimeField->label       = 'Created Time';
    $createdTimeField->table       = 'vtiger_crmentity';
    $createdTimeField->column      = 'createdtime';
    $createdTimeField->uitype      = 70;
    $createdTimeField->typeofdata  = 'T~O';
    $createdTimeField->displaytype = 2;
    $infoBlock->addField($createdTimeField);
}

/** Modified Time */
$modifiedTimeField = Vtiger_Field::getInstance('modifiedtime', $module);
if (!$modifiedTimeField) {
    $modifiedTimeField              = new Vtiger_Field();
    $modifiedTimeField->name        = 'modifiedtime';
    $modifiedTimeField->label       = 'Modified Time';
    $modifiedTimeField->table       = 'vtiger_crmentity';
    $modifiedTimeField->column      = 'modifiedtime';
    $modifiedTimeField->uitype      = 70;
    $modifiedTimeField->typeofdata  = 'T~O';
    $modifiedTimeField->displaytype = 2;
    $infoBlock->addField($modifiedTimeField);
}

/**
 * DEFAULT FILTER
 */
$allFilter = Vtiger_Filter::getInstance('All', $module);
if (!$allFilter) {
    $allFilter = new Vtiger_Filter();
    $allFilter->name = 'All';
    $allFilter->isdefault = true;
    $module->addFilter($allFilter);

    // Column positions start from 0/1; your original used 0..3 - keep it consistent
    $allFilter->addField($typeOfMetalField, 0)
              ->addField($priceDateField, 1)
              ->addField($amRateField, 2)
              ->addField($pmRateField, 3);
}

/**
 * SHARING & TOOLS
 */
$module->setDefaultSharing('Public_ReadOnly');
$module->enableTools(['Import']);
$module->disableTools('Merge');

/**
 * EXTRA TABLE: user field mapping
 */
$adb->pquery(
"CREATE TABLE IF NOT EXISTS `vtiger_metalprice_user_field` (
  `recordid` int(25) NOT NULL,
  `userid` int(25) NOT NULL,
  `starred` varchar(100) DEFAULT NULL,
  KEY `fk_metalpriceid_vtiger_metalprice_user_field` (`recordid`),
  CONSTRAINT `fk_metalpriceid_vtiger_metalprice_user_field`
    FOREIGN KEY (`recordid`) REFERENCES `vtiger_metalprice` (`metalpriceid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8",
[]
);

/**
 * Ensure vtiger_entityname entry exists (idempotent)
 */
$moduleId = getTabid('MetalPrice');

$adb->pquery(
  "INSERT IGNORE INTO vtiger_entityname (tabid,modulename,tablename,fieldname,entityidfield,entityidcolumn)
   VALUES (?,?,?,?,?,?)",
  [$moduleId, 'MetalPrice', 'vtiger_metalprice', 'type_of_metal', 'metalpriceid', 'metalpriceid']
);

/**
 * INDEXES (idempotent)
 */
$res = $adb->pquery("SHOW INDEX FROM vtiger_metalprice WHERE Key_name='unique_metalprice_idx'", []);
if ($adb->num_rows($res) == 0) {
    $adb->pquery("ALTER TABLE `vtiger_metalprice`
                  ADD UNIQUE `unique_metalprice_idx`(`price_date`,`type_of_metal`)", []);
}

$res2 = $adb->pquery("SHOW INDEX FROM vtiger_metalprice WHERE Key_name='metaprice_date_idx'", []);
if ($adb->num_rows($res2) == 0) {
    $adb->pquery("ALTER TABLE `vtiger_metalprice`
                  ADD INDEX `metaprice_date_idx` (`type_of_metal` ASC,`price_date` DESC)", []);
}

/**
 * APP PLACEMENT: move from TOOLS -> TRADES (idempotent)
 * Use the exact appname your vtiger menu uses: 'TRADES' vs 'Trades'
 * Here we use 'TRADES' because you asked for TRADES.
 */
$adb->pquery('DELETE FROM vtiger_app2tab WHERE tabid=? AND appname=?', [$moduleId, 'TOOLS']);
$adb->pquery('INSERT IGNORE INTO vtiger_app2tab(tabid,appname,sequence,visible) VALUES (?,?,?,?)',
             [$moduleId, 'TRADES', 30, 1]);

echo "Module MetalPrice created/updated successfully.\n";
echo "Please log out and log in again to see the changes.\n";
