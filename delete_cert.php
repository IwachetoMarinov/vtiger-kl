<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.inc.php';   // uses $dbconfig array from vtiger

// 1. Get contact ID from query
$contactId = isset($_GET['contact']) ? (int) $_GET['contact'] : 0;
if (!$contactId) {
    echo "Missing or invalid contact parameter. Use: delete_cert.php?contact=78";
    exit;
}

echo "<h2>Deleting certificates for Contact ID: {$contactId}</h2>";

// 2. Open direct mysqli connection (NO vtiger $adb)
$mysqli = new mysqli(
    $dbconfig['db_hostname'],
    $dbconfig['db_username'],
    $dbconfig['db_password'],
    $dbconfig['db_name']
);

if ($mysqli->connect_errno) {
    echo "DB connection failed: " . $mysqli->connect_error;
    exit;
}

// 3. Fetch all certificates for this contact
$sql = "SELECT holdingcertificateid, notes_id 
        FROM vtiger_holdingcertificate 
        WHERE contact_id = {$contactId}";
$res = $mysqli->query($sql);

if (!$res) {
    echo "Select failed: " . $mysqli->error;
    exit;
}

$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}

echo "<p><b>Found certificates:</b> " . count($rows) . "</p>";

if (!count($rows)) {
    echo "<p>No certificates for this contact.</p>";
    exit;
}

echo "<hr><h3>Starting deletion process…</h3>";

foreach ($rows as $row) {
    $certId = (int)$row['holdingcertificateid'];
    $noteId = (int)$row['notes_id'];

    echo "<hr>";
    echo "<p><b>Certificate:</b> {$certId} | <b>Note:</b> {$noteId}</p>";

    // 4. Delete attachments for the note
    if ($noteId) {
        $attRes = $mysqli->query("SELECT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid = {$noteId}");
        if ($attRes) {
            while ($att = $attRes->fetch_assoc()) {
                $attId = (int)$att['attachmentsid'];
                echo "<p>- Deleting attachment {$attId}</p>";

                $mysqli->query("DELETE FROM vtiger_attachments WHERE attachmentsid = {$attId}");
                echo "<p>  vtiger_attachments affected: {$mysqli->affected_rows}</p>";

                $mysqli->query("DELETE FROM vtiger_seattachmentsrel WHERE attachmentsid = {$attId}");
                echo "<p>  vtiger_seattachmentsrel affected: {$mysqli->affected_rows}</p>";
            }
        }

        echo "<p>- Deleting note {$noteId}</p>";
        $mysqli->query("DELETE FROM vtiger_notes WHERE notesid = {$noteId}");
        echo "<p>  vtiger_notes affected: {$mysqli->affected_rows}</p>";

        $mysqli->query("DELETE FROM vtiger_notescf WHERE notesid = {$noteId}");
        echo "<p>  vtiger_notescf affected: {$mysqli->affected_rows}</p>";

        $mysqli->query("DELETE FROM vtiger_crmentity WHERE crmid = {$noteId}");
        echo "<p>  vtiger_crmentity (note) affected: {$mysqli->affected_rows}</p>";
    }

    // 5. Delete the certificate itself
    echo "<p>- Deleting certificate {$certId}</p>";
    $mysqli->query("DELETE FROM vtiger_holdingcertificate WHERE holdingcertificateid = {$certId}");
    echo "<p>  vtiger_holdingcertificate affected: {$mysqli->affected_rows}</p>";

    $mysqli->query("DELETE FROM vtiger_crmentity WHERE crmid = {$certId}");
    echo "<p>  vtiger_crmentity (certificate) affected: {$mysqli->affected_rows}</p>";
}

echo "<hr><h3>DONE.</h3>";
