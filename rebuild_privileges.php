<?php
/**
 * vTiger 8.4 - Rebuild all user privilege and sharing files
 * Safe for Windows/Laragon setups
 * ----------------------------------------------------------
 * Usage:  http://localhost/vtiger-gpm/rebuild_privileges.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$Vtiger_Utils_Log = true;

// --- Core bootstrap ---
require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.inc.php';
require_once 'include/utils/utils.php';
require_once 'includes/main/WebUI.php';
require_once 'vtlib/Vtiger/Module.php';

// --- Load the privilege builder scripts ---
require_once 'modules/Users/CreateUserPrivilegeFile.php';
require_once 'modules/Users/CreateSharingPrivilegeFile.php';

// --- Load Admin context ---
global $current_user;
require_once 'modules/Users/Users.php';
$current_user = Users::getActiveAdminUser();

// --- Output setup ---
header('Content-Type: text/plain');
echo "=== Rebuilding privilege & sharing files ===\n";

// --- Database connection ---
$db = PearDatabase::getInstance();
$result = $db->pquery("SELECT id, user_name FROM vtiger_users WHERE status='Active'", []);

// --- Iterate through each user ---
while ($row = $db->fetchByAssoc($result)) {
    $userId = (int)$row['id'];
    $username = $row['user_name'];

    if (function_exists('createUserPrivilegesfile')) {
        createUserPrivilegesfile($userId);
    }
    if (function_exists('createSharingPrivilegesfile')) {
        createSharingPrivilegesfile($userId);
    }

    echo "  ✔ Rebuilt privileges for User ID {$userId} ({$username})\n";
}

echo "----------------------------------------------\n";
echo "✅ All privilege and sharing files rebuilt successfully.\n";
echo "Now reload your CRM (Ctrl + F5) and log back in.\n";
