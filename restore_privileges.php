<?php
// restore_privileges.php
chdir(__DIR__);
require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.inc.php';

require_once('vtlib/Vtiger/Module.php');
require_once('include/utils/utils.php');
require_once('modules/Users/CreateUserPrivilegeFile.php');

global $adb;

// Get all users
$result = $adb->query("SELECT id FROM vtiger_users WHERE deleted=0");
$userIds = array();

while($row = $adb->fetchByAssoc($result)) {
    $userIds[] = $row['id'];
}

// Recreate privileges for each user
foreach($userIds as $userId) {
    echo "Recreating privileges for user ID: $userId<br>";
    createUserPrivilegesfile($userId);
    createUserSharingPrivilegesfile($userId);
}

echo "<br><strong>Done! User privileges restored.</strong>";