<?php
/* dbo_db/ActivitySummary.php */

namespace dbo_db;

include_once 'data/CRMEntity.php';
include_once 'modules/Users/Users.php';
include_once 'helpers/DBConnection.php';

use helpers\DBConnection;

class HoldingsDB
{
    private $connection;

    public function __construct()
    {
        $this->connection = DBConnection::getConnection();
    }

    public function getHoldings($customer_id = null)
    {
        if (!$customer_id) return [];

        if (!$this->connection) die(print_r(sqlsrv_errors(), true));

        $params[] = $customer_id;

        $sql = "SELECT * FROM [HFS_SQLEXPRESS].[GPM].[dbo].[DW_DocHoldings] WHERE [Party_Code] = ?";

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
                'serial_no' => $item['Ser_No_List'] ?? '',
                'fine_oz' => $item['FineOz'] ?? 0,
                'total' => $item['Total'] ?? 0,
            ];
        }
        return $results;
    }

    public function getWalletBalances($customer_id = null)
    {
        if (!$customer_id) return [];

        if (!$this->connection) die(print_r(sqlsrv_errors(), true));

        $params[] = $customer_id;

        $sql = "SELECT * FROM [HFS_SQLEXPRESS].[GPM].[dbo].[DW_DocWalletBal] WHERE [Party_Code] = ?";

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

        if (!$this->connection) die(print_r(sqlsrv_errors(), true));

        $where = '';
        $params = [];

        if ($customer_id) {
            $where = "WHERE [Party_Code] = ?";
            $params[] = $customer_id;
        }

        $sql = "SELECT * FROM [HFS_SQLEXPRESS].[GPM].[dbo].[DW_StkHoldings] $where";

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
                'serial_no' => $item['Ser_No'],
                'gross_oz' => $item['GrossOz'] ?? 0,
                'fine_oz' => $item['FineOz'] ?? 0,
                'purity' => $item['Purity'] ?? 0,
                'acq_tx_no' => $item['Acq_Tx_No'] ?? '',
                'item_code' => $item['Item_Code'],
                'description' => $item['Item_Desc'],
                'quantity' => $item['Quantity'] ?? 0,
                'location' => $item['WH_Code'] ?? '',
                'brand' => $item['Brand'] ?? '',
            ];
        }
        return $results;
    }
}
