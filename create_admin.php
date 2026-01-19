<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_implicit_flush(true);

function out($m){ echo $m . "\n"; if(function_exists('flush')) flush(); }

if (php_sapi_name() !== 'cli') die("CLI only\n");
chdir(__DIR__);

require_once 'config.inc.php';
require_once 'includes/main/WebUI.php';
require_once 'modules/Users/Users.php';

global $adb, $current_user;

out("STEP 1: Bootstrapped");

/* Reset users + reset sequence */
$adb->pquery("DELETE FROM vtiger_user2role", []);
$adb->pquery("DELETE FROM vtiger_users", []);
$adb->pquery("DELETE FROM vtiger_users_seq", []);
$adb->pquery("INSERT INTO vtiger_users_seq (id) VALUES (0)", []);
out("STEP 2: Users reset");

/* Get a role */
$res = $adb->pquery("SELECT roleid FROM vtiger_role LIMIT 1", []);
if ($adb->num_rows($res) === 0) die("ERROR: No roles found\n");
$roleId = $adb->query_result($res, 0, 'roleid');
out("STEP 3: Role = $roleId");

/* Create admin user */
$user = new Users();
$user->column_fields['user_name']  = 'admin';
$user->column_fields['first_name'] = 'System';
$user->column_fields['last_name']  = 'Administrator';
$user->column_fields['email1']     = 'iwacheto@abv.bg';
$user->column_fields['status']     = 'Active';
$user->column_fields['is_admin']   = 'on';
$user->column_fields['roleid']     = $roleId;

$user->save('Users');

if (empty($user->id)) die("ERROR: user id not created\n");
out("STEP 4: User saved ID={$user->id}");

/* IMPORTANT: load as current_user so vtiger internals are happy */
$current_user = new Users();
$current_user->retrieve_entity_info($user->id, 'Users');
$current_user->id = $user->id;
$current_user->is_admin = 'on';

/* Set password (one argument) */
$current_user->setPassword('admin@1234');
out("STEP 5: Password set");

/* Force role mapping */
$adb->pquery(
    "INSERT INTO vtiger_user2role (userid, roleid) VALUES (?, ?)",
    [$user->id, $roleId]
);
out("STEP 6: Role mapped");

/* Clear cache/sessions */
@exec('rm -rf cache/* storage/session/*');
out("STEP 7: Cache cleared");

out("SUCCESS");
out("LOGIN: admin / admin@1234");
