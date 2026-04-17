<?php

$root = __DIR__;
chdir($root);

require_once $root . '/vendor/autoload.php';
require_once $root . '/config.inc.php';
require_once $root . '/include/utils/utils.php';
require_once $root . '/vtlib/Vtiger/Module.php';
require_once $root . '/includes/main/WebUI.php';

// Ensure vglobal exists
if (!function_exists('vglobal') && file_exists($root . '/includes/runtime/Globals.php')) {
    require_once $root . '/includes/runtime/Globals.php';
}

require_once $root . '/modules/Users/Users.php';

global $current_user;
$current_user = Users::getActiveAdminUser();

if (!$current_user || empty($current_user->id)) {
    throw new Exception('Failed to initialize execution user');
}