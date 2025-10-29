<?php

class GPMIntent_Line_Model extends Vtiger_Base_Model
{

    public function save()
    {
        global $adb;
        $sql = 'INSERT INTO `vtiger_gpmintent_line` (`gpmintentid`,`gpmmetalid`,`qty`,`fine_oz`,`premium_or_discount`,`premium_or_discount_usd`,`value_usd`,remark) VALUES (?,?,?,?,?,?,?,?)';
        $param = array(
            $this->get('gpmintentid'),
            $this->get('gpmmetalid'),
            $this->get('qty'),
            $this->get('fine_oz'),
            $this->get('premium_or_discount'),
            $this->get('premium_or_discount_usd'),
            $this->get('value_usd'),
            $this->get('remark'),

        );
        $adb->pquery($sql, $param);
    }

    public static function deleteByIntent($id)
    {
        global $adb;
        $sql = 'DELETE FROM  `vtiger_gpmintent_line` WHERE gpmintentid = ?';
        $adb->pquery($sql, array($id));
    }

    public function getMetalName()
    {
        global $adb;
        $sql = 'select product_name from vtiger_gpmmetal where gpmmetalid = ? ';
        $result =  $adb->pquery($sql, array( $this->get('gpmmetalid')));
        return  $adb->query_result($result, 0, 'product_name');
    }

    public static function getInstanceByIntent($intentId)
    {
        global $adb;
        $sql = 'select * from vtiger_gpmintent_line where gpmintentid = ?';
        $result = $adb->pquery($sql, array($intentId));
        $num_rows = $adb->num_rows($result);
        $lines = array();

        for ($i = 0; $i < $num_rows; $i++) {
            $valueMap = array(
                'gpmintentid' => $adb->query_result($result, $i, 'gpmintentid'),
                'gpmmetalid' => $adb->query_result($result, $i, 'gpmmetalid'),
                'qty' => $adb->query_result($result, $i, 'qty'),
                'fine_oz' => $adb->query_result($result, $i, 'fine_oz'),
                'premium_or_discount' => $adb->query_result($result, $i, 'premium_or_discount'),
                'premium_or_discount_usd' => $adb->query_result($result, $i, 'premium_or_discount_usd'),
                'value_usd' => $adb->query_result($result, $i, 'value_usd'),
                'remark' => $adb->query_result($result, $i, 'remark'),
            );
            $newLine = new self($valueMap);
            $lines[] = $newLine;
        }
        return $lines;
    }


    public static function getInstencesFromRequest(Vtiger_Request $request, $intentId)
    {
        $metal = $request->get('metal');
        $qty = $request->get('qty');
        $fineoz = $request->get('fineoz');
        $premium = $request->get('premium');
        $premium_usd = $request->get('premium_usd');
        $value_usd = $request->get('value_usd');
        $remark = $request->get('item_remark');
        $lineCount = count($metal);
        $lines = array();
        for ($i = 1; $i < $lineCount; $i++) {

            $valueMap = array(
                'gpmintentid' => $intentId,
                'gpmmetalid' => $metal[$i],
                'qty' => $qty[$i],
                'fine_oz' => $fineoz[$i],
                'premium_or_discount' => $premium[$i],
                'premium_or_discount_usd' => $premium_usd[$i],
                'value_usd' => $value_usd[$i],
                'remark' => $remark[$i],

            );
            $newLine = new self($valueMap);
            $lines[] = $newLine;
        }
        return $lines;
    }
}
