<?php
/**
 * Run from CLI:
 * php create_admin.php
 */

if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

chdir(__DIR__);

require_once 'config.inc.php';
require_once 'includes/main/WebUI.php';
require_once 'modules/Users/Users.php';

$user = new Users();
$user->column_fields['user_name']  = 'admin';
$user->column_fields['first_name'] = 'System';
$user->column_fields['last_name']  = 'Administrator';
$user->column_fields['is_admin']   = 'on';
$user->column_fields['status']     = 'Active';
$user->column_fields['email1']     = 'admin@example.com';
$user->column_fields['roleid']     = 'H1';   // MUST exist

$user->save('Users');

/** THIS IS THE CRITICAL LINE (was missing before) */
$user->setPassword('admin@1234');

echo "OK: admin / admin@1234 created\n";
