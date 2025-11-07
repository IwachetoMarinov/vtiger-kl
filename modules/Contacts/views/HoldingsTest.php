<?php
class Contacts_HoldingsTest_View extends Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        echo '<div style="padding:10px;border:1px solid #ccc;">
                <h3 style="color:#008ECA;">HoldingsTest Widget Works!</h3>
                <p>Record ID: ' . htmlspecialchars($request->get('record')) . '</p>
              </div>';
        exit;
    }
}
