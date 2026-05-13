<?php

namespace adapters;

include_once 'adapters/AdapterHelper.php';

use adapters\AdapterHelper;

class ActivitySummaryMapper
{
    public static function normalize(array $item): array
    {
        $warnings = [];

        return [
            'voucher_no' => AdapterHelper::firstValue($item, ['Tx_No', 'Transaction_No'], '', $warnings, 'voucher_no'),
            'voucher_type' => AdapterHelper::firstValue($item, ['Tx_Type', 'Transaction_Type'], '', $warnings, 'voucher_type'),
            'description' => AdapterHelper::firstValue($item, ['Description', 'Tx_Desc'], '', $warnings, 'description'),
            'scr_description' => AdapterHelper::firstValue($item, ['SCR_Desc'], '', $warnings, 'scr_description'),
            'table_name' => AdapterHelper::firstValue($item, ['Tx1_TblName', 'TableName'], '', $warnings, 'table_name'),
            'transaction_2' => AdapterHelper::firstValue($item, ['Tx2'], '', $warnings, 'transaction_2'),
            'table_name_2' => AdapterHelper::firstValue($item, ['Tx2_TblName'], '', $warnings, 'table_name_2'),
            'transaction_3' => AdapterHelper::firstValue($item, ['Tx3'], '', $warnings, 'transaction_3'),
            'table_name_3' => AdapterHelper::firstValue($item, ['Tx3_TblName'], '', $warnings, 'table_name_3'),

            'matched_amt' => AdapterHelper::firstValue($item, ['Matched_Amt', 'Match_Amt', 'Matched_Amount'], 0, $warnings, 'matched_amt'),
            'tx_amount' => AdapterHelper::firstValue($item, ['TxAmt', 'Tx_Amt', 'Transaction_Amount'], 0, $warnings, 'tx_amount'),
            'currency' => AdapterHelper::firstValue($item, ['Curr_Code', 'Currency'], '', $warnings, 'currency'),
            'document_date' => AdapterHelper::firstValue($item, ['Tx_Date', 'Document_Date'], null, $warnings, 'document_date'),
            'posting_date' => AdapterHelper::firstValue($item, ['Appr_Date', 'Posting_Date'], null, $warnings, 'posting_date'),

            '_warnings' => $warnings,
        ];
    }

    public static function mapTransactionRow(array $item): array
    {
        $normalized = self::normalize($item);

        return [
            'voucher_no' => $normalized['voucher_no'],
            'voucher_type' => $normalized['voucher_type'],
            'description' => $normalized['description'],
            'scr_description' => $normalized['scr_description'],
            'table_name' => $normalized['table_name'],
            'transaction_2' => $normalized['transaction_2'],
            'table_name_2' => $normalized['table_name_2'],
            'transaction_3' => $normalized['transaction_3'],
            'table_name_3' => $normalized['table_name_3'],
            'doctype' => $normalized['description'],
            'currency' => $normalized['currency'],
            'document_date' => AdapterHelper::formatDate($normalized['document_date']),
            'amount_in_account_currency' => AdapterHelper::toFloat($normalized['tx_amount']),
            '_warnings' => $normalized['_warnings'],
        ];
    }

    public static function mapActivitySummaryRow(array $item): array
    {
        $normalized = self::normalize($item);

        return [
            'voucher_no' => $normalized['voucher_no'],
            'voucher_type' => $normalized['voucher_type'],
            'description' => $normalized['description'],
            'table_name' => $normalized['table_name'],
            'usd_val' => AdapterHelper::toFloat($normalized['matched_amt']),
            'doctype' => $normalized['description'],
            'currency' => $normalized['currency'],
            'document_date' => AdapterHelper::formatDate($normalized['document_date']),
            'posting_date' => AdapterHelper::formatDate($normalized['posting_date']),
            'matched_amt' => AdapterHelper::toFloat($normalized['matched_amt']),
            'amount_in_account_currency' => AdapterHelper::toFloat($normalized['tx_amount']),
            '_warnings' => $normalized['_warnings'],
        ];
    }

    public static function mapSingleTransaction(array $row): array
    {
        $normalized = self::normalizeTransactionHeader($row);

        return [
            'docNo' => $normalized['doc_no'],
            'GST' => true,
            'voucherType' => $normalized['voucher_type'],
            'currency' => $normalized['currency'],
            'description' => $normalized['description'],
            'doctype' => $normalized['voucher_type'],
            'documentDate' => AdapterHelper::formatDate($normalized['document_date']),
            'postingDate' => AdapterHelper::formatDate($normalized['posting_date']),
            'grandTotal' => AdapterHelper::toFloat($normalized['grand_total']),
            'totalusdVal' => AdapterHelper::toFloat($normalized['matched_amt']),
            '_warnings' => $normalized['_warnings'],
        ];
    }

    public static function normalizeTransactionHeader(array $row): array
    {
        $warnings = [];

        return [
            'doc_no' => AdapterHelper::firstValue($row, ['Tx_No', 'Doc_No'], '', $warnings, 'doc_no'),
            'voucher_type' => AdapterHelper::firstValue($row, ['Tx_Type', 'Doc_Type'], '', $warnings, 'voucher_type'),
            'currency' => AdapterHelper::firstValue($row, ['Curr_Code', 'Currency'], '', $warnings, 'currency'),
            'description' => AdapterHelper::firstValue($row, ['Description', 'Tx_Desc'], '', $warnings, 'description'),
            'document_date' => AdapterHelper::firstValue($row, ['Tx_Date', 'Document_Date'], null, $warnings, 'document_date'),
            'posting_date' => AdapterHelper::firstValue($row, ['Appr_Date', 'Del_Date', 'Posting_Date'], null, $warnings, 'posting_date'),
            'grand_total' => AdapterHelper::firstValue($row, ['Tx_Amt', 'TxAmt', 'Grand_Total'], 0, $warnings, 'grand_total'),
            'matched_amt' => AdapterHelper::firstValue($row, ['Matched_Amt', 'Match_Amt', 'Matched_Amount'], 0, $warnings, 'matched_amt'),

            '_warnings' => $warnings,
        ];
    }
}
