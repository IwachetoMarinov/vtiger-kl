<?php
/* dbo_db/GetDBRows.php */
namespace dbo_db;

class GetDBRows
{

    public static function getRows($connection, $sql, $params)
    {
        try {
            $stmt = sqlsrv_query($connection, $sql, $params);
            $summary = [];

            if ($stmt === false) return $summary;


            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $summary[] = $row;
            }

            sqlsrv_free_stmt($stmt);

            return $summary;
        } catch (\Exception $e) {
            return [];
        }
    }
}
