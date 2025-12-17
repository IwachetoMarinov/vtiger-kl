<?php
/* modules/Contacts/models/MetalsAPI.php */

include_once 'data/CRMEntity.php';
include_once 'modules/Users/Users.php';
include_once 'helpers/DBConnection.php';

use helpers\DBConnection;

class MetalsAPI
{
    private $connection;

    public function __construct()
    {
        $this->connection = DBConnection::getConnection();
    }

    public function getLatestPriceByName($metal, $currency)
    {
        // check db_username and db_password
        // if (empty($this->db_username) || empty($this->db_password)) return null;
        if (!$this->connection) return null;

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
        if (!$this->connection) return [];

        $metals = $this->fetchMetals();
        $unique_metals = $this->getUniqueMetals($metals, "USD");

        return $unique_metals;
    }

    protected function fetchMetals($date = null)
    {
        if (!$this->connection) return [];

        $params = [];
        $where  = '';

        if ($date) {
            $where = "WHERE [Date] = ?";
            $params[] = $date;
        }

        $sql = "SELECT [Date],[MT_Code],[Curr_Code],[SpotPriceUSD],[Exc_Rate],[SpotPriceCurr]
        FROM [HFS_SQLEXPRESS].[GPM].[dbo].[DW_SpotPrice]
        $where
        ORDER BY [Date] DESC, [Curr_Code]";

        $stmt = sqlsrv_query($this->connection, $sql, $params);

        if ($stmt === false) die(print_r(sqlsrv_errors(), true));

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

    protected function fetchExchangeRateHistoric($date = null)
    {
        if (!$date) return [];

        if (!$this->connection) return [];

        $params = [];
        $where  = '';

        if ($date) {
            $where = "WHERE [Exc_Date] = ?";
            $params[] = $date;
        }

        $sql = "SELECT * FROM [HFS_SQLEXPRESS].[GPM].[dbo].[DW_ExcRateHistoric] $where ORDER BY [Exc_Date] DESC";

        // var_dump($sql, $params);

        $stmt = sqlsrv_query($this->connection, $sql, $params);

        if ($stmt === false) return [];

        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if (isset($row['Exc_Date']) && $row['Exc_Date'] instanceof DateTime) {
                $row['Exc_Date'] = $row['Exc_Date']->format('Y-m-d');
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
        return $this->fetchExchangeRateHistoric($date);
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
