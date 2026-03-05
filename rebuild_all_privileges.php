<?php
// Force rebuild of ALL user privilege + sharing privilege files.
// Run as the web user: sudo -u apache php /var/www/html/crm_kl/rebuild_all_privileges.php

chdir(__DIR__);

ini_set('display_errors', '1');
error_reporting(E_ALL);


require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.php';
require_once 'include/database/PearDatabase.php';
require_once 'modules/Users/CreateUserPrivilegeFile.php';
require_once 'modules/Users/Users.php';

global $adb, $current_user;

// Run as admin so vtiger has full context
$current_user = Users::getActiveAdminUser();

$privDir = rtrim($root_directory, "/\\") . '/user_privileges';

echo "Root: {$root_directory}\n";
echo "PrivDir: {$privDir}\n\n";

// exit;

// Pull all active, non-deleted users
$result = $adb->pquery(
    "SELECT id, user_name FROM vtiger_users WHERE deleted=0 AND status='Active' ORDER BY id",
    []
);

$rows = $adb->num_rows($result);
echo "Active users: {$rows}\n\n";

for ($i = 0; $i < $rows; $i++) {
    $uid = (int)$adb->query_result($result, $i, 'id');
    $uname = $adb->query_result($result, $i, 'user_name');

    $up = "{$privDir}/user_privileges_{$uid}.php";
    $sp = "{$privDir}/sharing_privileges_{$uid}.php";

    // Remove old files first (full rebuild)
    @unlink($up);
    @unlink($sp);

    try {
        createUserPrivilegesfile($uid);
        if (function_exists('createUserSharingPrivilegesfile')) {
            createUserSharingPrivilegesfile($uid);
        } else {
            // Some vtiger variants only create sharing file via this name
            if (function_exists('createUserSharingPrivilegesFile')) {
                createUserSharingPrivilegesFile($uid);
            }
        }
    } catch (Throwable $e) {
        echo "[FAIL] {$uid} {$uname} EXCEPTION: {$e->getMessage()}\n";
        continue;
    }

    $okUp = file_exists($up) ? "OK" : "MISSING";
    $okSp = file_exists($sp) ? "OK" : "MISSING";

    echo "[DONE] {$uid} {$uname}  user_priv={$okUp}  sharing_priv={$okSp}\n";
}

echo "\nFinished.\n";
