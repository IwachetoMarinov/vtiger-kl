<?php
/* dbo_db/ActivitySummary.php */

// ini_set('display_errors', 1); error_reporting(E_ALL);

namespace dbo_db;

include_once 'data/CRMEntity.php';
include_once 'modules/Users/Users.php';
include_once 'helpers/DBConnection.php';
include_once 'dbo_db/GetDBRows.php';
include_once 'adapters/ActivitySummaryMapper.php';
include_once 'adapters/TransactionItemMapper.php';

use helpers\DBConnection;
use adapters\ActivitySummaryMapper;
use adapters\TransactionItemMapper;

class ActivitySummary
{
    private $connection;
    private $database_prefix;

    public function __construct()
    {
        $this->connection = DBConnection::getConnection();
        $this->database_prefix = DBConnection::getDatabasePrefix();
    }

    public function getPIActivitySummary($customer_id = null)
    {
        if (!$customer_id || !$this->connection) return [];

        $where = "WHERE [Party_Code] = ?";
        $params[] = $customer_id;

        $sql = "SELECT * FROM $this->database_prefix.[DW_TxHxv2] $where order by [Tx_Date] DESC";

        $summary = GetDBRows::getRows($this->connection, $sql, $params);

        if (!is_array($summary) || count($summary) === 0) return [];

        $results  = [];
        foreach ($summary as $item) {
            // $description = $item['Description'] ? $item['Description'] : $item['Tx_Desc'] ?? '';

            // $results[] = [
            //     'voucher_no' => $item['Tx_No'] ?? '',
            //     'voucher_type' => $item['Tx_Type'] ?? '',
            //     'description' => $description,
            //     'scr_description' => $item['SCR_Desc'] ?? '',
            //     'table_name' => $item['Tx1_TblName'] ?? '',
            //     'transaction_2' => $item['Tx2'] ?? '',
            //     'table_name_2' => $item['Tx2_TblName'] ?? '',
            //     'transaction_3' => $item['Tx3'] ?? '',
            //     'table_name_3' => $item['Tx3_TblName'] ?? '',
            //     'usd_val' => $item['Matched_Amt'] ? floatval($item['Matched_Amt']) : 0.00,
            //     'doctype' => $item['Description'] ?? '',
            //     'currency' => $item['Curr_Code'] ?? '',
            //     'document_date' => $item['Tx_Date'] instanceof \DateTime ? $item['Tx_Date']->format('Y-m-d') : $item['Tx_Date'],
            //     'posting_date' => $item['Appr_Date'] instanceof \DateTime ? $item['Appr_Date']->format('Y-m-d') : $item['Appr_Date'],
            //     'мatched_аmt' => isset($item['Matched_Amt']) ? floatval($item['Matched_Amt']) : 0.00,
            //     'amount_in_account_currency' =>
            //     isset($item['TxAmt']) ? (float) $item['TxAmt'] : (isset($item['Tx_Amt']) ? (float) $item['Tx_Amt'] : 0.00),
            // ];
            $results[] = ActivitySummaryMapper::mapTransactionRow($item);
        }

        return $results;
    }

    public function getActivitySummaryOpeningBalance($customer_id = null, $currency = null, $start_date = null)
    {
        if (!$customer_id || !$currency || !$start_date || !$this->connection) return 0.00;

        $where = "WHERE [Party_Code] = ?";
        $params[] = $customer_id;

        if ($currency) {
            $where .= empty($where) ? "WHERE" : " AND";
            $where .= " [Curr_Code] = ?";
            $params[] = $currency;
        }

        if ($start_date) {
            $where .= empty($where) ? "WHERE" : " AND";
            $where .= " [Tx_Date] < ?";
            $params[] = $start_date;
        }

        $sql = "SELECT 'Opening Balance', sum(Tx_Amt) FROM $this->database_prefix.[DW_TxHx] $where";

        try {
            $summary = GetDBRows::getRows($this->connection, $sql, $params);

            $amount = 0.00;

            if (is_array($summary) && count($summary) > 0) {
                foreach ($summary as $row) {
                    // check if row has a value
                    if (isset($row) && !is_null($row)) {
                        if (is_array($row)) {
                            $value = reset($row);
                            $amount += floatval($value);
                        }
                    }
                }
            }

            return $amount;
        } catch (\Exception $e) {
            return 0.00;
        }
    }

    public function getActivitySummary($customer_id = null)
    {
        if (!$customer_id) return [];

        if (!$this->connection || !is_resource($this->connection)) return [];

        $where = "WHERE [Party_Code] = ?";
        $params[] = $customer_id;

        $sql = "SELECT * FROM $this->database_prefix.[DW_TxHx] $where order by [Tx_Date] DESC";

        $summary = GetDBRows::getRows($this->connection, $sql, $params);

        $results  = [];
        foreach ($summary as $item) {
            // $description = $item['Description'] ? $item['Description'] : $item['Tx_Desc'] ?? '';

            // $results[] = [
            //     'voucher_no' => $item['Tx_No'] ?? '',
            //     'voucher_type' => $item['Tx_Type'] ?? '',
            //     'description' => $description,
            //     'table_name' => $item['TableName'] ?? '',
            //     'usd_val' => $item['Matched_Amt'] ? floatval($item['Matched_Amt']) : 0.00,
            //     'doctype' => $item['Description'] ?? '',
            //     'currency' => $item['Curr_Code'] ?? '',
            //     'document_date' => $item['Tx_Date'] instanceof \DateTime ? $item['Tx_Date']->format('Y-m-d') : $item['Tx_Date'],
            //     'posting_date' => $item['Appr_Date'] instanceof \DateTime ? $item['Appr_Date']->format('Y-m-d') : $item['Appr_Date'],
            //     'мatched_аmt' => isset($item['Matched_Amt']) ? floatval($item['Matched_Amt']) : 0.00,
            //     'amount_in_account_currency' =>
            //     isset($item['TxAmt']) ? (float) $item['TxAmt'] : (isset($item['Tx_Amt']) ? (float) $item['Tx_Amt'] : 0.00),
            // ];

            $results[] = ActivitySummaryMapper::mapActivitySummaryRow($item);
        }

        return $results;
    }

    public function getTCPrintPreviewData($doc_no = null, $table_name = null)
    {
        if (!$doc_no || !$table_name || !$this->connection)  return [];

        if (!preg_match('/^[A-Za-z0-9_]+$/', $table_name)) return [];

        try {
            $transaction  = $this->getSingleTransaction($doc_no, $table_name);

            $where = "WHERE [Tx_No] = ?";
            $params[] = $doc_no;

            $sql = "
                SELECT *
                FROM $this->database_prefix.[$table_name] $where";

            $summary = GetDBRows::getRows($this->connection, $sql, $params);

            $items = $this->mapTransactionItems($summary, $transaction);

            $transaction['barItems'] = $items;

            return $transaction;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function checkConnection(): ?string
    {
        $errors = \sqlsrv_errors();

        if (!$errors || !is_array($errors))
            return 'Database connection is temporarily unavailable.';

        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error['message'] ?? 'Unknown SQL Server error.';
        }

        return implode(' | ', $messages);
    }

    public function getActivityYears($customer_id = null)
    {
        if (!$customer_id || !$this->connection) return [];

        try {
            $where = "WHERE [Party_Code] = ?";
            $params[] = $customer_id;

            $sql = "SELECT DISTINCT Year(Tx_Date) AS Year FROM $this->database_prefix.[DW_TxHxv2] $where";

            $years = GetDBRows::getRows($this->connection, $sql, $params);

            return array_map(function ($row) {
                return $row['Year'] ?? '';
            }, $years);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getTransactionCurrencies($customer_id = null)
    {
        if (!$customer_id || !$this->connection) return [];

        try {
            $where = "WHERE [Party_Code] = ?";
            $params[] = $customer_id;

            $sql = "SELECT DISTINCT Curr_Code FROM $this->database_prefix.[DW_TxHxv2] $where";

            $currencies = GetDBRows::getRows($this->connection, $sql, $params);

            // return array but remove null or empty values
            $currencies = array_filter($currencies, function ($row) {
                return !empty($row['Curr_Code']);
            });

            return array_map(function ($row) {
                return $row['Curr_Code'] ?? '';
            }, $currencies);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getProformaInvoiceData($doc_no = null, $table_name = null)
    {
        if (!$doc_no || !$table_name || !$this->connection) return [];

        if (!preg_match('/^[A-Za-z0-9_]+$/', $table_name)) return [];

        try {
            $transaction = $this->getProformaInvoiceTransaction($doc_no, $table_name);

            $where = "WHERE [Tx_No] = ?";
            $params[] = $doc_no;

            $sql = "
                SELECT * FROM $this->database_prefix.[$table_name] $where";

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
            $transaction = $this->getSingleTransaction($doc_no, $table_name);

            $where = "WHERE [Tx_No] = ?";
            $params[] = $doc_no;

            $sql = "
                SELECT * FROM $this->database_prefix.[$table_name] $where";

            $summary = GetDBRows::getRows($this->connection, $sql, $params);

            $items = $this->mapTransactionItems($summary, $transaction);

            $transaction['barItems'] = $items;
            return $transaction;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getProformaInvoiceTransaction($doc_no, $table_name)
    {
        try {
            if (!$doc_no || !$table_name || !$this->connection) return [];

            $params = [$doc_no];

            $sql = "SELECT * FROM $this->database_prefix.[$table_name] WHERE [Tx_No] = ?";

            $summary = GetDBRows::getRows($this->connection, $sql, $params);

            if (count($summary) === 0) return [];

            return ActivitySummaryMapper::mapSingleTransaction($summary[0]);
        } catch (\Exception $e) {
            return [];
        }
    }

    // protected function getProformaInvoiceTransaction($doc_no, $table_name)
    // {
    //     try {
    //         $params = [];
    //         $where  = '';

    //         if ($doc_no) {
    //             $where = "WHERE [Tx_No] = ?";
    //             $params[] = $doc_no;
    //         }

    //         $sql = "SELECT * FROM $this->database_prefix.[$table_name] $where";

    //         $summary = GetDBRows::getRows($this->connection, $sql, $params);

    //         if (count($summary) === 0) return [];
    //         $row = $summary[0];

    //         return [
    //             'docNo'        => $row['Tx_No'] ?? '',
    //             'GST'          => true,
    //             'voucherType'  => $row['Tx_Type'] ?? '',
    //             'currency'     => $row['Curr_Code'] ?? '',
    //             'description'  => $row['Description'] ?? '',
    //             'doctype'      => $row['Tx_Type'] ?? '',
    //             'documentDate' =>
    //             isset($row['Tx_Date']) && $row['Tx_Date'] instanceof \DateTime
    //                 ? $row['Tx_Date']->format('Y-m-d')
    //                 : ($row['Tx_Date'] ?? null),
    //             'postingDate' =>
    //             isset($row['Appr_Date']) && $row['Appr_Date'] instanceof \DateTime
    //                 ? $row['Appr_Date']->format('Y-m-d')
    //                 : ($row['Appr_Date'] ?? null),
    //             'grandTotal'   => isset($row['Tx_Amt']) ? (float)$row['Tx_Amt'] : 0.00,
    //             'totalusdVal'  => isset($row['Matched_Amt']) ? (float)$row['Matched_Amt'] : 0.00,
    //         ];
    //     } catch (\Exception $e) {
    //         return [];
    //     }
    // }

    protected function getSingleTransaction($doc_no, $table_name = "DW_TxHx")
    {
        if (!$doc_no || !$this->connection) return [];

        $params = [];
        $where = "WHERE [Tx_No] = ?";
        $params[] = $doc_no;

        $sql = "SELECT * FROM $this->database_prefix.[$table_name] $where";
        $summary = GetDBRows::getRows($this->connection, $sql, $params);

        if (count($summary) === 0) return [];

        return ActivitySummaryMapper::mapSingleTransaction($summary[0]);
    }

    protected function getSingleTransactionOld($doc_no, $table_name = "DW_TxHx")
    {
        if (!$doc_no || !$this->connection) return [];

        $where = "WHERE [Tx_No] = ?";
        $params[] = $doc_no;

        $sql = "SELECT * FROM $this->database_prefix.[$table_name] $where";
        $summary = GetDBRows::getRows($this->connection, $sql, $params);

        if (count($summary) === 0) return [];
        $row = $summary[0];

        $tx_type  = isset($row['Tx_Type']) ? $row['Tx_Type'] : null;

        if (!$tx_type && isset($row['Doc_Type'])) $tx_type = $row['Doc_Type'];

        $posting_date = isset($row['Appr_Date']) && $row['Appr_Date'] instanceof \DateTime
            ? $row['Appr_Date']->format('Y-m-d')
            : ($row['Appr_Date'] ?? null);

        if (!$posting_date && isset($row['Del_Date'])) {
            $posting_date = isset($row['Del_Date']) && $row['Del_Date'] instanceof \DateTime
                ? $row['Del_Date']->format('Y-m-d')
                : ($row['Del_Date'] ?? null);
        }

        return [
            'docNo'        => $row['Tx_No'] ?? '',
            'GST'          => true,
            'voucherType'  => $tx_type ?? '',
            'currency'     => $row['Curr_Code'] ?? '',
            'description'  => $row['Description'] ?? '',
            'doctype'      => $tx_type ?? '',
            'documentDate' =>
            isset($row['Tx_Date']) && $row['Tx_Date'] instanceof \DateTime
                ? $row['Tx_Date']->format('Y-m-d')
                : ($row['Tx_Date'] ?? null),
            'postingDate' => $posting_date,
            'grandTotal'   => isset($row['Tx_Amt']) ? (float)$row['Tx_Amt'] : 0.00,
            'totalusdVal'  => isset($row['Matched_Amt']) ? (float)$row['Matched_Amt'] : 0.00,
        ];
    }


    protected function mapTransactionItems($summary, $transaction)
    {
        $items = [];

        foreach ($summary as $item) {
            $items[] = TransactionItemMapper::map($item, $transaction);

            //     $items[] = (object) [
            //         'quantity'          => isset($item['Qty']) ? (int)$item['Qty'] : 1,
            //         'currency'          => $item['Curr_Code'] ?? '',
            //         'metal'             => $item['MT_Code'] ?? '',
            //         'metal_name'        => $item['MT_Name'] ?? '',
            //         'metal_type_code'        => $item['Metal_Type_Code'] ?? '',
            //         'warehouse'         => $item['WH_Name'] ?? '',
            //         'transactionType'         => $transactionType,
            //         'description'       => $description,

            //         'taxAmount'         => isset($item['Tx_Amt']) ? (float)$item['Tx_Amt'] : 0.00,
            //         'spotPrice'         => isset($item['Spot_Price']) ? (float)$item['Spot_Price'] : 0.00,
            //         'averageSpotPrice'  => isset($item['Avg_Spot_Price']) ? (float)$item['Avg_Spot_Price'] : 0.00,

            //         'postingDate'       =>
            //         isset($item['Appr_Date']) && $item['Appr_Date'] instanceof \DateTime
            //             ? $item['Appr_Date']->format('Y-m-d')
            //             : ($item['Appr_Date'] ?? null),

            //         'documentDate'      =>
            //         isset($item['Tx_Date']) && $item['Tx_Date'] instanceof \DateTime
            //             ? $item['Tx_Date']->format('Y-m-d')
            //             : ($item['Tx_Date'] ?? null),

            //         'exchangeRate'      => isset($item['Exc_Rate']) ? (float)$item['Exc_Rate'] : 0.00,
            //         'itemCode'          => $item['Item_Code'] ?? '',
            //         'itemDescription'   => $item['Item_Desc'] ?? '',
            //         'fineOz'            => isset($item['FineOz']) ? (float)$item['FineOz'] : 0.00,
            //         'totalFineOz'       => isset($item['Tot_FineOz']) ? (float)$item['Tot_FineOz'] : 0.00,
            //         'grossOz'           => isset($item['GrossOz']) ? (float)$item['GrossOz'] : 0.00,
            //         'purity'            => $item['Purity'] ?? '',
            //         'price'         => isset($item['Item_Price']) ? (float)$item['Item_Price'] : 0.00,
            //         'unitPrice'         => isset($item['Unit_Price']) ? (float)$item['Unit_Price'] : 0.00,
            //         'premium'          => isset($item['Premium_Perc']) ? $item['Premium_Perc'] : "",
            //         'premiumFinal'      => isset($item['Premium_Final']) ? (float)$item['Premium_Final'] : 0.00,
            //         'totalItemAmount'   => $totalItemAmount,
            //         'totalItemDcAmount' => isset($item['Total_Item_DC_Amt']) ? (float)$item['Total_Item_DC_Amt'] : 0.00,

            //         'serialNumbers'     => $item['Ser_No'] ? $this->sanitizeSerialNumbers($item['Ser_No']) : '',
            //         'serials'           => isset($item['Ser_No']) ? explode(',', $item['Ser_No']) : [],

            //         'voucherType'       => $transaction['voucherType'] ?? '',
            //         'docNo'             => $item['Tx_No'] ?? '',

            //         'weight' => max((float)($item['Weight'] ?? 0), 1),
            //         'barNumber'         => $item['Bar_No'] ?? '',
            //         'pureOz'            => isset($item['GrossOz']) ? (float)$item['GrossOz'] : 0.00,
            //         'remarks'            => isset($item['Remarks']) ? $item['Remarks'] : "",
            //         'otherCharge'       => isset($item['Other_Charge']) ? (float)$item['Other_Charge'] : 0.00,
            //         'narration'         => $item['Narration'] ?? '',
            //         'longDesc'          => $item['Long_Desc'] ?? '',
            //         'creditNoteAmount'  => $creditNoteAmount,
            //     ];
        }

        return $items;
    }

    public function getMonthlyTransactions($client_id, $start_date, $end_date)
    {
        if (!$client_id || !$this->connection || !$start_date || !$end_date) return [];

        try {
            $where = "WHERE [Party_Code] = ?";
            $params[] = $client_id;

            if ($start_date) {
                $where .= empty($where) ? "WHERE" : " AND";
                $where .= " [Tx_Date] >= ?";
                $params[] = $start_date;
            }

            if ($end_date) {
                $where .= empty($where) ? "WHERE" : " AND";
                $where .= " [Tx_Date] <= ?";
                $params[] = $end_date;
            }

            $sql = "SELECT * FROM $this->database_prefix.[DW_TxHx] $where order by [Tx_Date] DESC";

            $summary = GetDBRows::getRows($this->connection, $sql, $params);

            // $results  = [];
            // foreach ($summary as $item) {
            //     $description = $item['Description'] ? $item['Description'] : $item['Tx_Desc'] ?? '';

            //     $results[] = [
            //         'voucher_no' => $item['Tx_No'] ?? '',
            //         'voucher_type' => $item['Tx_Type'] ?? '',
            //         'description' => $description,
            //         'table_name' => $item['TableName'] ?? '',
            //         'usd_val' => $item['Matched_Amt'] ? floatval($item['Matched_Amt']) : 0.00,
            //         'doctype' => $item['Description'] ?? '',
            //         'currency' => $item['Curr_Code'] ?? '',
            //         'document_date' => $item['Tx_Date'] instanceof \DateTime ? $item['Tx_Date']->format('Y-m-d') : $item['Tx_Date'],
            //         'posting_date' => $item['Appr_Date'] instanceof \DateTime ? $item['Appr_Date']->format('Y-m-d') : $item['Appr_Date'],
            //         'matched_amt' => isset($item['Matched_Amt']) ? floatval($item['Matched_Amt']) : 0.00,
            //         'amount_in_account_currency' =>
            //         isset($item['TxAmt']) ? (float) $item['TxAmt'] : (isset($item['Tx_Amt']) ? (float) $item['Tx_Amt'] : 0.00),
            //     ];
            // }

            // return $results;

            return array_map(function ($item) {
                return ActivitySummaryMapper::mapActivitySummaryRow($item);
            }, $summary);
        } catch (\Exception $e) {
            return [];
        }
    }

    // protected function sanitizeSerialNumbers($serials): string
    // {
    //     if (!$serials) return '';

    //     // 1. Remove trailing semicolons
    //     $serials = preg_replace('/;+$/', '', $serials);

    //     // 2. Replace multiple semicolons in the middle with newline
    //     $serials = preg_replace('/;{2,}/', "\n", $serials);

    //     return $serials;
    // }
}
