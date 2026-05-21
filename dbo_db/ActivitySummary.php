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

        if (!$errors || !is_array($errors)) return null;

        $messages = [];

        foreach ($errors as $error) {
            $message = $error['message'] ?? '';
            $sqlState = $error['SQLSTATE'] ?? '';
            $code = $error['code'] ?? null;

            // Ignore informational SQL Server messages
            if (
                $sqlState === '01000' &&
                in_array($code, [5701, 5703])
            ) {
                continue;
            }

            // Remove Microsoft / driver prefixes
            $message = preg_replace('/(\[.*?\])+/', '', $message);
            $message = trim($message);

            // Human readable replacements
            if (stripos($message, 'Login failed for user') !== false) $message = 'Authentication with the ERP database failed.';

            if (stripos($message, 'Cannot open database') !== false)
                $message = 'The ERP database is currently unavailable.';

            if (!empty($message)) {
                $messages[] = $message;
            }
        }

        // Remove duplicates
        $messages = array_unique($messages);

        // If nothing meaningful remains
        if (empty($messages)) return null;

        return implode(' ', $messages);
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

    protected function mapTransactionItems($summary, $transaction)
    {
        $items = [];

        foreach ($summary as $item) {
            $items[] = TransactionItemMapper::map($item, $transaction);
        }

        return $items;
    }

    public function getMonthlyTransactions($client_id, $start_date, $end_date, $currency = null)
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

            if ($currency) {
                $where .= " AND [Curr_Code] = ?";
                $params[] = $currency;
            }

            $sql = "SELECT * FROM {$this->database_prefix}.[DW_TxHxv2] {$where} ORDER BY [Tx_Date] DESC";

            $summary = GetDBRows::getRows($this->connection, $sql, $params);

            $results  = [];
            foreach ($summary as $item) {
                $description = $item['Description'] ? $item['Description'] : $item['Tx_Desc'] ?? '';

                $results[] = [
                    'voucher_no' => $item['Tx_No'] ?? '',
                    'voucher_type' => $item['Tx_Type'] ?? '',
                    'description' => $description,
                    'table_name' => $item['Tx1_TblName'] ?? '',
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

            // return array_map(function ($item) {
            //     return ActivitySummaryMapper::mapActivitySummaryRow($item);
            // }, $summary);
        } catch (\Exception $e) {
            return [];
        }
    }
}
