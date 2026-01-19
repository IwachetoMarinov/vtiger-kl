<?php
/**
 * vTiger 8.4 – Create admin user from scratch (CLI)
 *
 * Run:
 *   php create_admin_cli.php
 *
 * After success, DELETE THIS FILE.
 */

if (php_sapi_name() !== 'cli') {
    die("CLI only\n");
}

chdir(__DIR__);

require_once 'config.inc.php';
require_once 'includes/main/WebUI.php';
require_once 'modules/Users/Users.php';

global $adb;

/* -----------------------------
 * 1. DELETE ALL USERS
 * ----------------------------- */
$adb->pquery("DELETE FROM vtiger_user2role", []);
$adb->pquery("DELETE FROM vtiger_users", []);

/* -----------------------------
 * 2. GET A VALID ROLE
 * ----------------------------- */
$res = $adb->pquery("SELECT roleid FROM vtiger_role LIMIT 1", []);
if ($adb->num_rows($res) === 0) {
    die("ERROR: No roles found\n");
}
$roleId = $adb->query_result($res, 0, 'roleid');

/* -----------------------------
 * 3. CREATE ADMIN USER
 * ----------------------------- */
$user = new Users();
$user->column_fields['user_name']  = 'admin';
$user->column_fields['first_name'] = 'System';
$user->column_fields['last_name']  = 'Administrator';
$user->column_fields['email1']     = 'admin@example.com';
$user->column_fields['status']     = 'Active';
$user->column_fields['is_admin']   = 'on';
$user->column_fields['roleid']     = $roleId;

$user->save('Users');

/* -----------------------------
 * 4. SET PASSWORD (PHASH – REQUIRED)
 * ----------------------------- */
$user->setPassword('admin@1234');
$user->save('Users');

/* -----------------------------
 * 5. ENSURE ROLE MAPPING
 * ----------------------------- */
$adb->pquery(
    "INSERT INTO vtiger_user2role (userid, roleid) VALUES (?, ?)",
    [$user->id, $roleId]
);

/* -----------------------------
 * 6. CLEAR CACHE / SESSIONS
 * ----------------------------- */
@exec('rm -rf cache/* storage/session/*');

echo "SUCCESS\n";
echo "LOGIN: admin / admin@1234\n";
