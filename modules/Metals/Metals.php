<?php
/* modules/Metals/Metals.php */
include_once 'data/CRMEntity.php';
include_once 'data/Tracker.php';

class Metals extends CRMEntity
{
    /** Primary tables */
    public $table_name  = 'vtiger_metals';
    public $table_index = 'metalsid';

    /** Custom fields */
    public $customFieldTable = array('vtiger_metalscf', 'metalsid');

    /** All tables participating in save/list/search */
    public $tab_name = array(
        'vtiger_crmentity',
        'vtiger_metals',
        'vtiger_metalscf'
    );

    /** Key index for each table */
    public $tab_name_index = array(
        'vtiger_crmentity' => 'crmid',
        'vtiger_metals'    => 'metalsid',
        'vtiger_metalscf'  => 'metalsid',
    );

    /** ListView columns (label => [table, column]) */
    public $list_fields = array(
        'Name'         => array('metals', 'name'),
        'FineOz'       => array('metals', 'fineoz'),
        'Type'         => array('metals', 'metal_type'),
        'Created Time' => array('crmentity', 'createdtime'),
        'Assigned To'  => array('crmentity', 'smownerid'),
    );

    /** Field API names corresponding to $list_fields */
    public $list_fields_name = array(
        'Name'         => 'name',
        'FineOz'       => 'fineoz',
        'Type'         => 'metal_type',
        'Created Time' => 'createdtime',
        'Assigned To'  => 'assigned_user_id',
    );

    /** The field used as clickable link in ListView */
    public $list_link_field = 'name';

    /** Popup and record name settings */
    public $popup_fields           = array('name');
    public $def_basicsearch_col    = 'name';
    public $def_detailview_recname = 'name';

    /** Required fields */
    public $mandatory_fields = array('name', 'assigned_user_id');

    /** Default ordering */
    public $default_order_by   = 'name';
    public $default_sort_order = 'ASC';

    /** Important flag */
    public $IsCustomModule = true;

    public function __construct()
    {
        $this->column_fields = getColumnFields(get_class($this));
        $this->db = PearDatabase::getInstance();
    }

    public function save_module($module)
    {
        // Ensure setype is correct (some builds miss it on fresh modules)
        if (!empty($this->id)) {
            $db = PearDatabase::getInstance();
            $db->pquery('UPDATE vtiger_crmentity SET setype=? WHERE crmid=?', ['Metals', $this->id]);
        }
    }
}
