<?php

namespace adapters;

include_once 'adapters/AdapterHelper.php';

use adapters\AdapterHelper;

class HoldingsMapper
{
    public static function mapDocHoldingRow(array $item): array
    {
        $warnings = [];

        return [
            'spot_date' => AdapterHelper::formatDate(
                AdapterHelper::firstValue($item, ['Spot_Date'], null, $warnings, 'spot_date')
            ),
            'spot_price' => AdapterHelper::firstValue($item, ['Spot_Price'], 0, $warnings, 'spot_price'),
            'location' => AdapterHelper::firstValue($item, ['WH_Code'], '', $warnings, 'location'),
            'description' => AdapterHelper::firstValue($item, ['Item_Desc'], '', $warnings, 'description'),
            'quantity' => AdapterHelper::firstValue($item, ['Qty'], 0, $warnings, 'quantity'),
            'serial_no' => self::sanitizeSerials(
                AdapterHelper::firstValue($item, ['Ser_No_List'], '', $warnings, 'serial_no')
            ),
            'fine_oz' => AdapterHelper::firstValue($item, ['FineOz'], 0, $warnings, 'fine_oz'),
            'total' => AdapterHelper::firstValue($item, ['Total'], 0, $warnings, 'total'),
            '_warnings' => $warnings,
        ];
    }

    public static function mapStockHoldingRow(array $item): array
    {
        $warnings = [];

        $mtCode = AdapterHelper::firstValue($item, ['MT_Code'], '', $warnings, 'mt_code');

        return [
            'serial_no' => AdapterHelper::firstValue($item, ['Ser_No_List', 'Ser_No'], '', $warnings, 'serial_no'),
            'gross_oz' => AdapterHelper::firstValue($item, ['GrossOz'], 0, $warnings, 'gross_oz'),
            'fine_oz' => AdapterHelper::firstValue($item, ['FineOz'], 0, $warnings, 'fine_oz'),
            'purity' => AdapterHelper::firstValue($item, ['Purity'], 0, $warnings, 'purity'),
            'acq_tx_no' => AdapterHelper::firstValue($item, ['Acq_Tx_No'], '', $warnings, 'acq_tx_no'),
            'item_code' => AdapterHelper::firstValue($item, ['Item_Code'], '', $warnings, 'item_code'),
            'description' => AdapterHelper::firstValue($item, ['Item_Desc'], '', $warnings, 'description'),
            'quantity' => AdapterHelper::firstValue($item, ['Qty', 'Quantity'], 1, $warnings, 'quantity'),
            'location' => AdapterHelper::firstValue($item, ['WH_Code'], '', $warnings, 'location'),
            'brand' => AdapterHelper::firstValue($item, ['Brand'], '', $warnings, 'brand'),
            'mt_code' => $mtCode,
            'metal' => self::getMetalName($mtCode),
            '_warnings' => $warnings,
        ];
    }

    public static function mapDocHoldingRows(array $data): array
    {
        return array_map(function ($item) {
            return self::mapDocHoldingRow($item);
        }, $data);
    }

    public static function mapStockHoldingRows(array $data): array
    {
        return array_map(function ($item) {
            return self::mapStockHoldingRow($item);
        }, $data);
    }

    private static function getMetalName($code): string
    {
        if (!$code) return '';

        $metalNames = [
            'XAU' => 'Gold',
            'XAG' => 'Silver',
            'XPT' => 'Platinum',
            'XPD' => 'Palladium',
            'XPL' => 'Palladium',
            'MBTC' => 'mBitCoin',
        ];

        return $metalNames[$code] ?? '';
    }

    private static function sanitizeSerials($serials): string
    {
        if (!$serials) return '';

        $serials = preg_replace('/;+$/', '', $serials);
        $serials = preg_replace('/;{2,}/', "\n", $serials);

        return $serials;
    }
}
