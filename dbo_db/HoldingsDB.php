<?php
/* dbo_db/ActivitySummary.php */

namespace dbo_db;

include_once 'data/CRMEntity.php';
include_once 'modules/Users/Users.php';
include_once 'helpers/DBConnection.php';
include_once 'helpers/DBSettings.php';
include_once 'adapters/HoldingsMapper.php';

use helpers\DBConnection;
use helpers\DBSettings;
use adapters\HoldingsMapper;

class HoldingsDB
{
    private $connection;
    private $database_prefix;
    private $metal_settings;

    public function __construct()
    {
        $this->connection = DBConnection::getConnection();
        $this->database_prefix = DBConnection::getDatabasePrefix();
        $this->metal_settings = DBSettings::MetalsOrderSettings();
    }

    public function getHoldingsMetalsByDateRange(string $customer_id, string $start_date, string $end_date)
    {
        if (!$customer_id || !$start_date || !$end_date) return [];

        if (!$this->connection) return [];

        $where = "WHERE Party_Code = ?";
        $params[] = $customer_id;

        if ($start_date) {
            $where .= empty($where) ? "WHERE" : " AND";
            $where .= " Spot_Date >= ?";
            $params[] = $start_date;
        }

        if ($end_date) {
            $where .= empty($where) ? "WHERE" : " AND";
            $where .= " Spot_Date <= ?";
            $params[] = $end_date;
        }

        try {
            $sql = "SELECT DISTINCT MT_Name, Spot_Price FROM $this->database_prefix.[DW_DocHoldings] $where";

            $stmt = sqlsrv_query($this->connection, $sql, $params);

            if ($stmt === false) return [];

            $summary = [];

            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $summary[] = $row;
            }

            // reorder metals based on settings
            usort($summary, function ($a, $b) {
                $metalA = $a['MT_Name'] ?? '';
                $metalB = $b['MT_Name'] ?? '';

                $orderA = $this->metal_settings[$metalA] ?? PHP_INT_MAX;
                $orderB = $this->metal_settings[$metalB] ?? PHP_INT_MAX;

                return $orderA <=> $orderB;
            });

            sqlsrv_free_stmt($stmt);

            return $summary;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getHoldingsMetals($customer_id = null)
    {
        if (!$customer_id || !$this->connection) return [];

        try {
            $params[] = $customer_id;

            $sql = "SELECT DISTINCT MT_Name, Spot_Price FROM $this->database_prefix.[DW_DocHoldings] WHERE Party_Code = ?";

            $stmt = sqlsrv_query($this->connection, $sql, $params);

            if ($stmt === false) return [];

            $summary = [];

            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $summary[] = $row;
            }

            // reorder metals based on settings
            usort($summary, function ($a, $b) {
                $metalA = $a['MT_Name'] ?? '';
                $metalB = $b['MT_Name'] ?? '';

                $orderA = $this->metal_settings[$metalA] ?? PHP_INT_MAX;
                $orderB = $this->metal_settings[$metalB] ?? PHP_INT_MAX;

                return $orderA <=> $orderB;
            });

            sqlsrv_free_stmt($stmt);

            return $summary;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getHoldings($customer_id = null)
    {
        if (!$customer_id) return [];

        if (!$this->connection) return [];

        try {
            $params[] = $customer_id;

            $sql = "SELECT * FROM $this->database_prefix.[DW_DocHoldings] WHERE [Party_Code] = ?";

            $stmt = sqlsrv_query($this->connection, $sql, $params);

            if ($stmt === false) {
                throw new \Exception('Holdings data is temporarily unavailable.');
            }

            $summary = [];

            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $summary[] = $row;
            }

            sqlsrv_free_stmt($stmt);

            return HoldingsMapper::mapDocHoldingRows($summary);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getHoldingsByDateRange(string $customer_id, string $start_date, string $end_date)
    {
        if (!$customer_id || !$start_date || !$end_date) return [];

        if (!$this->connection) return [];

        $where = '';
        $params = [];

        if ($customer_id) {
            $where .= "WHERE [Party_Code] = ?";
            $params[] = $customer_id;
        }

        if ($start_date) {
            $where .= empty($where) ? "WHERE" : " AND";
            $where .= " [Spot_Date] >= ?";
            $params[] = $start_date;
        }

        if ($end_date) {
            $where .= empty($where) ? "WHERE" : " AND";
            $where .= " [Spot_Date] <= ?";
            $params[] = $end_date;
        }

        try {
            $sql = "SELECT * FROM $this->database_prefix.[DW_DocHoldings] $where";

            $stmt = sqlsrv_query($this->connection, $sql, $params);

            if ($stmt === false) {
                throw new \Exception('Holdings data is temporarily unavailable.');
            }

            $summary = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $summary[] = $row;
            }

            sqlsrv_free_stmt($stmt);

            return HoldingsMapper::mapDocHoldingRows($summary);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getWalletBalances($customer_id = null)
    {
        if (!$customer_id) return [];

        if (!$this->connection) return [];

        try {
            $params[] = $customer_id;

            $sql = "SELECT * FROM $this->database_prefix.[DW_DocWalletBal] WHERE [Party_Code] = ?";

            $stmt = sqlsrv_query($this->connection, $sql, $params);

            if ($stmt === false) {
                throw new \Exception('Wallet balances data is temporarily unavailable.');
            }

            $summary = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $summary[] = $row;
            }

            sqlsrv_free_stmt($stmt);

            return $summary;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getHoldingsData($customer_id = null)
    {
        if (!$customer_id) return [];

        if (!$this->connection) return [];

        $where = '';
        $params = [];

        if ($customer_id) {
            $where = "WHERE [Party_Code] = ?";
            $params[] = $customer_id;
        }

        $sql = "SELECT * FROM $this->database_prefix.[DW_StkHoldings] $where";

        $stmt = sqlsrv_query($this->connection, $sql, $params);

        if ($stmt === false) {
            // die(print_r(sqlsrv_errors(), true));
            throw new \Exception('Holdings data is temporarily unavailable.');
        }

        $summary = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $summary[] = $row;
        }

        sqlsrv_free_stmt($stmt);

        return HoldingsMapper::mapStockHoldingRows($summary);
    }
}
