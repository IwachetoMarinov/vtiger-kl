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
            if (!isset($result[$key])) $result[$key] = $row['rate'];
        }

        return $result[$currency_pair] ?? null;

        // old functionality SHOULD be removed later
        // $db = PearDatabase::getInstance();
        // $sql = "select $currency_pair from vtiger_masforex where price_date <= ?  order by price_date desc limit 1";
        // $sqlResult = $db->pquery($sql, array($date));
        // return $db->query_result($sqlResult, 0, $currency_pair);
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
}
