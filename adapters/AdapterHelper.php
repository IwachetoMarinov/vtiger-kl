<?php

namespace adapters;

class AdapterHelper
{
    public static function firstValue(
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

        $warnings[] = [
            'field' => $fieldName,
            'keys' => $keys,
            'message' => sprintf(
                'Missing %s. Expected one of: %s',
                $fieldName,
                implode(', ', $keys)
            ),
        ];

        return $default;
    }

    public static function toFloat($value): float
    {
        return isset($value) ? (float) $value : 0.00;
    }

    public  static function formatDate($value)
    {
        if ($value instanceof \DateTime) return $value->format('Y-m-d');

        return $value;
    }
}
