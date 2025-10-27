<?php
/* modules/Metals/MetalsCron.php */

include_once 'data/CRMEntity.php';
include_once 'modules/Metals/Metals.php';
include_once 'modules/Users/Users.php';

class MetalsCron
{

    /**
     * This will be called by vtiger cron.
     */
    public function createDailyMetal()
    {
        global $current_user;

        // Run as admin
        $current_user = Users::getActiveAdminUser();

        $metal_names = ['Gold', 'Palladium', 'Silver', 'Cryptocurrency', 'Platinum', 'Rhodium'];
        $metal_types = ['XAU', 'XPD', 'XAG', 'CRYPTO'];

        // Get random metal type
        $random_metal = $metal_types[array_rand($metal_types)];

        $random_name = $metal_names[array_rand($metal_names)];

        $metal = new Metals();
        $metal->column_fields['name'] = $random_name;
        $metal->column_fields['fineoz'] = rand(100, 1000) / 10;
        $metal->column_fields['type'] = $random_metal;
        $metal->save('Metals');


        echo "[MetalsCron] Created metal: {$random_name} ({$random_metal})" . PHP_EOL;
    }
}
