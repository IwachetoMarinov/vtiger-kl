<?php
/* dbo_db/ActivitySummary.php */

// ini_set('display_errors', 1); error_reporting(E_ALL);

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

        if (!$this->connection || !is_resource($this->connection)) return [];

        $params = [];
        $where  = '';

        if ($customer_id) {
            $where = "WHERE [Party_Code] = ?";
            $params[] = $customer_id;
        }

        $sql = "SELECT * FROM [HFS_SQLEXPRESS].[GPM].[dbo].[DW_TxHx] $where order by [Tx_Date] DESC";

        $summary = GetDBRows::getRows($this->connection, $sql, $params);

        // echo "<pre>";
        // print_r($summary);
        // echo "</pre>";

        $results  = [];
        foreach ($summary as $item) {
            $description = $item['Description'] ? $item['Description'] : $item['Tx_Desc'] ?? '';

            $results[] = [
                'voucher_no' => $item['Tx_No'] ?? '',
                'voucher_type' => $item['Tx_Type'] ?? '',
                'description' => $description,
                'table_name' => $item['TableName'] ?? '',
                'usd_val' => $item['Matched_Amt'] ? floatval($item['Matched_Amt']) : 0.00,
                'doctype' => $item['Description'] ?? '',
                'currency' => $item['Curr_Code'] ?? '',
                'document_date' => $item['Tx_Date'] instanceof \DateTime ? $item['Tx_Date']->format('Y-m-d') : $item['Tx_Date'],
                'posting_date' => $item['Appr_Date'] instanceof \DateTime ? $item['Appr_Date']->format('Y-m-d') : $item['Appr_Date'],
                'мatched_аmt' => isset($item['Matched_Amt']) ? floatval($item['Matched_Amt']) : 0.00,
                'amount_in_account_currency' =>
                isset($item['TxAmt']) ? (float) $item['TxAmt'] : (isset($item['Tx_Amt']) ? (float) $item['Tx_Amt'] : 0.00),
            ];
        }

        return $results;
    }

    public function getTCPrintPreviewData($doc_no = null, $table_name = null)
    {
        if (!$doc_no || !$table_name || !$this->connection)  return [];

        if (!preg_match('/^[A-Za-z0-9_]+$/', $table_name)) return [];

        try {
            $transaction  = $this->getSingleTransaction($doc_no);

            $params = [];
            $where  = '';

            if ($doc_no) {
                $where = "WHERE [Tx_No] = ?";
                $params[] = $doc_no;
            }

            $sql = "
                SELECT *
                FROM [HFS_SQLEXPRESS].[GPM].[dbo].[$table_name] $where";

            $summary = GetDBRows::getRows($this->connection, $sql, $params);

            $items = $this->mapTransactionItems($summary, $transaction);

            $transaction['barItems'] = $items;

            return $transaction;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getDocumentPrintPreviewData($doc_no = null, $table_name = null)
    {
        if (!$doc_no || !$table_name || !$this->connection) return [];

        if (!preg_match('/^[A-Za-z0-9_]+$/', $table_name)) return [];

        try {
            $transaction = $this->getSingleTransaction($doc_no);

            $params = [];
            $where  = '';

            if ($doc_no) {
                $where = "WHERE [Tx_No] = ?";
                $params[] = $doc_no;
            }

            // Get items for this transaction
            $sql = "
                SELECT * FROM [HFS_SQLEXPRESS].[GPM].[dbo].[$table_name] $where";

            $summary = GetDBRows::getRows($this->connection, $sql, $params);

            $items = $this->mapTransactionItems($summary, $transaction);

            $transaction['barItems'] = $items;
            return $transaction;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getSingleTransaction($doc_no)
    {
        if (!$doc_no || !$this->connection) {
            die(print_r(sqlsrv_errors(), true));
            return [];
        }

        $params = [];
        $where  = '';

        if ($doc_no) {
            $where = "WHERE [Tx_No] = ?";
            $params[] = $doc_no;
        }

        $sql = "SELECT * FROM [HFS_SQLEXPRESS].[GPM].[dbo].[DW_TxHx] $where";
        $summary = GetDBRows::getRows($this->connection, $sql, $params);

        if (count($summary) === 0) return [];

        $row = $summary[0];

        return [
            'docNo'        => $row['Tx_No'] ?? '',
            'GST'          => true,
            'voucherType'  => $row['Tx_Type'] ?? '',
            'currency'     => $row['Curr_Code'] ?? '',
            'description'  => $row['Description'] ?? '',
            'doctype'      => $row['Tx_Type'] ?? '',
            'documentDate' =>
            isset($row['Tx_Date']) && $row['Tx_Date'] instanceof \DateTime
                ? $row['Tx_Date']->format('Y-m-d')
                : ($row['Tx_Date'] ?? null),
            'postingDate' =>
            isset($row['Appr_Date']) && $row['Appr_Date'] instanceof \DateTime
                ? $row['Appr_Date']->format('Y-m-d')
                : ($row['Appr_Date'] ?? null),
            'grandTotal'   => isset($row['Tx_Amt']) ? (float)$row['Tx_Amt'] : 0.00,
            'totalusdVal'  => isset($row['Matched_Amt']) ? (float)$row['Matched_Amt'] : 0.00,
        ];
    }


    protected function mapTransactionItems($summary, $transaction)
    {
        $items = [];

        foreach ($summary as $item) {
            $totalItemAmount = 0.00;
            if (isset($item['Total_Item_Amt'])) {
                $totalItemAmount = (float)$item['Total_Item_Amt'];
            } elseif (isset($item['DN_Det_Amt'])) {
                $totalItemAmount = (float)$item['DN_Det_Amt'];
            } elseif (isset($item['TxAmt'])) {
                $totalItemAmount = (float)$item['TxAmt'];
            }

            $items[] = (object) [
                'quantity'          => isset($item['Qty']) ? (int)$item['Qty'] : 1,
                'transactionType'   => $item['Tax_Type'] ?? '',
                'currency'          => $item['Curr_Code'] ?? '',
                'metal'             => $item['MT_Code'] ?? '',
                'metal_name'        => $item['MT_Name'] ?? '',
                'warehouse'         => $item['WH_Name'] ?? '',
                'description'       => isset($item['Description']) ? $item['Description'] : (isset($item['Item_Desc']) ? $item['Item_Desc'] : ''),

                'taxAmount'         => isset($item['Tx_Amt']) ? (float)$item['Tx_Amt'] : 0.00,

                'postingDate'       =>
                isset($item['Appr_Date']) && $item['Appr_Date'] instanceof \DateTime
                    ? $item['Appr_Date']->format('Y-m-d')
                    : ($item['Appr_Date'] ?? null),

                'documentDate'      =>
                isset($item['Tx_Date']) && $item['Tx_Date'] instanceof \DateTime
                    ? $item['Tx_Date']->format('Y-m-d')
                    : ($item['Tx_Date'] ?? null),

                'exchangeRate'      => isset($item['Exc_Rate']) ? (float)$item['Exc_Rate'] : 0.00,
                'itemCode'          => $item['Item_Code'] ?? '',
                'itemDescription'   => $item['Item_Desc'] ?? '',
                'fineOz'            => isset($item['FineOz']) ? (float)$item['FineOz'] : 0.00,
                'totalFineOz'       => isset($item['Tot_FineOz']) ? (float)$item['Tot_FineOz'] : 0.00,
                'grossOz'           => isset($item['GrossOz']) ? (float)$item['GrossOz'] : 0.00,
                'purity'            => $item['Purity'] ?? '',
                'price'         => isset($item['Item_Price']) ? (float)$item['Item_Price'] : 0.00,
                'unitPrice'         => isset($item['Unit_Price']) ? (float)$item['Unit_Price'] : 0.00,
                'premium'          => isset($item['Premium_Perc']) ? (float)$item['Premium_Perc'] : 0.00,
                'premiumFinal'      => isset($item['Premium_Final']) ? (float)$item['Premium_Final'] : 0.00,
                'totalItemAmount'   => $totalItemAmount,
                'totalItemDcAmount' => isset($item['Total_Item_DC_Amt']) ? (float)$item['Total_Item_DC_Amt'] : 0.00,

                'serialNumbers'     => $item['Ser_No'] ?? '',
                'serials'           => isset($item['Ser_No']) ? explode(',', $item['Ser_No']) : [],

                'voucherType'       => $transaction['voucherType'] ?? '',
                'docNo'             => $item['Tx_No'] ?? '',

                'weight' => max((float)($item['Weight'] ?? 0), 1),
                'barNumber'         => $item['Bar_No'] ?? '',
                'pureOz'            => isset($item['GrossOz']) ? (float)$item['GrossOz'] : 0.00,
                'remarks'            => isset($item['Remarks']) ? $item['Remarks'] : "",
                'otherCharge'       => isset($item['Other_Charge']) ? (float)$item['Other_Charge'] : 0.00,
                'narration'         => $item['Narration'] ?? '',
                'longDesc'          => $item['Long_Desc'] ?? '',
            ];
        }

        return $items;
    }
}
