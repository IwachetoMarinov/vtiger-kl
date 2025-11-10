<?php
/* modules/Metals/MetalsCron.php */

include_once 'data/CRMEntity.php';
include_once 'modules/Metals/Metals.php';
include_once 'modules/Users/Users.php';

use Dotenv\Dotenv;

class MetalsCron
{

    private $db_username;
    private $db_password;
    private $connection;

    public function __construct()
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();

        $this->db_username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: '';
        $this->db_password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

        $serverName = "qcpitech.ddns.net";
        $connectionOptions = array(
            "Database" => "GPM_DW",
            "Uid" => $this->db_username,
            "PWD" => $this->db_password,
            "TrustServerCertificate" => true,
            "Encrypt" => false
        );

        $this->connection = sqlsrv_connect($serverName, $connectionOptions);
    }

    public function __destruct()
    {
        if ($this->connection) {
            sqlsrv_close($this->connection);
        }
    }

    /**
     * This will be called by vtiger cron.
     */
    public function createUpdateDailyMetal()
    {
        global $adb;
        global $current_user;

        // Run as admin
        $current_user = Users::getActiveAdminUser();
        $metals = $this->fetchMetals();

        $unique_metals = $this->getUniqueMetals($metals);

        // Update or insert metals
        foreach ($unique_metals as $metal_data) {
            $metal_type = $metal_data['MT_Code'];
            $name = $this->getMetalName($metal_type);
            $fineoz = (float)$metal_data['SpotPriceUSD'];

            // Check if this metal already exists
            $result = $adb->pquery("SELECT metalsid FROM vtiger_metals WHERE metal_type = ?", [$metal_type]);

            if (!$this->connection) {
                echo "[MetalsCron] No SQL Server connection.\n";
                return;
            }

            if ($adb->num_rows($result) > 0) {
                // ✅ Update existing record
                $adb->pquery(
                    "UPDATE vtiger_metals SET fineoz = ? WHERE metal_type = ?",
                    [$fineoz, $metal_type]
                );
                echo "[MetalsCron] Updated existing metal: {$name} ({$metal_type}) with fineoz: {$fineoz}" . PHP_EOL;
            } else {
                // ✅ Create new record
                $created_time = date('Y-m-d H:i:s');

                $adb->pquery(
                    "INSERT INTO vtiger_metals (metal_type, fineoz, name, createdtime) VALUES (?, ?, ?, ?)",
                    [$metal_type, $fineoz, $name, $created_time]
                );

                $new_metal_id = $adb->getLastInsertID();
                // Also insert into vtiger_crmentity
                $adb->pquery(
                    "INSERT INTO vtiger_crmentity (crmid, smcreatorid, smownerid, setype, createdtime, modifiedtime, presence, deleted)
                     VALUES (?, ?, ?, ?, NOW(), NOW(), 1, 0)",
                    [$new_metal_id, $current_user->id, $current_user->id, 'Metals']
                );

                echo "\n [MetalsCron] Created new metal record with ID: {$new_metal_id}" . PHP_EOL;
            }
        }

        echo "Cron job completed" . PHP_EOL;
    }

    protected function fetchMetals()
    {
        if (!$this->connection) {
            die(print_r(sqlsrv_errors(), true));
        }

        $sql = "
        SELECT 
            [Date],
            [MT_Code],
            [Curr_Code],
            [SpotPriceUSD],
            [Exc_Rate],
            [SpotPriceCurr]
        FROM [HFS_SQLEXPRESS].[GPM].[dbo].[Metal_Spot_Price]
        ";

        $stmt = sqlsrv_query($this->connection, $sql);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Convert DateTime objects to strings for var_dump readability
            if (isset($row['Date']) && $row['Date'] instanceof DateTime) {
                $row['Date'] = $row['Date']->format('Y-m-d');
            }
            $data[] = $row;
        }

        sqlsrv_free_stmt($stmt);

        return $data;
    }

    protected function getUniqueMetals($metals): array
    {
        $unique_metals = [];

        foreach ($metals as $metal) {
            $code = $metal['MT_Code'];


            if (!isset($unique_metals[$code])) {
                $unique_metals[$code] = $metal;
            }
        }

        return array_values($unique_metals);
    }

    protected function getMetalName($code): string
    {
        $metal_names = [
            'XAU' => 'Gold',
            'XAG' => 'Silver',
            'XPT' => 'Platinum',
            'XPD' => 'Palladium',
            'XPL' => 'Palladium',
            'MBTC' => 'mBitCoin',
        ];

        return $metal_names[$code] ?? '';
    }
}
