<?php


$serverName = "qcpitech.ddns.net";
$connectionOptions = array(
    "Database" => "GPM_DW",
    "Uid" => "DWUser",
    "PWD" => 'GPM@4632$gpm',
    "TrustServerCertificate" => true,
    "Encrypt" => false
);

$conn = sqlsrv_connect($serverName, $connectionOptions);


if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

$sql = "
SELECT 
    [Date],
    [MT_Code],
    [Curr_Code],
    [SpotPriceUSD],
    [Exc_Rate],
    [SpotPriceCurr]
FROM [HFS_SQLEXPRESS].[GPM].[dbo].[Metal_Spot_Price]
";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}


$data = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Convert DateTime objects to strings for var_dump readability
    if (isset($row['Date']) && $row['Date'] instanceof DateTime) {
        $row['Date'] = $row['Date']->format('Y-m-d');
    }
    $data[] = $row;
}

echo "<pre>";
var_dump($data);
echo "</pre>";

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

