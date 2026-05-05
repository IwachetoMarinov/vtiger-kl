<?php

require_once __DIR__ . '/vendor/autoload.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'vtlib/Vtiger/Block.php';
include_once 'vtlib/Vtiger/Field.php';
include_once 'include/utils/utils.php';
include_once 'vtlib/Vtiger/Cron.php';

// ini_set('display_errors', 1); error_reporting(E_ALL);

global $adb;

$moduleName = 'YTDReports';

$module = Vtiger_Module::getInstance($moduleName);

if (!$module) {
    $module = new Vtiger_Module();
    $module->name = $moduleName;
    $module->save();
    $module->initTables();

    echo "$moduleName module created\n";
} else {
    echo "$moduleName module already exists\n";
}

/**
 * Create block
 */
$block = Vtiger_Block::getInstance('LBL_YTDREPORTS_INFORMATION', $module);

if (!$block) {
    $block = new Vtiger_Block();
    $block->label = 'LBL_YTDREPORTS_INFORMATION';
    $module->addBlock($block);

    echo "Block created\n";
} else {
    echo "Block already exists\n";
}

$nameField = Vtiger_Field::getInstance('ytdreportsname', $module);

if (!$nameField) {
    $nameField = new Vtiger_Field();
    $nameField->name = 'ytdreportsname';
    $nameField->label = 'Report Name';
    $nameField->table = 'vtiger_ytdreports';
    $nameField->column = 'ytdreportsname';
    $nameField->columntype = 'VARCHAR(255)';
    $nameField->uitype = 2;
    $nameField->typeofdata = 'V~M';
    $block->addField($nameField);

    $module->setEntityIdentifier($nameField);

    echo "Report Name field added\n";
} else {
    echo "Report Name field already exists\n";
}

// Ensure Webservice entity exists for YTDReports
$wsCheck = $adb->pquery(
    "SELECT 1 FROM vtiger_ws_entity WHERE name = ?",
    [$moduleName]
);

if ($adb->num_rows($wsCheck) == 0) {
    $module->initWebservice();
    echo "Webservice entity created for $moduleName\n";
} else {
    echo "Webservice entity already exists\n";
}

/**
 * Create client_id field
 */
$field = Vtiger_Field::getInstance('client_id', $module);

if (!$field) {
    $field = new Vtiger_Field();
    $field->name = 'client_id';
    $field->label = 'Client ID';
    $field->table = 'vtiger_ytdreportscf';
    $field->column = 'client_id';
    $field->columntype = 'VARCHAR(100)';
    $field->uitype = 1;
    $field->typeofdata = 'V~M';

    $block->addField($field);

    echo "client_id field added\n";
} else {
    echo "client_id field already exists\n";
}

// 4. Link module to Contacts
$adb->pquery(
    "UPDATE vtiger_field
     SET presence = 0
     WHERE tabid = ?
     AND fieldname = ?",
    [$module->id, 'client_id']
);

echo "client_id field enabled\n";

/**
 * Link module to Contacts
 */
$contactsModule = Vtiger_Module::getInstance('Contacts');

if ($contactsModule) {
    $relationExists = $adb->pquery(
        "SELECT 1 FROM vtiger_relatedlists 
         WHERE tabid = ? AND related_tabid = ? AND label = ?",
        [$contactsModule->id, $module->id, 'YTD Reports']
    );

    if ($adb->num_rows($relationExists) == 0) {
        $contactsModule->setRelatedList(
            $module,
            'YTD Reports',
            ['ADD', 'SELECT'],
            'get_related_list'
        );

        echo "Related list added to Contacts\n";
    } else {
        echo "Related list already exists\n";
    }
} else {
    echo "Contacts module not found\n";
}

$documentsModule = Vtiger_Module::getInstance('Documents');

if ($documentsModule) {
    $relationExists = $adb->pquery(
        "SELECT 1 FROM vtiger_relatedlists 
         WHERE tabid = ? AND related_tabid = ?",
        [$module->id, $documentsModule->id]
    );

    if ($adb->num_rows($relationExists) == 0) {
        $module->setRelatedList(
            $documentsModule,
            'Documents',
            ['ADD', 'SELECT'],
            'get_related_list'
        );

        echo "Documents related list added to YTDReports\n";
    } else {
        echo "Documents related list already exists\n";
    }
}

/**
 * Register scheduler
 */
$job = Vtiger_Cron::getInstance('YTDReports Monthly');

if (!$job) {
    Vtiger_Cron::register(
        'YTDReports Monthly',
        'modules/YTDReports/cron/Monthly.php',
        1440,
        'YTDReports_Monthly_Cron'
    );

    echo "Cron job created\n";
} else {
    echo "Cron job already exists\n";
}

/**
 * Create log table
 */
$adb->pquery("
    CREATE TABLE IF NOT EXISTS vtiger_ytdreports_log (
        id INT NOT NULL AUTO_INCREMENT,
        client_id VARCHAR(100) NOT NULL,
        period_start DATE NOT NULL,
        period_end DATE NOT NULL,
        activity_summary_docid INT DEFAULT NULL,
        holdings_docid INT DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        error_message TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_client_period (client_id, period_start, period_end)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", []);

echo "YTD reports log table checked/created\n";
