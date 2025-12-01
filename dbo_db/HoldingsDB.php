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
