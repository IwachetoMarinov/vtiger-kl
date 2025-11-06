<?php
/*+**********************************************************************************
 * Script: delete_GPMCryptoTrx.php
 * Purpose: cleanly remove the custom module GPMCryptoTrx from vtiger
 * Author: ChatGPT helper
 ************************************************************************************/
$Vtiger_Utils_Log = true;
require_once __DIR__ . '/vendor/autoload.php';
require_once 'includes/main/WebUI.php';
require_once 'vtlib/Vtiger/Module.php';

global $adb;

$moduleName = 'GPMCryptoTrx';

try {
    echo "=== Removing module: {$moduleName} ===\n";

    // 1️⃣ Check if the module exists
    $moduleInstance = Vtiger_Module::getInstance($moduleName);
    if (!$moduleInstance) {
        echo "Module {$moduleName} not found or already deleted.\n";
        exit;
    }

    // 2️⃣ Delete related CRM tables (optional, keep if you want data)
    echo "Dropping tables...\n";
    $tables = [
        'vtiger_gpmcryptotrx',
        'vtiger_gpmcryptotrxcf',
    ];
    foreach ($tables as $table) {
        $adb->pquery("DROP TABLE IF EXISTS {$table}");
    }

    // 3️⃣ Delete module relationships and links
    echo "Deleting module relationships...\n";
    $adb->pquery('DELETE FROM vtiger_relatedlists WHERE tabid=? OR related_tabid=?',
        [$moduleInstance->id, $moduleInstance->id]);
    $adb->pquery('DELETE FROM vtiger_field WHERE tabid=?', [$moduleInstance->id]);
    $adb->pquery('DELETE FROM vtiger_blocks WHERE tabid=?', [$moduleInstance->id]);
    $adb->pquery('DELETE FROM vtiger_filter WHERE tabid=?', [$moduleInstance->id]);
    $adb->pquery('DELETE FROM vtiger_entityname WHERE tabid=?', [$moduleInstance->id]);
    $adb->pquery('DELETE FROM vtiger_modentity_num WHERE semodule=?', [$moduleName]);
    $adb->pquery('DELETE FROM vtiger_crmentityrel WHERE module=? OR relmodule=?', [$moduleName, $moduleName]);

    // 4️⃣ Unregister module
    echo "Unregistering module...\n";
    $moduleInstance->delete();

    echo "✅ Module {$moduleName} deleted successfully.\n";
} catch (Throwable $e) {
    echo "❌ Error while deleting module: " . $e->getMessage() . "\n";
}
