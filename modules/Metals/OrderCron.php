<?php
/* modules/Orders/OrdersCron.php */

include_once 'data/CRMEntity.php';
include_once 'modules/Users/Users.php';

class OrdersCron
{
    /**
     * This will be called by vtiger cron.
     */
    public function createDailyOrders()
    {
        try {
            global $current_user;

            // Run as admin
            $current_user = Users::getActiveAdminUser();

            $user_id = $current_user->id;

            // Logic to create orders can be added here

            echo "[OrdersCron] Created daily orders. User ID: {$user_id}" . PHP_EOL;
        } catch (Exception $e) {
            echo 'ERROR in OrdersCron: ' . $e->getMessage() . PHP_EOL;
        }
    }
}

class MetalsCron {}