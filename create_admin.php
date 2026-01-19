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

require_once 'includes/runtime/Globals.php';
require_once 'includes/runtime/BaseModel.php';
require_once 'includes/Loader.php';
require_once 'includes/main/WebUI.php';
echo "STEP 3: Core loaded\n";

require_once 'modules/Users/Users.php';
echo "STEP 4: Users module loaded\n";

global $adb, $current_user;

try {
    /* RESET USERS - BE CAREFUL WITH THIS IN PRODUCTION */
    echo "STEP 5: Starting user reset...\n";
    
    // Delete in proper order (foreign key constraints)
    $adb->pquery("DELETE FROM vtiger_user2role WHERE userid > 1", []);
    echo "  - Deleted user2role mappings\n";
    
    $adb->pquery("DELETE FROM vtiger_users WHERE id > 1", []);
    echo "  - Deleted users (kept ID 1 for safety)\n";
    
    // Reset sequence to start from ID 1
    $adb->pquery("DELETE FROM vtiger_users_seq", []);
    $adb->pquery("INSERT INTO vtiger_users_seq (id) VALUES (0)", []);
    echo "  - Reset user sequence\n";

    /* GET ROLE - Try to get Organization role (H2) first */
    $res = $adb->pquery(
        "SELECT roleid FROM vtiger_role WHERE rolename = ? LIMIT 1", 
        ['Organisation']
    );
    
    if ($adb->num_rows($res) === 0) {
        // Fallback to any role
        $res = $adb->pquery("SELECT roleid FROM vtiger_role LIMIT 1", []);
    }
    
    if ($adb->num_rows($res) === 0) {
        die("ERROR: No roles found in database!\n");
    }
    
    $roleId = $adb->query_result($res, 0, 'roleid');
    echo "STEP 6: Role found (ID=$roleId)\n";

    /* CREATE USER OBJECT */
    $user = new Users();
    $user->column_fields['user_name']  = 'admin';
    $user->column_fields['first_name'] = 'System';
    $user->column_fields['last_name']  = 'Administrator';
    $user->column_fields['email1']     = 'iwacheto@abv.bg';
    $user->column_fields['status']     = 'Active';
    $user->column_fields['is_admin']   = 'on';
    $user->column_fields['roleid']     = $roleId;
    
    // Additional required fields
    $user->column_fields['user_password'] = ''; // Will be set later
    $user->column_fields['confirm_password'] = '';
    $user->column_fields['date_format'] = 'yyyy-mm-dd';
    $user->column_fields['time_zone'] = 'UTC';
    $user->column_fields['currency_id'] = 1;
    $user->column_fields['hour_format'] = '24';
    
    echo "STEP 7: Saving user...\n";
    $user->save('Users');
    
    if (empty($user->id)) {
        die("ERROR: User ID is empty after save!\n");
    }
    
    echo "  - User saved (ID={$user->id})\n";

    /* SET CURRENT USER CONTEXT - CRITICAL FOR PASSWORD CHANGE */
    $current_user = new Users();
    $current_user->retrieve_entity_info($user->id, 'Users');
    $current_user->id = $user->id;
    $current_user->is_admin = 'on';
    
    echo "STEP 8: Current user context set\n";

    /* SET PASSWORD */
    $newPassword = 'admin@1234';
    
    // Method 1: Using change_password (requires current_user context)
    try {
        $current_user->change_password($newPassword);
        echo "  - Password set via change_password()\n";
    } catch (Exception $e) {
        // Method 2: Direct database update as fallback
        echo "  - Fallback: Setting password directly\n";
        $encryptedPassword = $user->encrypt_password($newPassword);
        $adb->pquery(
            "UPDATE vtiger_users SET user_password = ?, confirm_password = ? WHERE id = ?",
            [$encryptedPassword, $encryptedPassword, $user->id]
        );
    }
    
    echo "STEP 9: Password configured\n";

    /* ENSURE ROLE MAPPING EXISTS */
    // Check if mapping already exists
    $checkRole = $adb->pquery(
        "SELECT * FROM vtiger_user2role WHERE userid = ?",
        [$user->id]
    );
    
    if ($adb->num_rows($checkRole) === 0) {
        $adb->pquery(
            "INSERT INTO vtiger_user2role (userid, roleid) VALUES (?, ?)",
            [$user->id, $roleId]
        );
        echo "STEP 10: Role mapping created\n";
    } else {
        echo "STEP 10: Role mapping already exists\n";
    }

    /* SET AS ADMIN IN vtiger_users table */
    $adb->pquery(
        "UPDATE vtiger_users SET is_admin = 'on' WHERE id = ?",
        [$user->id]
    );
    echo "STEP 11: Admin flag set\n";

    /* CLEAR CACHE */
    $cacheCleared = false;
    
    if (function_exists('exec')) {
        @exec('rm -rf cache/* storage/session/* 2>&1', $output, $returnCode);
        if ($returnCode === 0) {
            $cacheCleared = true;
        }
    }
    
    // Fallback: PHP-based cache clear
    if (!$cacheCleared) {
        $cacheDirs = ['cache', 'storage/session', 'test/templates_c'];
        foreach ($cacheDirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }
        }
    }
    
    echo "STEP 12: Cache cleared\n";

    /* VERIFY USER WAS CREATED */
    $verify = $adb->pquery(
        "SELECT user_name, is_admin, status FROM vtiger_users WHERE id = ?",
        [$user->id]
    );
    
    if ($adb->num_rows($verify) > 0) {
        $username = $adb->query_result($verify, 0, 'user_name');
        $isAdmin = $adb->query_result($verify, 0, 'is_admin');
        $status = $adb->query_result($verify, 0, 'status');
        
        echo "\n=================================\n";
        echo "SUCCESS - Admin User Created\n";
        echo "=================================\n";
        echo "Username: $username\n";
        echo "Password: $newPassword\n";
        echo "Is Admin: $isAdmin\n";
        echo "Status: $status\n";
        echo "User ID: {$user->id}\n";
        echo "=================================\n";
        echo "\nYou can now login at:\n";
        echo "http://your-vtiger-url/index.php\n";
    } else {
        echo "ERROR: User verification failed!\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}