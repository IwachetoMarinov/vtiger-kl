<?php
/* dbo_db/ProformaInvoice.php */

// ini_set('display_errors', 1); error_reporting(E_ALL);

namespace dbo_db;

include_once 'data/CRMEntity.php';
include_once 'modules/Users/Users.php';
include_once 'helpers/DBConnection.php';
include_once 'dbo_db/GetDBRows.php';

use helpers\DBConnection;

class ProformaInvoice
{
    private $connection;

    public function __construct()
    {
        $this->connection = DBConnection::getConnection();
    }

    public function getProformaInvoice(string $client_id, string $table_name)
    {
        if (!$client_id || !$table_name || !$this->connection) return [];

        try {
            $params = [];
            $where  = '';

            if ($client_id) {
                $where = "WHERE [Party_Code] = ?";
                $params[] = $client_id;
            }

            $sql = "SELECT * FROM [HFS_SQLEXPRESS].[GPM].[dbo].[$table_name] $where";

            // var_dump("Executing SQL: " . $sql);
            // var_dump("With params: " . implode(", ", $params));

            $summary = GetDBRows::getRows($this->connection, $sql, $params);

            // echo "<pre>";
            // var_dump($summary);
            // echo "</pre>";

            return $summary;
        } catch (\Exception $e) {
            return [];
        }
    }
}
