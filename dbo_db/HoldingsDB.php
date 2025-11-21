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
                'description' => $item['Item_Desc'],
                'quantity' => $item['Quantity'] ?? 0,
                'location' => $item['WH_Code'] ?? '',
            ];
        }
        return $results;
    }
}
