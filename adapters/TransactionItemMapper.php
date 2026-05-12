<?php

namespace adapters;

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

            'taxAmount' => self::toFloat($normalized['tx_amount']),
            'spotPrice' => self::toFloat($normalized['spot_price']),
            'averageSpotPrice' => self::toFloat($normalized['avg_spot_price']),

            'postingDate' => self::formatDate($normalized['posting_date']),
            'documentDate' => self::formatDate($normalized['document_date']),

            'exchangeRate' => self::toFloat($normalized['exchange_rate']),
            'itemCode' => $normalized['item_code'],
            'itemDescription' => $normalized['item_description'],

            'fineOz' => self::toFloat($normalized['fine_oz']),
            'totalFineOz' => self::toFloat($normalized['total_fine_oz']),
            'grossOz' => self::toFloat($normalized['gross_oz']),
            'purity' => $normalized['purity'],

            'price' => self::toFloat($normalized['item_price']),
            'unitPrice' => self::toFloat($normalized['unit_price']),
            'premium' => $normalized['premium_perc'],
            'premiumFinal' => self::toFloat($normalized['premium_final']),

            'totalItemAmount' => self::toFloat($normalized['total_item_amount']),
            'totalItemDcAmount' => self::toFloat($normalized['total_item_dc_amount']),

            'serialNumbers' => self::sanitizeSerialNumbers($normalized['serial_numbers']),
            'serials' => !empty($normalized['serial_numbers'])
                ? explode(',', $normalized['serial_numbers'])
                : [],

            'voucherType' => $transaction['voucherType'] ?? '',
            'docNo' => $normalized['doc_no'],

            'weight' => max(self::toFloat($normalized['weight']), 1),
            'barNumber' => $normalized['bar_number'],
            'pureOz' => self::toFloat($normalized['gross_oz']),

            'remarks' => $normalized['remarks'],
            'otherCharge' => self::toFloat($normalized['other_charge']),
            'narration' => $normalized['narration'],
            'longDesc' => $normalized['long_desc'],

            'creditNoteAmount' => self::toFloat($normalized['credit_note_amount']),

            '_warnings' => $normalized['_warnings'],
        ];
    }

    public static function normalize(array $item): array
    {
        $warnings = [];

        $transactionType = self::firstValue(
            $item,
            ['Tx_Type', 'Doc_Type', 'Transaction_Type'],
            '',
            $warnings,
            'transaction_type'
        );

        return [
            'quantity' => self::firstValue($item, ['Qty', 'Quantity'], 1, $warnings, 'quantity'),
            'currency' => self::firstValue($item, ['Curr_Code', 'Currency'], '', $warnings, 'currency'),
            'metal_code' => self::firstValue($item, ['MT_Code', 'Metal_Code'], '', $warnings, 'metal_code'),
            'metal_name' => self::firstValue($item, ['MT_Name', 'Metal_Name'], '', $warnings, 'metal_name'),
            'metal_type_code' => self::firstValue($item, ['Metal_Type_Code'], '', $warnings, 'metal_type_code'),
            'warehouse' => self::firstValue($item, ['WH_Name', 'Warehouse'], '', $warnings, 'warehouse'),

            'transaction_type' => $transactionType,

            'description' => self::firstValue(
                $item,
                ['Description', 'Item_Desc', 'Desciption'],
                '',
                $warnings,
                'description'
            ),

            'tx_amount' => self::firstValue($item, ['Tx_Amt', 'TxAmt'], 0, $warnings, 'tx_amount'),
            'spot_price' => self::firstValue($item, ['Spot_Price'], 0, $warnings, 'spot_price'),
            'avg_spot_price' => self::firstValue($item, ['Avg_Spot_Price'], 0, $warnings, 'avg_spot_price'),

            'posting_date' => self::firstValue($item, ['Appr_Date', 'Posting_Date'], null, $warnings, 'posting_date'),
            'document_date' => self::firstValue($item, ['Tx_Date', 'Document_Date'], null, $warnings, 'document_date'),

            'exchange_rate' => self::firstValue($item, ['Exc_Rate', 'Exchange_Rate'], 0, $warnings, 'exchange_rate'),
            'item_code' => self::firstValue($item, ['Item_Code'], '', $warnings, 'item_code'),
            'item_description' => self::firstValue($item, ['Item_Desc'], '', $warnings, 'item_description'),

            'fine_oz' => self::firstValue($item, ['FineOz', 'Fine_Oz'], 0, $warnings, 'fine_oz'),
            'total_fine_oz' => self::firstValue($item, ['Tot_FineOz', 'Total_FineOz'], 0, $warnings, 'total_fine_oz'),
            'gross_oz' => self::firstValue($item, ['GrossOz', 'Gross_Oz'], 0, $warnings, 'gross_oz'),
            'purity' => self::firstValue($item, ['Purity'], '', $warnings, 'purity'),

            'item_price' => self::firstValue($item, ['Item_Price'], 0, $warnings, 'item_price'),
            'unit_price' => self::firstValue($item, ['Unit_Price'], 0, $warnings, 'unit_price'),
            'premium_perc' => self::firstValue($item, ['Premium_Perc'], '', $warnings, 'premium_perc'),
            'premium_final' => self::firstValue($item, ['Premium_Final'], 0, $warnings, 'premium_final'),

            'total_item_amount' => self::firstValue(
                $item,
                ['Total_Item_Amt', 'DN_Det_Amt', 'TxAmt', 'Tx_Amt'],
                0,
                $warnings,
                'total_item_amount'
            ),

            'total_item_dc_amount' => self::firstValue(
                $item,
                ['Total_Item_DC_Amt'],
                0,
                $warnings,
                'total_item_dc_amount'
            ),

            'serial_numbers' => self::firstValue($item, ['Ser_No'], '', $warnings, 'serial_numbers'),

            'doc_no' => self::firstValue($item, ['Tx_No', 'Doc_No'], '', $warnings, 'doc_no'),
            'weight' => self::firstValue($item, ['Weight'], 0, $warnings, 'weight'),
            'bar_number' => self::firstValue($item, ['Bar_No'], '', $warnings, 'bar_number'),
            'remarks' => self::firstValue($item, ['Remarks'], '', $warnings, 'remarks'),
            'other_charge' => self::firstValue($item, ['Other_Charge'], 0, $warnings, 'other_charge'),
            'narration' => self::firstValue($item, ['Narration'], '', $warnings, 'narration'),
            'long_desc' => self::firstValue($item, ['Long_Desc'], '', $warnings, 'long_desc'),

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
            return self::toFloat(
                self::firstValue($item, ['CN_Det_Amt'], 0, $warnings, 'credit_note_amount')
            );
        }

        if ($transactionType === 'DN') {
            return self::toFloat(
                self::firstValue($item, ['DN_Det_Amt'], 0, $warnings, 'credit_note_amount')
            );
        }

        return 0.00;
    }

    private static function firstValue(
        array $item,
        array $keys,
        $default,
        array &$warnings,
        string $fieldName
    ) {
        foreach ($keys as $key) {
            if (isset($item[$key]) && $item[$key] !== '') return $item[$key];
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

    private static function sanitizeSerialNumbers($serials): string
    {
        if (!$serials) return '';

        $serials = preg_replace('/;+$/', '', $serials);
        $serials = preg_replace('/;{2,}/', "\n", $serials);

        return $serials;
    }
}
