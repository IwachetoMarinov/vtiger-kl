<?php
/* dbo_db/DatabaseSchema.php */

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

namespace dbo_db;

include_once 'data/CRMEntity.php';
include_once 'helpers/DBConnection.php';
include_once 'dbo_db/GetDBRows.php';

use helpers\DBConnection;

class DatabaseSchema
{

    private $connection;

    public function __construct()
    {
        $this->connection = DBConnection::getConnection();
    }

    public function getTables()
    {
        if (!$this->connection) die(print_r(sqlsrv_errors(), true));

        $sql = "SELECT DISTINCT TABLE_SCHEMA FROM INFORMATION_SCHEMA.TABLES";
        print_r(GetDBRows::getRows($this->connection, $sql, []));


        $tables = GetDBRows::getRows($this->connection, $sql, []);

        var_dump($tables);

        return $tables;
    }
}
