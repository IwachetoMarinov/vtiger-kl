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
        $metalsAPI = new MetalsAPI();
        $data = $metalsAPI->getLatestExchangeRate($date);
        $result = [];

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";

        // foreach ($data as $row) {
        //     $cc  = $row['Curr_Code'];
        //     $key = strtolower($cc) . '_sgd';

        //     if (!isset($result[$key])) $result[$key] = $row['rate'];
        // }

        return $result;
    }
}
