<?php

namespace adapters;

class ActivitySummaryMapper
{
    public static function normalize(array $item): array
    {
        $warnings = [];

        return [
            'voucher_no' => self::firstValue($item, ['Tx_No', 'Transaction_No'], '', $warnings, 'voucher_no'),
            'voucher_type' => self::firstValue($item, ['Tx_Type', 'Transaction_Type'], '', $warnings, 'voucher_type'),
            'description' => self::firstValue($item, ['Description', 'Tx_Desc'], '', $warnings, 'description'),
            'scr_description' => self::firstValue($item, ['SCR_Desc'], '', $warnings, 'scr_description'),
            'table_name' => self::firstValue($item, ['Tx1_TblName', 'TableName'], '', $warnings, 'table_name'),
            'transaction_2' => self::firstValue($item, ['Tx2'], '', $warnings, 'transaction_2'),
            'table_name_2' => self::firstValue($item, ['Tx2_TblName'], '', $warnings, 'table_name_2'),
            'transaction_3' => self::firstValue($item, ['Tx3'], '', $warnings, 'transaction_3'),
            'table_name_3' => self::firstValue($item, ['Tx3_TblName'], '', $warnings, 'table_name_3'),

            'matched_amt' => self::firstValue($item, ['Matched_Amt', 'Match_Amt', 'Matched_Amount'], 0, $warnings, 'matched_amt'),
            'tx_amount' => self::firstValue($item, ['TxAmt', 'Tx_Amt', 'Transaction_Amount'], 0, $warnings, 'tx_amount'),
            'currency' => self::firstValue($item, ['Curr_Code', 'Currency'], '', $warnings, 'currency'),
            'document_date' => self::firstValue($item, ['Tx_Date', 'Document_Date'], null, $warnings, 'document_date'),
            'posting_date' => self::firstValue($item, ['Appr_Date', 'Posting_Date'], null, $warnings, 'posting_date'),

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
            'usd_val' => self::toFloat($normalized['matched_amt']),
            'doctype' => $normalized['description'],
            'currency' => $normalized['currency'],
            'document_date' => self::formatDate($normalized['document_date']),
            'posting_date' => self::formatDate($normalized['posting_date']),
            'matched_amt' => self::toFloat($normalized['matched_amt']),
            'amount_in_account_currency' => self::toFloat($normalized['tx_amount']),
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
            'usd_val' => self::toFloat($normalized['matched_amt']),
            'doctype' => $normalized['description'],
            'currency' => $normalized['currency'],
            'document_date' => self::formatDate($normalized['document_date']),
            'posting_date' => self::formatDate($normalized['posting_date']),
            'matched_amt' => self::toFloat($normalized['matched_amt']),
            'amount_in_account_currency' => self::toFloat($normalized['tx_amount']),
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
            'documentDate' => self::formatDate($normalized['document_date']),
            'postingDate' => self::formatDate($normalized['posting_date']),
            'grandTotal' => self::toFloat($normalized['grand_total']),
            'totalusdVal' => self::toFloat($normalized['matched_amt']),
            '_warnings' => $normalized['_warnings'],
        ];
    }

    public static function normalizeTransactionHeader(array $row): array
    {
        $warnings = [];

        return [
            'doc_no' => self::firstValue($row, ['Tx_No', 'Doc_No'], '', $warnings, 'doc_no'),
            'voucher_type' => self::firstValue($row, ['Tx_Type', 'Doc_Type'], '', $warnings, 'voucher_type'),
            'currency' => self::firstValue($row, ['Curr_Code', 'Currency'], '', $warnings, 'currency'),
            'description' => self::firstValue($row, ['Description', 'Tx_Desc'], '', $warnings, 'description'),
            'document_date' => self::firstValue($row, ['Tx_Date', 'Document_Date'], null, $warnings, 'document_date'),
            'posting_date' => self::firstValue($row, ['Appr_Date', 'Del_Date', 'Posting_Date'], null, $warnings, 'posting_date'),
            'grand_total' => self::firstValue($row, ['Tx_Amt', 'TxAmt', 'Grand_Total'], 0, $warnings, 'grand_total'),
            'matched_amt' => self::firstValue($row, ['Matched_Amt', 'Match_Amt', 'Matched_Amount'], 0, $warnings, 'matched_amt'),

            '_warnings' => $warnings,
        ];
    }

    private static function firstValue(
        array $item,
        array $keys,
        $default,
        array &$warnings,
        string $fieldName
    ) {
        foreach ($keys as $key) {
            if (isset($item[$key]) && $item[$key] !== '') {
                return $item[$key];
            }
        }

        $warnings[] = sprintf(
            'Missing %s. Expected one of: %s',
            $fieldName,
            implode(', ', $keys)
        );

        return $default;
    }

    private static function formatDate($value)
    {
        if ($value instanceof \DateTime) return $value->format('Y-m-d');

        return $value;
    }

    private static function toFloat($value): float
    {
        return isset($value) ? (float) $value : 0.00;
    }
}