<?php

/* modules/Contacts/cron/CronHelpers.php */

class Contacts_CronHelpers
{
    public static function buildMonthlyDateRange()
    {
        $year = date('Y');
        $month = date('m');
        $startDate = date('Y-m-d', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime($startDate));
        return [$startDate, $endDate];
    }

    public static function fetchClientIds()
    {
        $db = PearDatabase::getInstance();

        $query = "
        SELECT DISTINCT cf_898 AS client_id
        FROM vtiger_contactscf
        WHERE cf_898 IS NOT NULL AND cf_898 != ''
    ";

        $result = $db->pquery($query, []);

        $clientIds = [];
        while ($row = $db->fetch_array($result)) {
            $clientIds[] = $row['client_id'];
        }

        return $clientIds;
    }

    public static function getContactRecordByClientId(string $client_id)
    {
        // Fetch Contact record linked by custom field (cf_898 = client_id)
        $db = PearDatabase::getInstance();

        $query = "
            SELECT c.contactid
            FROM vtiger_contactscf ccf
            INNER JOIN vtiger_contactdetails c ON c.contactid = ccf.contactid
            INNER JOIN vtiger_crmentity ce ON ce.crmid = c.contactid
            WHERE ccf.cf_898 = ?
            AND ce.deleted = 0
            LIMIT 1
        ";

        $result = $db->pquery($query, [$client_id]);
        $row = $db->fetch_array($result);

        if (!$row) return null;

        // Return full vTiger Record Model
        return Vtiger_Record_Model::getInstanceById($row['contactid'], 'Contacts');
    }

    public static function generatePdf($html, $client_id, $date_range, string $file_name_prefix = '%s-AS-%s-%s')
    {
        $startDate = date('d-M-Y', strtotime($date_range[0]));
        $endDate = date('d-M-Y', strtotime($date_range[1]));

        $fileName = sprintf($file_name_prefix, $client_id, $startDate, $endDate);

        // Temporary HTML + final PDF paths
        $basePath = realpath(dirname(__DIR__, 3));
        $tmpDir = sys_get_temp_dir();

        if (!$basePath) throw new Exception('Cannot resolve base path');

        $htmlPath = $tmpDir . '/' . $fileName . '.html';
        $pdfPath  = $tmpDir . '/' . $fileName . '.pdf';

        file_put_contents($htmlPath, $html);

        $inputFile = 'file://' . $htmlPath;

        $command = '/usr/bin/wkhtmltopdf --enable-local-file-access -L 0 -R 0 -B 0 -T 0 '
            . escapeshellarg($inputFile) . ' '
            . escapeshellarg($pdfPath) . ' 2>&1';

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if (file_exists($htmlPath)) unlink($htmlPath);

        return $pdfPath;
    }

    public static function storePdfInDocuments(string $pdfPath, string $client_id, string $selected_year, string $selected_currency, string $titlePrefix = 'Monthly Activity Summary - %s - %s%s')
    {
        global $adb, $current_user;

        // Ensure vTiger execution context (required for CRMEntity->save)
        self::initExecutionUser();

        // Validate PDF existence
        if (!file_exists($pdfPath)) throw new Exception("PDF file does not exist: " . $pdfPath);

        // Get Contact + Owner info
        $contactInfo = self::getContactInfoByClientId($client_id);

        if (!$contactInfo) throw new Exception("No contact found for client_id: " . $client_id);

        $contactId = $contactInfo['contact_id'];     // Contact record ID
        $contactOwnerId = $contactInfo['owner_id'];  // Owner of the contact

        // File metadata
        $fileName = basename($pdfPath);
        $fileSize = filesize($pdfPath);
        $mimeType = 'application/pdf';

        // Document title
        $documentTitle = sprintf(
            $titlePrefix,
            $client_id,
            $selected_year,
            $selected_currency ? ' - ' . $selected_currency : ''
        );

        // Create Document record
        $notes = CRMEntity::getInstance('Documents');
        $notes->column_fields['notes_title'] = $documentTitle;
        $notes->column_fields['filename'] = $fileName;
        $notes->column_fields['filelocationtype'] = 'I'; // Internal file
        $notes->column_fields['filestatus'] = 1;
        $notes->column_fields['filesize'] = $fileSize;
        $notes->column_fields['filetype'] = $mimeType;
        $notes->column_fields['folderid'] = 1;
        $notes->column_fields['notecontent'] = 'Auto-generated monthly activity summary.';
        $notes->column_fields['assigned_user_id'] = $contactOwnerId; // Assign to client owner

        // Save Document (triggers workflows/events)
        $notes->save('Documents');

        $documentId = $notes->id;
        if (!$documentId) throw new Exception('Failed to create Documents record.');

        // Create attachment entry
        $attachmentId = $adb->getUniqueID('vtiger_crmentity');

        // Determine upload directory (vTiger storage)
        $basePath = realpath(dirname(__DIR__, 3));
        $uploadDir = $basePath . '/storage/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $storedFileName = $attachmentId . '_' . $fileName;
        $destination = $uploadDir . $storedFileName;

        // MOVE instead of copy
        if (!rename($pdfPath, $destination)) {
            throw new Exception('Failed to move PDF to storage: ' . $destination);
        }

        // Insert attachment entity
        $adb->pquery(
            "INSERT INTO vtiger_crmentity
        (crmid, smcreatorid, smownerid, setype, description, createdtime, modifiedtime, presence, deleted)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)",
            [
                $attachmentId,
                $current_user->id,
                $contactOwnerId,
                'Documents Attachment',
                $documentTitle,
                1,
                0
            ]
        );

        // Insert attachment file record
        $adb->pquery(
            "INSERT INTO vtiger_attachments (attachmentsid, name, description, type, path)
         VALUES (?, ?, ?, ?, ?)",
            [
                $attachmentId,
                $fileName,
                $documentTitle,
                $mimeType,
                $uploadDir
            ]
        );

        // Link attachment to Document
        $adb->pquery(
            "INSERT INTO vtiger_seattachmentsrel (crmid, attachmentsid)
         VALUES (?, ?)",
            [$documentId, $attachmentId]
        );

        // Relate Document to Contact (so it appears in UI)
        $adb->pquery(
            "INSERT INTO vtiger_senotesrel (crmid, notesid)
         VALUES (?, ?)",
            [$contactId, $documentId]
        );
    }

    protected static function initExecutionUser()
    {
        // Ensures a valid $current_user exists (required for vTiger internals)
        global $current_user;

        if ($current_user && !empty($current_user->id)) return $current_user;

        require_once 'modules/Users/Users.php';
        $current_user = Users::getActiveAdminUser();

        if (!$current_user || empty($current_user->id))
            throw new Exception('Unable to initialize execution user.');

        return $current_user;
    }



    protected static function getContactInfoByClientId(string $client_id)
    {
        // Returns both contact ID and owner ID
        $db = PearDatabase::getInstance();

        $query = "
        SELECT c.contactid, ce.smownerid
        FROM vtiger_contactscf ccf
        INNER JOIN vtiger_contactdetails c ON c.contactid = ccf.contactid
        INNER JOIN vtiger_crmentity ce ON ce.crmid = c.contactid
        WHERE ccf.cf_898 = ?
          AND ce.deleted = 0
        LIMIT 1
    ";

        $result = $db->pquery($query, [$client_id]);
        $row = $db->fetch_array($result);

        if (!$row) return null;

        return [
            'contact_id' => (int)$row['contactid'],
            'owner_id'   => (int)$row['smownerid'],
        ];
    }
}
