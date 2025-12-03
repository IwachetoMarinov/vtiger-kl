<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.inc.php';
require_once 'includes/runtime/BaseModel.php';
require_once 'includes/runtime/Globals.php';
require_once 'includes/Loader.php';
require_once 'include/database/PearDatabase.php';

global $adb;

$contactId = isset($_GET['contact']) ? (int) $_GET['contact'] : 0;

if (!$contactId) {
    echo "Missing or invalid contact parameter. Use: delete_cert.php?contact=78";
    exit;
}

/**
 * Simple debug: show DB name & prefix
 */
echo "<h2>Deleting certificates for Contact ID: {$contactId}</h2>";

if (property_exists($adb, 'dbName')) {
    echo "<p><b>DB Name:</b> {$adb->dbName}</p>";
}
if (property_exists($adb, 'table_prefix')) {
    echo "<p><b>Table prefix:</b> {$adb->table_prefix}</p>";
}

/**
 * 1. Fetch all certificates for this contact
 */
$sql = "
    SELECT holdingcertificateid, notes_id
    FROM vtiger_holdingcertificate
    WHERE contact_id = ?
";
$result = $adb->pquery($sql, [$contactId]);

if ($result === false) {
    echo "<p><b>Query failed:</b> " . $adb->database->ErrorMsg() . "</p>";
    exit;
}

$rows = $adb->num_rows($result);
echo "<p><b>Found certificates:</b> {$rows}</p>";

if ($rows == 0) {
    echo "<p>No certificates found for this contact in THIS database.</p>";
    exit;
}

$deletedCerts = 0;


echo "<hr>";

echo "<h3>Starting deletion process…</h3>";


while ($row = $adb->fetch_array($result)) {

    echo "<hr>";
    echo "<p><b>Processing certificate record:</b></p>";
    var_dump($row);

    $certId = (int) $row['holdingcertificateid'];
    $noteId = (int) $row['notes_id'];

    echo "<hr>";
    echo "<p><b>Certificate ID:</b> {$certId} | <b>Note ID:</b> {$noteId}</p>";

    /**
     * 2. Delete attachments linked to the note
     */
    if ($noteId) {
        $attRes = $adb->pquery(
            "
            SELECT attachmentsid 
            FROM vtiger_seattachmentsrel 
            WHERE crmid = ?",
            [$noteId]
        );

        if ($attRes === false) {
            echo "<p style='color:red;'>Error fetching attachments for note {$noteId}</p>";
        } else {
            while ($att = $adb->fetch_array($attRes)) {
                $attId = (int) $att['attachmentsid'];
                echo "<p>Deleting attachment ID: {$attId}</p>";

                $adb->pquery("DELETE FROM vtiger_attachments WHERE attachmentsid = ?", [$attId]);
                echo "<p>- vtiger_attachments affected: " . $adb->getAffectedRowCount() . "</p>";

                $adb->pquery("DELETE FROM vtiger_seattachmentsrel WHERE attachmentsid = ?", [$attId]);
                echo "<p>- vtiger_seattachmentsrel affected: " . $adb->getAffectedRowCount() . "</p>";
            }
        }

        echo "<p>Deleting note {$noteId}…</p>";

        $adb->pquery("DELETE FROM vtiger_notes WHERE notesid = ?", [$noteId]);
        echo "<p>- vtiger_notes affected: " . $adb->getAffectedRowCount() . "</p>";

        $adb->pquery("DELETE FROM vtiger_notescf WHERE notesid = ?", [$noteId]);
        echo "<p>- vtiger_notescf affected: " . $adb->getAffectedRowCount() . "</p>";

        $adb->pquery("DELETE FROM vtiger_crmentity WHERE crmid = ?", [$noteId]);
        echo "<p>- vtiger_crmentity (note) affected: " . $adb->getAffectedRowCount() . "</p>";
    }
    /**
     * 3. Delete the certificate itself
     */
    echo "<p>Deleting certificate {$certId}…</p>";

    $adb->pquery("DELETE FROM vtiger_holdingcertificate WHERE holdingcertificateid = ?", [$certId]);
    echo "<p>- vtiger_holdingcertificate affected: " . $adb->getAffectedRowCount() . "</p>";

    $adb->pquery("DELETE FROM vtiger_crmentity WHERE crmid = ?", [$certId]);
    echo "<p>- vtiger_crmentity (certificate) affected: " . $adb->getAffectedRowCount() . "</p>";

    $deletedCerts++;
}

echo "<hr>";
echo "<h3>Finished. Deleted {$deletedCerts} certificate record(s) for contact {$contactId}.</h3>";
