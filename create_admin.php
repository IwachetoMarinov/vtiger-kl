<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "STEP 0: PHP STARTED\n";

if (php_sapi_name() !== 'cli') {
    die("CLI only\n");
}
echo "STEP 1: CLI OK\n";

chdir(__DIR__);

require_once 'config.inc.php';
echo "STEP 2: config loaded\n";

require_once 'includes/main/WebUI.php';
echo "STEP 3: WebUI loaded\n";

require_once 'modules/Users/Users.php';
echo "STEP 4: Users loaded\n";

global $adb, $current_user;

/* RESET USERS */
$adb->pquery("DELETE FROM vtiger_user2role", []);
$adb->pquery("DELETE FROM vtiger_users", []);
$adb->pquery("DELETE FROM vtiger_users_seq", []);
$adb->pquery("INSERT INTO vtiger_users_seq (id) VALUES (0)", []);
echo "STEP 5: Users reset\n";

/* GET ROLE */
$res = $adb->pquery("SELECT roleid FROM vtiger_role LIMIT 1", []);
$roleId = $adb->query_result($res, 0, 'roleid');
echo "STEP 6: Role found ($roleId)\n";

/* CREATE USER */
$user = new Users();
$user->column_fields['user_name']  = 'admin';
$user->column_fields['first_name'] = 'System';
$user->column_fields['last_name']  = 'Administrator';
$user->column_fields['email1']     = 'admin@example.com';
$user->column_fields['status']     = 'Active';
$user->column_fields['is_admin']   = 'on';
$user->column_fields['roleid']     = $roleId;
$user->save('Users');

echo "STEP 7: User saved (ID={$user->id})\n";

/* 🔑 CRITICAL PART — DEFINE CURRENT USER */
$current_user = new Users();
$current_user->retrieve_entity_info($user->id, 'Users');
$current_user->id = $user->id;
$current_user->is_admin = 'on';

/* SET PASSWORD — THIS NOW WORKS */
$current_user->change_password('admin@1234');
echo "STEP 8: Password set\n";

/* ROLE MAP */
$adb->pquery(
    "INSERT INTO vtiger_user2role (userid, roleid) VALUES (?, ?)",
    [$user->id, $roleId]
);
echo "STEP 9: Role mapped\n";

/* CLEAR CACHE */
@exec('rm -rf cache/* storage/session/*');
echo "STEP 10: Cache cleared\n";

echo "SUCCESS\n";
echo "LOGIN: admin / admin@1234\n";
