<?php
// HARD DEBUG: always show *why* it stops
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 0);
error_reporting(E_ALL);
ob_implicit_flush(true);

function out($msg) {
    echo $msg . "\n";
    if (function_exists('flush')) flush();
}

register_shutdown_function(function () {
    $e = error_get_last();
    if ($e) {
        // Print fatal error details even when PHP dies
        fwrite(STDERR, "SHUTDOWN(FATAL): {$e['message']} in {$e['file']}:{$e['line']}\n");
    } else {
        fwrite(STDERR, "SHUTDOWN: no PHP fatal captured (possible explicit exit/die in vtiger code)\n");
    }
});

out("STEP 0: PHP STARTED");

if (php_sapi_name() !== 'cli') {
    die("CLI only\n");
}
out("STEP 1: CLI OK");

chdir(__DIR__);

require_once 'config.inc.php';
out("STEP 2: config loaded");

require_once 'includes/main/WebUI.php';
out("STEP 3: WebUI loaded");

require_once 'modules/Users/Users.php';
out("STEP 4: Users loaded");

global $adb, $current_user;

// Reset users + seq (critical)
$adb->pquery("DELETE FROM vtiger_user2role", []);
$adb->pquery("DELETE FROM vtiger_users", []);
$adb->pquery("DELETE FROM vtiger_users_seq", []);
$adb->pquery("INSERT INTO vtiger_users_seq (id) VALUES (0)", []);
out("STEP 5: Users reset");

// Role
$res = $adb->pquery("SELECT roleid FROM vtiger_role LIMIT 1", []);
if ($adb->num_rows($res) === 0) die("NO ROLE FOUND\n");
$roleId = $adb->query_result($res, 0, 'roleid');
out("STEP 6: Role found ($roleId)");

// Create user
$user = new Users();
$user->column_fields['user_name']  = 'admin';
$user->column_fields['first_name'] = 'System';
$user->column_fields['last_name']  = 'Administrator';
$user->column_fields['email1']     = 'admin@example.com';
$user->column_fields['status']     = 'Active';
$user->column_fields['is_admin']   = 'on';
$user->column_fields['roleid']     = $roleId;

$user->save('Users');
out("STEP 7: User saved (ID={$user->id})");

if (empty($user->id)) die("USER ID INVALID\n");

// ---- Everything below is where your run is dying ----

// Make current_user valid (vtiger 8.x often requires this)
out("STEP 7.1: build current_user");
$current_user = new Users();
$current_user->retrieve_entity_info($user->id, 'Users');
$current_user->id = $user->id;
$current_user->is_admin = 'on';
out("STEP 7.2: current_user ready");

// Try password change (most likely failing point)
out("STEP 8: changing password...");
$current_user->change_password('admin@1234');
out("STEP 8: Password set");

// Role map
$adb->pquery(
    "INSERT INTO vtiger_user2role (userid, roleid) VALUES (?, ?)",
    [$user->id, $roleId]
);
out("STEP 9: Role mapped");

// Clear cache/sessions
@exec('rm -rf cache/* storage/session/*');
out("STEP 10: Cache cleared");

out("SUCCESS");
out("LOGIN: admin / admin@1234");
