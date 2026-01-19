<?php
$Vtiger_Utils_Log = true;

// Composer dependencies (Monolog, etc.)
require_once __DIR__ . '/vendor/autoload.php';
require_once 'modules/Users/Users.php';

$user = new Users();
$user->column_fields['user_name'] = 'admin';
$user->column_fields['first_name'] = 'System';
$user->column_fields['last_name'] = 'Administrator';
$user->column_fields['is_admin'] = 'on';
$user->column_fields['status'] = 'Active';
$user->column_fields['user_password'] = 'admin@1234';
$user->save('Users');

echo "DONE\n";
