<?php

include_once 'modules/Contacts/models/MetalsAPI.php';

class MASForex_Record_Model extends Vtiger_Record_Model
{

    public static function getExchangeRate($date, $currency_pair)
    {
        $metalsAPI = new MetalsAPI();
        $data = $metalsAPI->getLatestExchangeRate($date);

        $result = [];

        foreach ($data as $row) {
            $cc  = $row['Curr_Code'];
            $key = strtolower($cc) . '_sgd';

            // Only add if not already stored
            if (!isset($result[$key])) $result[$key] = $row['100CurrToSGD'];
        }

        return $result[$currency_pair] ?? null;
    }

    public static function getLatestExchangeRate()
    {
        $metalsAPI = new MetalsAPI();
        $data = $metalsAPI->getLatestExchangeRate();

        $result = [];

        foreach ($data as $row) {
            $cc  = $row['Curr_Code'];
            $key = strtolower($cc) . '_sgd';

            if (!isset($result[$key])) $result[$key] = $row['rate'];
        }

        return $result;
    }

    public static function getLatestExchangeRateByCurrency($date, $currency)
    {
        if (!$date || $date == "" || $date == "0000-00-00" || !isset($currency)) return [];

        // If currency is SGD, return USD to SGD rate
        if (strtolower($currency) == "sgd") $currency = "USD";

        $metalsAPI = new MetalsAPI();
        $data = $metalsAPI->getLatestExchangeRate($date);
        $result = [];

        foreach ($data as $row) {
            $cc  = $row['Curr_Code'];

            if (strtolower($cc) != strtolower($currency)) continue;

            if (strtolower($cc) == strtolower($currency)) {
                $item = [
                    'currency' => $cc,
                    'date' => $row['Exc_Date'],
                    'rate'     => $row['100CurrToSGD']
                ];
                $result = $item;
                break;
            }
        }

        return $result;
    }
}
