<?php
/**
 * Run from CLI:
 *   cd /var/www/html/vtiger-kl
 *   php create_admin.php
 */
if (php_sapi_name() !== 'cli') {
  http_response_code(403);
  exit("CLI only.\n");
}

$Vtiger_Utils_Log = true;

chdir(__DIR__);

// vTiger bootstrap (loads config.inc.php + DB + core)
require_once 'config.inc.php';
require_once 'includes/main/WebUI.php'; // initializes vtiger environment

require_once 'modules/Users/Users.php';

$user = new Users();
$user->column_fields['user_name'] = 'admin';
$user->column_fields['first_name'] = 'System';
$user->column_fields['last_name'] = 'Administrator';
$user->column_fields['is_admin'] = 'on';
$user->column_fields['status'] = 'Active';
$user->column_fields['user_password'] = 'admin@1234';

// Optional but often required fields in some vtiger versions:
$user->column_fields['email1'] = 'admin@example.com';
$user->column_fields['roleid'] = 'H1'; // your admin role id

$user->save('Users');

echo "DONE. admin/admin@1234 created or updated.\n";
