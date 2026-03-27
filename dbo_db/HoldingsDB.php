<?php
/* dbo_db/ActivitySummary.php */

namespace dbo_db;

include_once 'data/CRMEntity.php';
include_once 'modules/Users/Users.php';
include_once 'helpers/DBConnection.php';
include_once 'helpers/DBSettings.php';

use helpers\DBConnection;
use helpers\DBSettings;

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

        $params[] = $customer_id;

        $sql = "SELECT * FROM $this->database_prefix.[DW_DocHoldings] WHERE [Party_Code] = ?";

        $stmt = sqlsrv_query($this->connection, $sql, $params);

        if ($stmt === false) die(print_r(sqlsrv_errors(), true));

        $summary = [];

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $summary[] = $row;
        }

        sqlsrv_free_stmt($stmt);
        
        $results = [];
        foreach ($summary as $item) {
            $results[] = [
                'spot_date' => $item['Spot_Date'] instanceof \DateTime ? $item['Spot_Date']->format('Y-m-d') : $item['Spot_Date'],
                'spot_price' => $item['Spot_Price'] ?? '',
                'location' => $item['WH_Code'] ?? '',
                'description' => $item['Item_Desc'] ?? '',
                'quantity' => $item['Qty'] ?? 0,
                'serial_no' => $item['Ser_No_List'] ? $this->sanitizeSerials($item['Ser_No_List']) :  '',
                'fine_oz' => $item['FineOz'] ?? 0,
                'total' => $item['Total'] ?? 0,
            ];
        }
        return $results;
    }

    public function getWalletBalances($customer_id = null)
    {
        if (!$customer_id) return [];

        if (!$this->connection) return [];

        $params[] = $customer_id;

        $sql = "SELECT * FROM $this->database_prefix.[DW_DocWalletBal] WHERE [Party_Code] = ?";

        $stmt = sqlsrv_query($this->connection, $sql, $params);

        if ($stmt === false) die(print_r(sqlsrv_errors(), true));

        $summary = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $summary[] = $row;
        }

        sqlsrv_free_stmt($stmt);

        return $summary;
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

        if ($stmt === false) die(print_r(sqlsrv_errors(), true));

        $summary = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $summary[] = $row;
        }

        sqlsrv_free_stmt($stmt);

        $results = [];
        foreach ($summary as $item) {
            $quantity = $item['Qty'] ? $item['Qty'] : $item['Quantity'] ?? 1;
            $serial = $item['Ser_No_List'] ? $item['Ser_No_List'] : $item['Ser_No'] ?? 1;

            $results[] = [
                'serial_no' => $serial,
                'gross_oz' => $item['GrossOz'] ?? 0,
                'fine_oz' => $item['FineOz'] ?? 0,
                'purity' => $item['Purity'] ?? 0,
                'acq_tx_no' => $item['Acq_Tx_No'] ?? '',
                'item_code' => $item['Item_Code'],
                'description' => $item['Item_Desc'],
                'quantity' => $quantity,
                'location' => $item['WH_Code'] ?? '',
                'brand' => $item['Brand'] ?? '',
                'mt_code' => $item['MT_Code'] ?? '',
                "metal" => $this->getMetalName($item['MT_Code'] ?? ''),
            ];
        }
        return $results;
    }

    protected function getMetalName($code): string
    {
        if (!$code) return '';

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

    protected function sanitizeSerials($serials): string
    {
        if (!$serials) return '';

        // 1. Remove trailing semicolons
        $serials = preg_replace('/;+$/', '', $serials);

        // 2. Replace multiple semicolons in the middle with newline
        $serials = preg_replace('/;{2,}/', "\n", $serials);

        return $serials;
    }
}
