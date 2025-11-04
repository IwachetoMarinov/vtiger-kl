<?php
class GPMCompany_Record_Model extends Vtiger_Record_Model
{

    public static function getInstanceByCode($code)
    {
        $db = PearDatabase::getInstance();

        $query = 'select gpmcompanyid from vtiger_gpmcompany where company_orosoft_code = ? limit 1';
        $params = array($code);

        $result = $db->pquery($query, $params);
        $noOfRows = $db->num_rows($result);
        $instance = GPMCompany_Record_Model::getCleanInstance('GPMCompany');
        if ($noOfRows > 0) {
            $instance = parent::getInstanceById($db->query_result($result, 0, 'gpmcompanyid'), 'GPMCompany');
        }

        return $instance;
    }
}
