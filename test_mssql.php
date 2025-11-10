<!-- <?php phpinfo(); ?> -->

<?php


$serverName = "qcpitech.ddns.net";
$connectionOptions = array(
    "Database" => "YourDatabaseName",
    "Uid" => "DWUser",
    "PWD" => 'GPM@4632$gpm',
    "TrustServerCertificate" => true,
    "Encrypt" => false
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn) {
    echo "Connected successfully.<br>";
    // $query = "SELECT TOP 5 * FROM your_table";
    // $stmt = sqlsrv_query($conn, $query);
    // while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    //     print_r($row);
    // }
    // sqlsrv_close($conn);
} else {
    die(print_r(sqlsrv_errors(), true));
}
?>