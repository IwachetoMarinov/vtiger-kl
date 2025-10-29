<?php

class MASForex_Record_Model extends Vtiger_Record_Model
{

    public static function getExchangeRate($date, $currency_pair)
    {
        $db = PearDatabase::getInstance();
        $sql = "select $currency_pair from vtiger_masforex where price_date <= ?  order by price_date desc limit 1";
        $sqlResult = $db->pquery($sql, array($date));
        return $db->query_result($sqlResult, 0, $currency_pair);
    }

    public static function getLatestExchangeRate()
    {
        $db = PearDatabase::getInstance();
        $sql = "select * from vtiger_masforex order by price_date desc limit 1";
        $sqlResult = $db->pquery($sql, array());

        $currencyspairs = array(
            'usd_sgd' => $db->query_result($sqlResult, 0, 'usd_sgd'),
            'eur_sgd' => $db->query_result($sqlResult, 0, 'eur_sgd'),
            'cad_sgd' => $db->query_result($sqlResult, 0, 'cad_sgd'),
            'chf_sgd' => $db->query_result($sqlResult, 0, 'chf_sgd'),
            'hkd_sgd' => $db->query_result($sqlResult, 0, 'hkd_sgd'),
            'myr_sgd' => $db->query_result($sqlResult, 0, 'myr_sgd')
        );

        return $currencyspairs;
    }
}


