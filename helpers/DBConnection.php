<?php

namespace helpers;

use Dotenv\Dotenv;

class DBConnection
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection !== null) return self::$connection;

        try {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->safeLoad();

            $db_username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: '';
            $db_password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

            if (!$db_username || !$db_password) return null;

            $serverName = "qcpitech.ddns.net";
            $connectionOptions = [
                "Database" => "GPM_DW",
                "Uid" => $db_username,
                "PWD" => $db_password,
                "TrustServerCertificate" => true,
                "Encrypt" => false,
                "LoginTimeout" => 5
            ];

            $conn = sqlsrv_connect($serverName, $connectionOptions);

            // echo "<pre>";
            // print_r(sqlsrv_errors(SQLSRV_ERR_ALL));
            // print_r($conn);
            // echo "</pre>";

            if ($conn === false) return null;

            self::$connection = $conn;
            return self::$connection;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
