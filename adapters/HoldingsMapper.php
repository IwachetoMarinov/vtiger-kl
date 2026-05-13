<?php

namespace adapters;

class HoldingsMapper
{
    public static function mapDocHoldingRow(array $item): array
    {
        $warnings = [];

        return [
            'spot_date' => self::formatDate(
                self::firstValue($item, ['Spot_Date'], null, $warnings, 'spot_date')
            ),
            'spot_price' => self::firstValue($item, ['Spot_Price'], 0, $warnings, 'spot_price'),
            'location' => self::firstValue($item, ['WH_Code'], '', $warnings, 'location'),
            'description' => self::firstValue($item, ['Item_Desc'], '', $warnings, 'description'),
            'quantity' => self::firstValue($item, ['Qty'], 0, $warnings, 'quantity'),
            'serial_no' => self::sanitizeSerials(
                self::firstValue($item, ['Ser_No_List'], '', $warnings, 'serial_no')
            ),
            'fine_oz' => self::firstValue($item, ['FineOz'], 0, $warnings, 'fine_oz'),
            'total' => self::firstValue($item, ['Total'], 0, $warnings, 'total'),
            '_warnings' => $warnings,
        ];
    }

    public static function mapStockHoldingRow(array $item): array
    {
        $warnings = [];

        $mtCode = self::firstValue($item, ['MT_Code'], '', $warnings, 'mt_code');

        return [
            'serial_no' => self::firstValue($item, ['Ser_No_List', 'Ser_No'], '', $warnings, 'serial_no'),
            'gross_oz' => self::firstValue($item, ['GrossOz'], 0, $warnings, 'gross_oz'),
            'fine_oz' => self::firstValue($item, ['FineOz'], 0, $warnings, 'fine_oz'),
            'purity' => self::firstValue($item, ['Purity'], 0, $warnings, 'purity'),
            'acq_tx_no' => self::firstValue($item, ['Acq_Tx_No'], '', $warnings, 'acq_tx_no'),
            'item_code' => self::firstValue($item, ['Item_Code'], '', $warnings, 'item_code'),
            'description' => self::firstValue($item, ['Item_Desc'], '', $warnings, 'description'),
            'quantity' => self::firstValue($item, ['Qty', 'Quantity'], 1, $warnings, 'quantity'),
            'location' => self::firstValue($item, ['WH_Code'], '', $warnings, 'location'),
            'brand' => self::firstValue($item, ['Brand'], '', $warnings, 'brand'),
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
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        return $value;
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
