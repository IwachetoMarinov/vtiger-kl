<?php

chdir(dirname(__FILE__) . '/../../../');

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';
include_once 'include/Webservices/Create.php';
include_once 'modules/Users/Users.php';

class ExchangeRateFetcher
{

    public static $URL = "https://eservices.mas.gov.sg/api/action/datastore/search.json?resource_id=95932927-c8bc-4e7a-b484-68a66a24edfe&between[end_of_day]={{fromDate}},{{toDate}}&limit=1000&fields={{fields}}";
    public static $fields = ['end_of_day', 'usd_sgd', 'eur_sgd', 'cad_sgd', 'chf_sgd', 'hkd_sgd_100', 'myr_sgd_100', 'aed_sgd_100'];

    public static function fetchRate($from, $to)
    {
        $url = str_replace('{{fromDate}}', $from, ExchangeRateFetcher::$URL);
        $url = str_replace('{{toDate}}', $to, $url);
        $url = str_replace('{{fields}}', implode(',', ExchangeRateFetcher::$fields), $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $result['result']['records'];
    }

    public static function saveExchangeRate($data)
    {
        $user = new Users();
        $current_user = $user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId());
        $data['assigned_user_id'] = '19x1'; // 19=Users Module ID, 1=First user Entity ID
        try {
            $ent = vtws_create('MASForex', $data, $current_user);
        } catch (WebServiceException $ex) {
            //The data already exist in DB
            echo $ex->getMessage() . "\n";
        }
        return $ent;
    }
}
