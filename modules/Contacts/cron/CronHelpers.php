<?php

/* modules/Contacts/cron/CronHelpers.php */

class Contacts_CronHelpers
{
    // public static function buildMonthlyDateRange()
    // {
    //     $year = date('Y');
    //     $month = date('m');
    //     $startDate = date('Y-m-d', strtotime("$year-$month-01"));
    //     $endDate = date('Y-m-t', strtotime($startDate));
    //     return [$startDate, $endDate];
    // }

    public static function buildMonthlyDateRange()
    {
        $startDate = date('Y-m-01', strtotime('first day of last month'));
        $endDate   = date('Y-m-t', strtotime('last month'));

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

        $basePath = realpath(dirname(__DIR__, 3));
        $tmpDir = sys_get_temp_dir();

        if (!$basePath) throw new Exception('Cannot resolve base path');

        $tmpLayouts = sys_get_temp_dir() . '/layouts';

        if (!file_exists($tmpLayouts)) {
            symlink($basePath . '/layouts', $tmpLayouts);
        }

        $htmlPath = $tmpDir . '/' . $fileName . '.html';
        $pdfPath  = $tmpDir . '/' . $fileName . '.pdf';

        file_put_contents($htmlPath, $html);

        $inputFile = 'file://' . $htmlPath;

        // $command = '/usr/bin/wkhtmltopdf --enable-local-file-access -L 0 -R 0 -B 0 -T 0 '
        //     . escapeshellarg($inputFile) . ' '
        //     . escapeshellarg($pdfPath) . ' 2>&1';

        $wkhtmltopdfBinary = trim(shell_exec('which wkhtmltopdf'));

        if (!$wkhtmltopdfBinary) {
            throw new Exception('wkhtmltopdf binary not found');
        }

        $command = $wkhtmltopdfBinary .
            ' --enable-local-file-access -L 0 -R 0 -B 0 -T 0 '
            . escapeshellarg($inputFile) . ' '
            . escapeshellarg($pdfPath) . ' 2>&1';

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if (file_exists($pdfPath)) {
            echo "PDF SIZE INSIDE generatePdf: " . filesize($pdfPath) . "\n";
        }

        if (file_exists($htmlPath)) unlink($htmlPath);

        return $pdfPath;
    }

    public static function logYTDReport(string $client_id, string $start_date, string $end_date, int $activityDocId)
    {
        $db = PearDatabase::getInstance();

        $db->pquery(
            "INSERT INTO vtiger_ytdreports_log 
        (client_id, period_start, period_end, activity_summary_docid, status)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            activity_summary_docid = VALUES(activity_summary_docid),
            status = 'completed'",
            [
                $client_id,
                $start_date,
                $end_date,
                $activityDocId,
                'completed'
            ]
        );
    }

    public static function logYTDReportHoldings(string $client_id, string $start_date, string $end_date, int $holdingsDocId)
    {
        $db = PearDatabase::getInstance();

        $db->pquery(
            "INSERT INTO vtiger_ytdreports_log
        (client_id, period_start, period_end, holdings_docid, status)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            holdings_docid = VALUES(holdings_docid),
            status = 'completed'",
            [
                $client_id,
                $start_date,
                $end_date,
                $holdingsDocId,
                'completed'
            ]
        );
    }

    public static function createYTDReportRecord(
        string $client_id,
        string $start_date,
        string $end_date,
        int $documentId,
        string $reportType
    ) {
        global $adb, $current_user;

        self::initExecutionUser();

        $contactRecord = self::getContactRecordByClientId($client_id);
        if (!$contactRecord) return 0;

        $contactId = $contactRecord->getId();

        require_once 'modules/YTDReports/YTDReports.php';

        $report = CRMEntity::getInstance('YTDReports');
        $report->column_fields['ytdreportsname'] = sprintf(
            '%s - %s - %s to %s',
            $reportType,
            $client_id,
            $start_date,
            $end_date
        );
        $report->column_fields['client_id'] = $client_id;
        $report->column_fields['assigned_user_id'] = $current_user->id;

        $report->save('YTDReports');

        $reportId = $report->id;

        // Link YTDReports record to generated Document
        $adb->pquery(
            "INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule)
     VALUES (?, ?, ?, ?)",
            [$reportId, 'YTDReports', $documentId, 'Documents']
        );

        // Link report to Contact
        $adb->pquery(
            "INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule)
         VALUES (?, ?, ?, ?)",
            [$contactId, 'Contacts', $reportId, 'YTDReports']
        );

        // Link report to generated Document
        $adb->pquery(
            "INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule)
         VALUES (?, ?, ?, ?)",
            [$reportId, 'YTDReports', $documentId, 'Documents']
        );

        return $reportId;
    }

    public static function ytdReportExists(
        string $client_id,
        string $start_date,
        string $end_date,
        string $reportType
    ): bool {
        $db = PearDatabase::getInstance();

        $name = sprintf(
            '%s - %s - %s to %s',
            $reportType,
            $client_id,
            $start_date,
            $end_date
        );

        $result = $db->pquery(
            "SELECT ce.crmid
         FROM vtiger_crmentity ce
         INNER JOIN vtiger_ytdreports y ON y.ytdreportsid = ce.crmid
         WHERE ce.deleted = 0
         AND y.ytdreportsname = ?
         LIMIT 1",
            [$name]
        );

        return $db->num_rows($result) > 0;
    }

    public static function storePdfInDocuments(string $pdfPath, string $client_id, string $selected_year, string $selected_currency, string $titlePrefix = 'Monthly Activity Summary - %s - %s%s')
    {
        global $adb, $current_user;

        self::initExecutionUser();

        if (!file_exists($pdfPath)) {
            throw new Exception("PDF file does not exist: " . $pdfPath);
        }

        $contactInfo = self::getContactInfoByClientId($client_id);

        if (!$contactInfo) {
            throw new Exception("No contact found for client_id: " . $client_id);
        }

        $contactId = $contactInfo['contact_id'];
        $contactOwnerId = $contactInfo['owner_id'];

        $fileName = basename($pdfPath);
        $fileSize = filesize($pdfPath);
        $mimeType = 'application/pdf';

        $documentTitle = sprintf(
            $titlePrefix,
            $client_id,
            $selected_year,
            $selected_currency ? ' - ' . $selected_currency : ''
        );

        $notes = CRMEntity::getInstance('Documents');
        $notes->column_fields['notes_title'] = $documentTitle;
        $notes->column_fields['filename'] = $fileName;
        $notes->column_fields['filelocationtype'] = 'I';
        $notes->column_fields['filestatus'] = 1;
        $notes->column_fields['filesize'] = $fileSize;
        $notes->column_fields['filetype'] = $mimeType;
        $notes->column_fields['folderid'] = 1;
        $notes->column_fields['notecontent'] = 'Auto-generated monthly PDF report.';
        $notes->column_fields['assigned_user_id'] = $contactOwnerId;

        $notes->save('Documents');

        $documentId = $notes->id;

        if (!$documentId) {
            throw new Exception('Failed to create Documents record.');
        }

        $attachmentId = $adb->getUniqueID('vtiger_crmentity');

        $basePath = realpath(dirname(__DIR__, 3));

        if (!$basePath) {
            throw new Exception('Cannot resolve base path');
        }

        // Match existing working vtiger behavior in your DEV/LIVE DB rows
        $uploadDir = $basePath . '/storage/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $storedFileName = $attachmentId . '_' . $fileName;
        $destination = $uploadDir . $storedFileName;

        if (!copy($pdfPath, $destination)) {
            throw new Exception('Failed to copy PDF to storage: ' . $destination);
        }

        @unlink($pdfPath);

        chmod($destination, 0644);

        // Required on STAGING/LIVE with SELinux
        exec(
            'chcon -t httpd_sys_content_t ' . escapeshellarg($destination) . ' 2>&1',
            $chconOutput,
            $chconReturn
        );

        if ($chconReturn !== 0) throw new Exception('Failed to set SELinux context: ' . implode("\n", $chconOutput));

        if (!file_exists($destination)) throw new Exception('Stored PDF missing: ' . $destination);

        if (filesize($destination) === 0) throw new Exception('Stored PDF is empty: ' . $destination);

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

        $adb->pquery(
            "INSERT INTO vtiger_attachments
        (attachmentsid, name, description, type, path)
        VALUES (?, ?, ?, ?, ?)",
            [
                $attachmentId,
                $fileName,
                $documentTitle,
                $mimeType,
                $uploadDir
            ]
        );

        $adb->pquery(
            "INSERT INTO vtiger_seattachmentsrel (crmid, attachmentsid)
        VALUES (?, ?)",
            [$documentId, $attachmentId]
        );

        $adb->pquery(
            "INSERT INTO vtiger_senotesrel (crmid, notesid)
        VALUES (?, ?)",
            [$contactId, $documentId]
        );

        return $documentId;
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
