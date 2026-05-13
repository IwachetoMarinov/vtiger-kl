<?php

namespace adapters;

include_once 'adapters/AdapterHelper.php';

use adapters\AdapterHelper;

class TransactionItemMapper
{
    public static function map(array $item, array $transaction): object
    {
        $normalized = self::normalize($item);

        return (object) [
            'quantity' => (int) $normalized['quantity'],
            'currency' => $normalized['currency'],
            'metal' => $normalized['metal_code'],
            'metal_name' => $normalized['metal_name'],
            'metal_type_code' => $normalized['metal_type_code'],
            'warehouse' => $normalized['warehouse'],

            'transactionType' => $normalized['transaction_type'],
            'description' => $normalized['description'],

            'taxAmount' => AdapterHelper::toFloat($normalized['tx_amount']),
            'spotPrice' => AdapterHelper::toFloat($normalized['spot_price']),
            'averageSpotPrice' => AdapterHelper::toFloat($normalized['avg_spot_price']),

            'postingDate' => AdapterHelper::formatDate($normalized['posting_date']),
            'documentDate' => AdapterHelper::formatDate($normalized['document_date']),

            'exchangeRate' => AdapterHelper::toFloat($normalized['exchange_rate']),
            'itemCode' => $normalized['item_code'],
            'itemDescription' => $normalized['item_description'],

            'fineOz' => AdapterHelper::toFloat($normalized['fine_oz']),
            'totalFineOz' => AdapterHelper::toFloat($normalized['total_fine_oz']),
            'grossOz' => AdapterHelper::toFloat($normalized['gross_oz']),
            'purity' => $normalized['purity'],

            'price' => AdapterHelper::toFloat($normalized['item_price']),
            'unitPrice' => AdapterHelper::toFloat($normalized['unit_price']),
            'premium' => $normalized['premium_perc'],
            'premiumFinal' => AdapterHelper::toFloat($normalized['premium_final']),

            'totalItemAmount' => AdapterHelper::toFloat($normalized['total_item_amount']),
            'totalItemDcAmount' => AdapterHelper::toFloat($normalized['total_item_dc_amount']),

            'serialNumbers' => self::sanitizeSerialNumbers($normalized['serial_numbers']),
            'serials' => !empty($normalized['serial_numbers'])
                ? explode(',', $normalized['serial_numbers'])
                : [],

            'voucherType' => $transaction['voucherType'] ?? '',
            'docNo' => $normalized['doc_no'],

            'weight' => max(AdapterHelper::toFloat($normalized['weight']), 1),
            'barNumber' => $normalized['bar_number'],
            'pureOz' => AdapterHelper::toFloat($normalized['gross_oz']),

            'remarks' => $normalized['remarks'],
            'otherCharge' => AdapterHelper::toFloat($normalized['other_charge']),
            'narration' => $normalized['narration'],
            'longDesc' => $normalized['long_desc'],

            'creditNoteAmount' => AdapterHelper::toFloat($normalized['credit_note_amount']),

            '_warnings' => $normalized['_warnings'],
        ];
    }

    public static function normalize(array $item): array
    {
        $warnings = [];

        $transactionType = AdapterHelper::firstValue(
            $item,
            ['Tx_Type', 'Doc_Type', 'Transaction_Type'],
            '',
            $warnings,
            'transaction_type'
        );

        return [
            'quantity' => AdapterHelper::firstValue($item, ['Qty', 'Quantity'], 1, $warnings, 'quantity'),
            'currency' => AdapterHelper::firstValue($item, ['Curr_Code', 'Currency'], '', $warnings, 'currency'),
            'metal_code' => AdapterHelper::firstValue($item, ['MT_Code', 'Metal_Code'], '', $warnings, 'metal_code'),
            'metal_name' => AdapterHelper::firstValue($item, ['MT_Name', 'Metal_Name'], '', $warnings, 'metal_name'),
            'metal_type_code' => AdapterHelper::firstValue($item, ['Metal_Type_Code'], '', $warnings, 'metal_type_code'),
            'warehouse' => AdapterHelper::firstValue($item, ['WH_Name', 'Warehouse'], '', $warnings, 'warehouse'),

            'transaction_type' => $transactionType,

            'description' => AdapterHelper::firstValue(
                $item,
                ['Description', 'Item_Desc', 'Desciption'],
                '',
                $warnings,
                'description'
            ),

            'tx_amount' => AdapterHelper::firstValue($item, ['Tx_Amt', 'TxAmt'], 0, $warnings, 'tx_amount'),
            'spot_price' => AdapterHelper::firstValue($item, ['Spot_Price'], 0, $warnings, 'spot_price'),
            'avg_spot_price' => AdapterHelper::firstValue($item, ['Avg_Spot_Price'], 0, $warnings, 'avg_spot_price'),

            'posting_date' => AdapterHelper::firstValue($item, ['Appr_Date', 'Posting_Date'], null, $warnings, 'posting_date'),
            'document_date' => AdapterHelper::firstValue($item, ['Tx_Date', 'Document_Date'], null, $warnings, 'document_date'),

            'exchange_rate' => AdapterHelper::firstValue($item, ['Exc_Rate', 'Exchange_Rate'], 0, $warnings, 'exchange_rate'),
            'item_code' => AdapterHelper::firstValue($item, ['Item_Code'], '', $warnings, 'item_code'),
            'item_description' => AdapterHelper::firstValue($item, ['Item_Desc'], '', $warnings, 'item_description'),

            'fine_oz' => AdapterHelper::firstValue($item, ['FineOz', 'Fine_Oz'], 0, $warnings, 'fine_oz'),
            'total_fine_oz' => AdapterHelper::firstValue($item, ['Tot_FineOz', 'Total_FineOz'], 0, $warnings, 'total_fine_oz'),
            'gross_oz' => AdapterHelper::firstValue($item, ['GrossOz', 'Gross_Oz'], 0, $warnings, 'gross_oz'),
            'purity' => AdapterHelper::firstValue($item, ['Purity'], '', $warnings, 'purity'),

            'item_price' => AdapterHelper::firstValue($item, ['Item_Price'], 0, $warnings, 'item_price'),
            'unit_price' => AdapterHelper::firstValue($item, ['Unit_Price'], 0, $warnings, 'unit_price'),
            'premium_perc' => AdapterHelper::firstValue($item, ['Premium_Perc'], '', $warnings, 'premium_perc'),
            'premium_final' => AdapterHelper::firstValue($item, ['Premium_Final'], 0, $warnings, 'premium_final'),

            'total_item_amount' => AdapterHelper::firstValue(
                $item,
                ['Total_Item_Amt', 'DN_Det_Amt', 'TxAmt', 'Tx_Amt'],
                0,
                $warnings,
                'total_item_amount'
            ),

            'total_item_dc_amount' => AdapterHelper::firstValue(
                $item,
                ['Total_Item_DC_Amt'],
                0,
                $warnings,
                'total_item_dc_amount'
            ),

            'serial_numbers' => AdapterHelper::firstValue($item, ['Ser_No'], '', $warnings, 'serial_numbers'),

            'doc_no' => AdapterHelper::firstValue($item, ['Tx_No', 'Doc_No'], '', $warnings, 'doc_no'),
            'weight' => AdapterHelper::firstValue($item, ['Weight'], 0, $warnings, 'weight'),
            'bar_number' => AdapterHelper::firstValue($item, ['Bar_No'], '', $warnings, 'bar_number'),
            'remarks' => AdapterHelper::firstValue($item, ['Remarks'], '', $warnings, 'remarks'),
            'other_charge' => AdapterHelper::firstValue($item, ['Other_Charge'], 0, $warnings, 'other_charge'),
            'narration' => AdapterHelper::firstValue($item, ['Narration'], '', $warnings, 'narration'),
            'long_desc' => AdapterHelper::firstValue($item, ['Long_Desc'], '', $warnings, 'long_desc'),

            'credit_note_amount' => self::getCreditNoteAmount($item, $transactionType, $warnings),

            '_warnings' => $warnings,
        ];
    }

    private static function getCreditNoteAmount(
        array $item,
        string $transactionType,
        array &$warnings
    ): float {
        if ($transactionType === 'CN') {
            return AdapterHelper::toFloat(
                AdapterHelper::firstValue($item, ['CN_Det_Amt'], 0, $warnings, 'credit_note_amount')
            );
        }

        if ($transactionType === 'DN') {
            return AdapterHelper::toFloat(
                AdapterHelper::firstValue($item, ['DN_Det_Amt'], 0, $warnings, 'credit_note_amount')
            );
        }

        return 0.00;
    }

    private static function sanitizeSerialNumbers($serials): string
    {
        if (!$serials) return '';

        $serials = preg_replace('/;+$/', '', $serials);
        $serials = preg_replace('/;{2,}/', "\n", $serials);

        return $serials;
    }
}
