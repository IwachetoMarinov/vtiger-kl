<?php
/* dbo_db/ActivitySummary.php */

namespace dbo_db;

include_once 'data/CRMEntity.php';
include_once 'modules/Users/Users.php';
include_once 'helpers/DBConnection.php';

use helpers\DBConnection;

class ActivitySummary
{
    private $connection;

    public function __construct()
    {
        $this->connection = DBConnection::getConnection();
    }

    public function getActivitySummary($customer_id = null, $startDate = null, $endDate = null)
    {
        if (!$this->connection) die(print_r(sqlsrv_errors(), true));

        $params = [];
        $where  = '';

        if ($customer_id) {
            $where = "WHERE [Party_Code] = ?";
            $params[] = $customer_id;
        }

        $sql = "SELECT * FROM [HFS_SQLEXPRESS].[GPM].[dbo].[DW_ActivitySumm] $where";

        $stmt = sqlsrv_query($this->connection, $sql, $params);

        if ($stmt === false) die(print_r(sqlsrv_errors(), true));

        $summary = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $summary[] = $row;
        }

        sqlsrv_free_stmt($stmt);
        // Map and return data in this kind of structure 
        // [
        // 			'voucher_no' => 'SAL/2025/001',
        // 			'voucher_type' => 'Sales Invoice',
        // 			'doctype' => 'Sales Invoice',
        // 			'posting_date' => '2025-01-10',
        // 			'amount_in_account_currency' => 25000.00
        // 		],

        $results  = [];
        foreach ($summary as $item) {
            $results[] = [
                'voucher_no' => $item['TxNo'],
                'voucher_type' => $item['TxType'],
                'doctype' => $item['DocType'] ?? 'Sales Invoice',
                'posting_date' => $item['Tx_Date'] instanceof \DateTime ? $item['Tx_Date']->format('Y-m-d') : $item['Tx_Date'],
                'amount_in_account_currency' => $item['TxAmt'] ?? 0.00
            ];
        }

        return $results;
    }
}
