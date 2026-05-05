<?php

include_once 'data/CRMEntity.php';

class YTDReports extends CRMEntity
{
    public $table_name = 'vtiger_ytdreports';
    public $table_index = 'ytdreportsid';

    public $customFieldTable = ['vtiger_ytdreportscf', 'ytdreportsid'];

    public $tab_name = [
        'vtiger_crmentity',
        'vtiger_ytdreports',
        'vtiger_ytdreportscf'
    ];

    public $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'vtiger_ytdreports' => 'ytdreportsid',
        'vtiger_ytdreportscf' => 'ytdreportsid'
    ];

    public $list_fields = [
        'Report Name' => ['ytdreports', 'ytdreportsname'],
        'Client ID' => ['ytdreportscf', 'client_id'],
    ];

    public $list_fields_name = [
        'Report Name' => 'ytdreportsname',
        'Client ID' => 'client_id',
    ];

    public $popup_fields = ['ytdreportsname'];

    public $def_basicsearch_col = 'ytdreportsname';

    public $def_detailview_recname = 'ytdreportsname';

    public $mandatory_fields = ['ytdreportsname', 'assigned_user_id'];

    public $default_order_by = 'ytdreportsname';
    public $default_sort_order = 'ASC';

    public $column_fields = [];

    public function __construct()
    {
        global $log;
        $this->log = $log;
        $this->db = PearDatabase::getInstance();

        $this->column_fields = getColumnFields('YTDReports');
    }

    public function save_module($module)
    {
        // Required by CRMEntity save()
    }
}