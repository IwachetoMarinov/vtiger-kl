<?php
/* Install_GPMIntent.php */
$Vtiger_Utils_Log = true;
require_once __DIR__ . '/vendor/autoload.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'vtlib/Vtiger/Menu.php';
require_once 'vtlib/Vtiger/Package.php';
require_once 'include/Webservices/Utils.php';
include_once 'modules/ModTracker/ModTracker.php';

function pick(Vtiger_Field $f, array $values)
{
    // Helper to set picklist values safely across versions
    if (method_exists($f, 'setPicklistValues')) $f->setPicklistValues($values);
}

$module = Vtiger_Module::getInstance('GPMIntent');
if (!$module) {
    $module = new Vtiger_Module();
    $module->name   = 'GPMIntent';
    $module->label  = 'Intent';
    $module->parent = 'Trades';
    $module->save();
    $module->initTables();     // creates vtiger_gpmintent & vtiger_gpmintentcf
    $module->initWebservice(); // ws entity
} else {
    // Ensure tables exist even if module existed partially
    $module->initTables();
    $module->initWebservice();
}

// ===== Blocks =====
$infoBlock     = Vtiger_Block::getInstance('Intent Information',   $module) ?: (function () use ($module) {
    $b = new Vtiger_Block();
    $b->label = 'Intent Information';
    $module->addBlock($b);
    return $b;
})();

$packageBlock  = Vtiger_Block::getInstance('Package Information',  $module) ?: (function () use ($module) {
    $b = new Vtiger_Block();
    $b->label = 'Package Information';
    $module->addBlock($b);
    return $b;
})();

$checklistBlock = Vtiger_Block::getInstance('Checklist',            $module) ?: (function () use ($module) {
    $b = new Vtiger_Block();
    $b->label = 'Checklist';
    $module->addBlock($b);
    return $b;
})();

$itemBlock     = Vtiger_Block::getInstance('ITEM_INFO',            $module) ?: (function () use ($module) {
    $b = new Vtiger_Block();
    $b->label = 'ITEM_INFO';
    $module->addBlock($b);
    return $b;
})();

// ===== Fields (entity id first) =====
$entity = Vtiger_Field::getInstance('intent_no', $module);
if (!$entity) {
    $entity = new Vtiger_Field();
    $entity->name       = 'intent_no';
    $entity->label      = 'Intent No';
    $entity->table      = $module->basetable;               // vtiger_gpmintent
    $entity->column     = 'intent_no';
    $entity->columntype = 'VARCHAR(64)';                    // roomy enough
    $entity->uitype     = 4;                                // autonumber display
    $entity->typeofdata = 'V~O';
    $infoBlock->addField($entity);
    $module->setEntityIdentifier($entity);
}

$entity = Vtiger_Field::getInstance('intent_no', $module);
if (!$entity) {
    $entity = new Vtiger_Field();
    $entity->name       = 'intent_no';
    $entity->label      = 'Intent No';
    $entity->table      = $module->basetable;               // vtiger_gpmintent
    $entity->column     = 'intent_no';
    $entity->columntype = 'VARCHAR(64)';                    // roomy enough
    $entity->uitype     = 4;                                // autonumber display
    $entity->typeofdata = 'V~O';
    $infoBlock->addField($entity);
    $module->setEntityIdentifier($entity);
}

$contact = Vtiger_Field::getInstance('contact_id', $module);
if (!$contact) {
    $contact = new Vtiger_Field();
    $contact->name       = 'contact_id';
    $contact->label      = 'Customer';
    $contact->table      = $module->basetable;
    $contact->column     = 'contact_id';
    $contact->columntype = 'INT(11)';
    $contact->uitype     = 10;               // relation
    $contact->typeofdata = 'V~O';
    $infoBlock->addField($contact);
    $contact->setRelatedModules(['Contacts']);
}

$contactERP = Vtiger_Field::getInstance('contact_erp_no', $module);
if (!$contactERP) {
    $contactERP = new Vtiger_Field();
    $contactERP->name       = 'contact_erp_no';
    $contactERP->label      = 'Client ERP Number';
    $contactERP->table      = $module->basetable;
    $contactERP->column     = 'contact_erp_no';
    $contactERP->columntype = 'VARCHAR(32)';
    $contactERP->uitype     = 1;
    $contactERP->typeofdata = 'V~O';
    $infoBlock->addField($contactERP);
}



$status = Vtiger_Field::getInstance('intent_status', $module);
if (!$status) {
    $status = new Vtiger_Field();
    $status->name       = 'intent_status';
    $status->label      = 'Status';
    $status->table      = $module->basetable;
    $status->column     = 'intent_status';
    $status->columntype = 'VARCHAR(128)';
    $status->uitype     = 16;
    $status->typeofdata = 'V~M';
    $infoBlock->addField($status);
    pick($status, [
        'BD - Awaiting further instruction',
        'BD - Execute trade',
        'OPS - Trade executed',
        'BD - Arrange delivery',
        'OPS - Delivery completed',
        'BD - Trade completed'
    ]);
}

// Lead Location (text)
$leadLoc = Vtiger_Field::getInstance('lead_location', $module);
if (!$leadLoc) {
    $leadLoc = new Vtiger_Field();
    $leadLoc->name       = 'lead_location';
    $leadLoc->label      = 'Lead Location';
    $leadLoc->table      = $module->basetable;
    $leadLoc->column     = 'lead_location';
    $leadLoc->columntype = 'VARCHAR(128)';
    $leadLoc->uitype     = 1;
    $leadLoc->typeofdata = 'V~O';
    $infoBlock->addField($leadLoc);
}

// Lead (relation to Leads)
$lead = Vtiger_Field::getInstance('lead_id', $module);
if (!$lead) {
    $lead = new Vtiger_Field();
    $lead->name       = 'lead_id';
    $lead->label      = 'Lead';
    $lead->table      = $module->basetable;
    $lead->column     = 'lead_id';
    $lead->columntype = 'INT(11)';
    $lead->uitype     = 10;
    $lead->typeofdata = 'V~O';
    $infoBlock->addField($lead);
    $lead->setRelatedModules(['Leads']);
}

$remark = Vtiger_Field::getInstance('remark', $module);
if (!$remark) {
    $remark = new Vtiger_Field();
    $remark->name       = 'remark';
    $remark->label      = 'Remark';
    $remark->table      = $module->basetable;
    $remark->column     = 'remark';
    $remark->columntype = 'VARCHAR(500)';
    $remark->uitype     = 21;
    $remark->typeofdata = 'V~O';
    $infoBlock->addField($remark);
}

$loc = Vtiger_Field::getInstance('gpm_order_location', $module);
if (!$loc) {
    $loc = new Vtiger_Field();
    $loc->name       = 'gpm_order_location';
    $loc->label      = 'Location';
    $loc->table      = $module->basetable;
    $loc->column     = 'gpm_order_location';
    $loc->columntype = 'VARCHAR(128)';
    $loc->uitype     = 16;
    $loc->typeofdata = 'V~O';
    $infoBlock->addField($loc);
    pick($loc, ['Geneva, Switzerland', 'Zurich, Switzerland', 'Singapore', 'Hong Kong', 'Other']);
}

$labels = Vtiger_Field::getInstance('gpm_labels', $module);
if (!$labels) {
    $labels = new Vtiger_Field();
    $labels->name       = 'gpm_labels';
    $labels->label      = 'Labels';
    $labels->table      = $module->basetable;
    $labels->column     = 'gpm_labels';
    $labels->columntype = 'VARCHAR(256)';
    $labels->uitype     = 33; // multi-picklist
    $labels->typeofdata = 'V~O';
    $infoBlock->addField($labels);
    pick($labels, [
        'Pending Document',
        'Pending Receipt',
        'Pending Payment',
        'Pending Delivery',
        'Pending Arrival of Metals',
        'Pending Metal Allocation',
        'Untraded Funds',
        'Done'
    ]);
}

// Package Information block fields

$metalType = Vtiger_Field::getInstance('gpm_metal_type', $module);
if (!$metalType) {
    $metalType = new Vtiger_Field();
    $metalType->name       = 'gpm_metal_type';
    $metalType->label      = 'Metal Type';
    $metalType->table      = $module->basetable;
    $metalType->column     = 'gpm_metal_type';
    $metalType->columntype = 'VARCHAR(64)';
    $metalType->uitype     = 16;            // picklist
    $metalType->typeofdata = 'V~M';
    $packageBlock->addField($metalType);
    pick($metalType, ['XAU', 'XAG', 'XPT', 'XPD', 'XRH', 'CRYPTO']);
}

$orderType = Vtiger_Field::getInstance('gpm_order_type', $module);
if (!$orderType) {
    $orderType = new Vtiger_Field();
    $orderType->name       = 'gpm_order_type';
    $orderType->label      = 'Order Type';
    $orderType->table      = $module->basetable;
    $orderType->column     = 'gpm_order_type';
    $orderType->columntype = 'VARCHAR(128)';
    $orderType->uitype     = 16;
    $orderType->typeofdata = 'V~M';
    $packageBlock->addField($orderType);
    pick($orderType, [
        'Purchase & Delivery',
        'Purchase & Storage',
        'Sale',
        'Deposit of Metals',
        'Withdrawal of Metals',
        'Forward Contract',
        'To be confirmed'
    ]);
}

$currency = Vtiger_Field::getInstance('package_currency', $module);
if (!$currency) {
    $currency = new Vtiger_Field();
    $currency->name       = 'package_currency';
    $currency->label      = 'Currency';
    $currency->table      = $module->basetable;
    $currency->column     = 'package_currency';
    $currency->columntype = 'VARCHAR(12)';
    $currency->uitype     = 16;
    $currency->typeofdata = 'V~M';
    $packageBlock->addField($currency);
} else {
    $currency->label = 'Currency';
    $currency->save();
}
pick($currency, [
    'AUD',
    'CAD',
    'CHF',
    'CNY',
    'EUR',
    'GBP',
    'HKD',
    'IDR',
    'INR',
    'JPY',
    'KRW',
    'MYR',
    'NZD',
    'PHP',
    'QAR',
    'SAR',
    'SGD',
    'THB',
    'TWD',
    'USD',
    'VND'
]);

$pkgAmt = Vtiger_Field::getInstance('package_price', $module);
if (!$pkgAmt) {
    $pkgAmt = new Vtiger_Field();
    $pkgAmt->name       = 'package_price';
    $pkgAmt->label      = 'Package Amount';
    $pkgAmt->table      = $module->basetable;
    $pkgAmt->column     = 'package_price';
    $pkgAmt->columntype = 'DECIMAL(24,2)';
    $pkgAmt->uitype     = 71;
    $pkgAmt->typeofdata = 'N~O';
    $packageBlock->addField($pkgAmt);
}

$indicSpot = Vtiger_Field::getInstance('indicative_spot_price', $module);
if (!$indicSpot) {
    $indicSpot = new Vtiger_Field();
    $indicSpot->name       = 'indicative_spot_price';
    $indicSpot->label      = 'Indicative Spot';
    $indicSpot->table      = $module->basetable;
    $indicSpot->column     = 'indicative_spot_price';
    $indicSpot->columntype = 'DECIMAL(12,4)';
    $indicSpot->uitype     = 71;       // currency/number style input
    $indicSpot->typeofdata = 'N~M';     // mandatory
    $packageBlock->addField($indicSpot);
}

$totalOz = Vtiger_Field::getInstance('total_oz', $module);
if (!$totalOz) {
    $totalOz = new Vtiger_Field();
    $totalOz->name       = 'total_oz';
    $totalOz->label      = 'Total FineOz';
    $totalOz->table      = $module->basetable;
    $totalOz->column     = 'total_oz';
    $totalOz->columntype = 'DECIMAL(12,8)';
    $totalOz->uitype     = 1;
    $totalOz->displaytype = 3;
    $totalOz->typeofdata = 'N~O';
    $itemBlock->addField($totalOz);
}

$totalAmt = Vtiger_Field::getInstance('total_amount', $module);
if (!$totalAmt) {
    $totalAmt = new Vtiger_Field();
    $totalAmt->name       = 'total_amount';
    $totalAmt->label      = 'Total Amount';
    $totalAmt->table      = $module->basetable;
    $totalAmt->column     = 'total_amount';
    $totalAmt->columntype = 'DECIMAL(24,4)';
    $totalAmt->uitype     = 71;
    $totalAmt->displaytype = 3;
    $totalAmt->typeofdata = 'N~O';
    $itemBlock->addField($totalAmt);
}

$totalForeign = Vtiger_Field::getInstance('total_foreign_amount', $module);
if (!$totalForeign) {
    $totalForeign = new Vtiger_Field();
    $totalForeign->name       = 'total_foreign_amount';
    $totalForeign->label      = 'Total Amount (Foreign)';
    $totalForeign->table      = $module->basetable;
    $totalForeign->column     = 'total_foreign_amount';
    $totalForeign->columntype = 'DECIMAL(24,4)';
    $totalForeign->uitype     = 71;
    $totalForeign->displaytype = 3;
    $totalForeign->typeofdata = 'N~O';
    $itemBlock->addField($totalForeign);
}

// ===== System owner & timestamps from vtiger_crmentity (correct mapping) =====
$owner = Vtiger_Field::getInstance('assigned_user_id', $module);
if (!$owner) {
    $owner = new Vtiger_Field();
    $owner->name       = 'assigned_user_id';
    $owner->label      = 'Assigned To';
    $owner->table      = 'vtiger_crmentity';
    $owner->column     = 'smownerid';
    $owner->uitype     = 53;
    $owner->typeofdata = 'V~M';
    $infoBlock->addField($owner);
}
$created = Vtiger_Field::getInstance('createdtime', $module);
if (!$created) {
    $created = new Vtiger_Field();
    $created->name       = 'createdtime';
    $created->label      = 'Created Time';
    $created->table      = 'vtiger_crmentity';
    $created->column     = 'createdtime';
    $created->uitype     = 70;
    $created->typeofdata = 'DT~O';
    $created->displaytype = 2;
    $infoBlock->addField($created);
}
$modified = Vtiger_Field::getInstance('modifiedtime', $module);
if (!$modified) {
    $modified = new Vtiger_Field();
    $modified->name       = 'modifiedtime';
    $modified->label      = 'Modified Time';
    $modified->table      = 'vtiger_crmentity';
    $modified->column     = 'modifiedtime';
    $modified->uitype     = 70;
    $modified->typeofdata = 'DT~O';
    $modified->displaytype = 2;
    $infoBlock->addField($modified);
}

// ===== Checklist (uitype 56) =====
$checklist = [
    ['chk_trade_order', 'Obtain Signed Trade Order'],
    ['chk_collection_request', 'Obtain Signed Collection Request'],
    ['chk_tc_invoice_to_client', 'Send TC / Invoice to client'],
    ['chk_collection_acknowledgement', 'Obtain Signed Collection Acknowledgement'],
];
foreach ($checklist as [$name, $label]) {
    $f = Vtiger_Field::getInstance($name, $module);
    if (!$f) {
        $f = new Vtiger_Field();
        $f->name       = $name;
        $f->label      = $label;
        $f->table      = $module->basetable;
        $f->column     = $name;
        $f->columntype = 'VARCHAR(5)';
        $f->uitype     = 56;
        $f->typeofdata = 'C~O';
        $checklistBlock->addField($f);
    }
}

// ===== Ensure default "All" filter contains Owner & Created Time =====
$all = Vtiger_Filter::getInstance('All', $module);
if (!$all) {
    $all = new Vtiger_Filter();
    $all->name = 'All';
    $all->isdefault = true;
    $module->addFilter($all);
}
// Set visible columns (order indexes)
$all->addField($entity, 0)
    ->addField($metalType, 1)
    ->addField($status, 2)
    ->addField($totalOz, 3)
    ->addField($totalAmt, 4)
    ->addField($created, 5)
    ->addField($owner, 6);

// ===== Actions/tools, sharing, tracker, app tab, numbering, custom table =====
$module->enableTools(['Import', 'Export']);
$module->setDefaultSharing('private');

// ModTracker
$mt = new ModTracker();
$mt->enableTrackingForModule(getTabid('GPMIntent'));

// App tab placement
global $adb;
$adb->pquery(
    'INSERT IGNORE INTO vtiger_app2tab (tabid,appname,sequence,visible) VALUES (?,?,?,?)',
    [getTabid('GPMIntent'), 'TRADES', 30, 1]
);

// Safe create of detail lines table
$adb->pquery("CREATE TABLE IF NOT EXISTS `vtiger_gpmintent_line` (
    `gpmintentid` INT(19) NOT NULL,
    `gpmmetalid` INT(11) DEFAULT NULL,
    `remark` VARCHAR(512) DEFAULT NULL,
    `qty` DECIMAL(12,8) DEFAULT NULL,
    `fine_oz` DECIMAL(12,8) DEFAULT NULL,
    `premium_or_discount` DECIMAL(5,2) DEFAULT NULL,
    `premium_or_discount_usd` DECIMAL(12,4) DEFAULT NULL,
    `value_usd` DECIMAL(24,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

// Optional: numbering (adjust if you want a different prefix)
$adb->pquery(
    "INSERT IGNORE INTO vtiger_modentity_num (semodule,prefix,start_id,cur_id,active) VALUES (?,?,?,?,?)",
    ['GPMIntent', 'INT', 1, 1, 1]
);

echo '<h3>GPMIntent installed/updated successfully.</h3>';
