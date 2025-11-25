<?php
/* dbo_db/ActivitySummary.php */

// ini_set('display_errors', 1);
// error_reporting(E_ALL);


namespace dbo_db;

include_once 'data/CRMEntity.php';
include_once 'modules/Users/Users.php';
include_once 'helpers/DBConnection.php';
include_once 'dbo_db/GetDBRows.php';

use helpers\DBConnection;

class ActivitySummary
{
    private $connection;

    public function __construct()
    {
        $this->connection = DBConnection::getConnection();
    }

    public function getActivitySummary($customer_id = null)
    {
        if (!$customer_id) return [];

        if (!$this->connection) die(print_r(sqlsrv_errors(), true));

        $params = [];
        $where  = '';

        if ($customer_id) {
            $where = "WHERE [Party_Code] = ?";
            $params[] = $customer_id;
        }

        $sql = "SELECT * FROM [HFS_SQLEXPRESS].[GPM].[dbo].[DW_ActivitySumm] $where";

        $summary = GetDBRows::getRows($this->connection, $sql, $params);

        // echo '<pre>';
        // var_dump($summary);
        // echo '</pre>';

        $results  = [];
        foreach ($summary as $item) {
            $results[] = [
                'voucher_no' => $item['TxNo'],
                'voucher_type' => $item['TxType'],
                'usd_val' => $item['Matched_Amt'] ? floatval($item['Matched_Amt']) : 0.00,
                'doctype' => $item['DocType'] ?? 'Sales Invoice',
                'document_date' => $item['Tx_Date'] instanceof \DateTime ? $item['Tx_Date']->format('Y-m-d') : $item['Tx_Date'],
                'posting_date' => $item['Appr_Date'] instanceof \DateTime ? $item['Appr_Date']->format('Y-m-d') : $item['Appr_Date'],
                'amount_in_account_currency' => $item['TxAmt'] ?? 0.00
            ];
        }

        return $results;
    }

    public function getTCPrintPreviewData($doc_no = null)
    {
        if (!$doc_no) return [];

        try {
            if (!$this->connection) die(print_r(sqlsrv_errors(), true));

            $params = [];
            $where  = '';

            if ($doc_no) {
                $where = "WHERE [TxNo] = ?";
                $params[] = $doc_no;
            }

            $sql = "SELECT * FROM [HFS_SQLEXPRESS].[GPM].[dbo].[DW_ActivitySumm] $where";

            $summary = GetDBRows::getRows($this->connection, $sql, $params);
            $results  = [];

            foreach ($summary as $item) {
                $results = [
                    'doc_no' => $item['TxNo'],
                    'voucher_type' => $item['TxType'],
                    'currency' => $item['Curr_Code'],
                    'doctype' => substr($item['TxNo'], 0, 3),
                    'document_date' => $item['Tx_Date'] instanceof \DateTime ? $item['Tx_Date']->format('Y-m-d') : $item['Tx_Date'],
                    'posting_date' => $item['Appr_Date'] instanceof \DateTime ? $item['Appr_Date']->format('Y-m-d') : $item['Appr_Date'],
                    'grand_total' => $item['TxAmt'] ?? 0.00,
                    'totalusd_val' => $item['Matched_Amt'] ?? 0.00,
                ];
            }

            return $results;
        } catch (\Exception $e) {
            // Handle exception or log error
            var_dump('Error: ' . $e->getMessage());
            return [];
        }
    }
}
