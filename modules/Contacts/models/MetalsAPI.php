<?php
/* modules/Contacts/models/MetalsAPI.php */

include_once 'data/CRMEntity.php';
include_once 'modules/Metals/Metals.php';
include_once 'modules/Users/Users.php';

use Dotenv\Dotenv;

class MetalsAPI
{

    private $db_username;
    private $db_password;
    private $connection;

    public function __construct()
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
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

    public function getLatestPriceByName($metal, $currency)
    {
        // check db_username and db_password
        if (empty($this->db_username) || empty($this->db_password)) return null;

        $metals = $this->fetchMetals();

        foreach ($metals as $m) {
            if ($m['MT_Code'] === $metal && $m['Curr_Code'] === $currency) {
                return $m;
            }
        }

        return null;
    }

    public function getMetals()
    {
        // check db_username and db_password
        if (empty($this->db_username) || empty($this->db_password)) {
            throw new Exception("Database credentials are not set in environment variables.");

            return [];
        }
        $metals = $this->fetchMetals();
        $unique_metals = $this->getUniqueMetals($metals, "USD");

        return $unique_metals;
    }

    protected function fetchMetals($date = null)
    {
        if (!$this->connection) die(print_r(sqlsrv_errors(), true));

        $params = [];
        $where  = '';

        if ($date) {
            $where = "WHERE [Date] = ?";
            $params[] = $date;
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
        $where
        ORDER BY [Date] DESC, [Curr_Code]";

        $stmt = sqlsrv_query($this->connection, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if (isset($row['Date']) && $row['Date'] instanceof DateTime) {
                $row['Date'] = $row['Date']->format('Y-m-d');
            }
            $data[] = $row;
        }

        sqlsrv_free_stmt($stmt);

        return $data;
    }

    protected function getUniqueMetals($metals, $currencyCode = "USD"): array
    {
        $unique_metals = [];

        foreach ($metals as $metal) {
            $code = $metal['MT_Code'];
            $currency = $metal['Curr_Code'];

            if ($currency !== $currencyCode) continue;

            if (!isset($unique_metals[$code])) {
                $unique_metals[$code] = $metal;
            }
        }

        return array_values($unique_metals);
    }

    public function getLatestExchangeRate($date = null): array
    {
        $metals = $this->fetchMetals($date);

        $unique_rates = $this->getUniqueRates($metals);

        return $unique_rates;
    }

    protected function getUniqueRates($metals): array
    {
        $unique = [];
        $result = [];

        foreach ($metals as $row) {
            $curr = $row['Curr_Code'];
            $rate = $row['Exc_Rate'];

            if (isset($unique[$curr])) continue;

            $unique[$curr] = true;

            $result[] = [
                'Curr_Code' => $curr,
                'column'    => strtolower($curr) . '_sgd',
                'rate'      => $rate,
            ];
        }

        return $result;
    }
}
