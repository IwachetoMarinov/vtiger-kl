<?php

namespace helpers;

use Dotenv\Dotenv;

class DBConnection
{
    private static $connection = null;

    public static function getConnection()
    {
        // If already connected, return the same connection (singleton)
        if (self::$connection !== null) {
            return self::$connection;
        }

        // Load .env only once
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();

        $db_username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: '';
        $db_password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

        if (empty($db_username) || empty($db_password)) throw new \Exception("Database credentials are missing in .env");

        $serverName = "qcpitech.ddns.net";
        $connectionOptions = [
            "Database" => "GPM_DW",
            "Uid" => $db_username,
            "PWD" => $db_password,
            "TrustServerCertificate" => true,
            "Encrypt" => false
        ];

        $conn = sqlsrv_connect($serverName, $connectionOptions);

        if ($conn === false) throw new \Exception("SQL Connection failed: " . print_r(sqlsrv_errors(), true));

        self::$connection = $conn;
        return self::$connection;
    }
}
