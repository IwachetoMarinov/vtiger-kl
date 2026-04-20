<?php
/* modules/Contacts/cron/ActivitySummaryService.php */

include_once 'dbo_db/Helper.php';
include_once 'dbo_db/ActivitySummary.php';
require_once 'modules/Documents/Documents.php';
require_once 'data/CRMEntity.php';

class Contacts_ActivitySummaryService
{
    public function __construct() {}

    public function generateAndStoreForClient($client_id, $date_range = [])
    {
        // 1. Init ActivitySummary to fetch transactions for the date range
        $activity = new dbo_db\ActivitySummary();

        // 2. Init date variables (fallback to current month if not provided)
        $selected_year = !empty($date_range) ? date('Y', strtotime($date_range[0])) : date('Y');
        $start_date = !empty($date_range) ? $date_range[0] : date('Y-m-01');
        $end_date = !empty($date_range) ? $date_range[1] : date('Y-m-t');

        // 3. Fetch all transactions for this client in the given date range
        $activities = $activity->getMonthlyTransactions($client_id, $start_date, $end_date);

        // 4. Get Contact record model (used for template rendering)
        $contactRecord = $this->getContactRecordByClientId($client_id);

        // 5. Get all currencies used by this client
        $currency_list = $activity->getTransactionCurrencies($client_id);

        // 6. Select default currency (first available)
        $selected_currency = !empty($currency_list) ? $currency_list[0] : '';

        // 7. Get company information (used in PDF header)
        $company_record = Contacts_DefaultCompany_View::process();

        // 8. Build full company address string
        $company_full_address = Helper::getCompanyFullAddress($company_record);

        // 9. Get opening balance for this client and period
        $opening_balance = $activity->getActivitySummaryOpeningBalance($client_id, $selected_currency, $start_date);

        // 10. Calculate pagination (for PDF layout)
        $pages = $this->makeDataPage($activities);

        // 11. Initialize Smarty template engine
        $smarty = new Smarty();
        $smarty->setCompileDir(dirname(__DIR__, 3) . '/test/templates_c/');
        $smarty->setCacheDir(dirname(__DIR__, 3) . '/test/cache/');
        $smarty->setConfigDir(dirname(__DIR__, 3) . '/test/config/');

        // 12. Register custom template resolver for vTiger templates
        $templateRoot = dirname(__DIR__, 3) . '/layouts/v7/modules';
        $smarty->registerPlugin('modifier', 'vtemplate_path', function ($templateName, $moduleName) use ($templateRoot) {
            return $templateRoot . '/' . $moduleName . '/' . $templateName;
        });

        // 13. Assign all variables required by the template
        $smarty->assign('RECORD_MODEL', $contactRecord);
        $smarty->assign('TRANSACTIONS', $activities);
        $smarty->assign('COMPANY', $company_record);
        $smarty->assign('PAGES', $pages);
        $smarty->assign('OPENING_BALANCE', $opening_balance);
        $smarty->assign('COMPANY_FULL_ADDRESS', $company_full_address);
        $smarty->assign('ENABLE_DOWNLOAD_BUTTON', false);
        $smarty->assign('EARLIEST_DATE', $start_date ? date('Y-M-d', strtotime($start_date)) : null);
        $smarty->assign('LATEST_DATE', $end_date ? date('Y-M-d', strtotime($end_date)) : null);

        // 14. Render HTML from Smarty template
        $templatePath = dirname(__DIR__, 3) . '/layouts/v7/modules/Contacts/ActivtySummeryPrintPreview.tpl';
        $html = $smarty->fetch('file:' . $templatePath);

        // 15. Generate PDF from HTML using wkhtmltopdf
        $pdfPath = $this->generatePdf($html, $client_id, $date_range);

        // 16. If PDF generation failed → stop here
        if (!file_exists($pdfPath)) return;

        // 17. Store generated PDF in vTiger Documents module
        $this->storePdfInDocuments($pdfPath, $client_id, $selected_year, $selected_currency);

        // 18. Insert into monthly transactions table for record-keeping
        $this->insertIntoMonthlyTransactions($client_id, $start_date, $end_date, $selected_currency);

        // 19. Cleanup generated PDF file
        if (file_exists($pdfPath)) unlink($pdfPath);
    }

    protected function insertIntoMonthlyTransactions($client_id, $start_date, $end_date, $currency)
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            "SELECT 1 FROM vtiger_monthly_transactions 
     WHERE client_id = ? AND start_date = ? AND end_date = ?",
            [$client_id, $start_date, $end_date]
        );

        $description = sprintf('Monthly activity summary for client_id %s, period: %s to %s, currency: %s', $client_id, $start_date, $end_date, $currency);


        if ($db->num_rows($result) == 0) {
            $db->pquery(
                "INSERT INTO vtiger_monthly_transactions (client_id, start_date, end_date, description, created_at)
     VALUES (?, ?, ?, ?, NOW())",
                [$client_id, $start_date, $end_date, $description]
            );
        }
    }

    protected function getContactRecordByClientId($client_id)
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

    protected function getContactIdByClientId($client_id)
    {
        // Same lookup but returns only ID (lighter than Record Model)
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

        return $row ? (int)$row['contactid'] : 0;
    }

    protected function makeDataPage($transaction)
    {
        // Calculates number of pages for PDF layout
        // First page holds 22 records, next pages 30 each

        $totalPage = 1;

        if (count($transaction) > 22) {
            $totaldataAfterFirstPage = count($transaction) - 22;
            $totalPage = ceil($totaldataAfterFirstPage / 30) + 1;
        }

        return $totalPage;
    }

    protected function generatePdf($html, $client_id, $date_range)
    {
        global $root_directory;

        // Format date range for filename
        $startDate = date('d-M-Y', strtotime($date_range[0]));
        $endDate = date('d-M-Y', strtotime($date_range[1]));

        // Example: M2001-AS-01-Mar-2026-31-Mar-2026
        $fileName = sprintf(
            '%s-AS-%s-%s',
            $client_id,
            $startDate,
            $endDate
        );

        // Temporary HTML + final PDF paths
        $htmlPath = $root_directory . $fileName . '.html';
        $pdfPath = $root_directory . $fileName . '.pdf';

        // Save HTML to file
        file_put_contents($htmlPath, $html);

        // Execute wkhtmltopdf command
        $command = 'wkhtmltopdf --enable-local-file-access -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking '
            . escapeshellarg($htmlPath) . ' '
            . escapeshellarg($pdfPath) . ' 2>&1';

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        // Cleanup temp HTML file
        if (file_exists($htmlPath)) unlink($htmlPath);

        return $pdfPath;
    }

    protected function storePdfInDocuments($pdfPath, $client_id, $selected_year, $selected_currency)
    {
        global $adb, $current_user;

        // Ensure vTiger execution context (required for CRMEntity->save)
        $this->initExecutionUser();

        // Validate PDF existence
        if (!file_exists($pdfPath)) throw new Exception("PDF file does not exist: " . $pdfPath);

        // Get Contact + Owner info
        $contactInfo = $this->getContactInfoByClientId($client_id);

        if (!$contactInfo) throw new Exception("No contact found for client_id: " . $client_id);

        $contactId = $contactInfo['contact_id'];     // Contact record ID
        $contactOwnerId = $contactInfo['owner_id'];  // Owner of the contact

        // File metadata
        $fileName = basename($pdfPath);
        $fileSize = filesize($pdfPath);
        $mimeType = 'application/pdf';

        // Document title
        $documentTitle = sprintf(
            'Monthly Activity Summary - %s - %s%s',
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
        if (!$documentId) {
            throw new Exception('Failed to create Documents record.');
        }

        // Create attachment entry
        $attachmentId = $adb->getUniqueID('vtiger_crmentity');

        // Determine upload directory (vTiger storage)
        $uploadDir = decideFilePath();
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        // Physical file name in storage
        $storedFileName = $attachmentId . '_' . $fileName;
        $destination = $uploadDir . $storedFileName;

        // Copy PDF into storage
        if (!copy($pdfPath, $destination)) throw new Exception('Failed to copy PDF to storage directory: ' . $destination);

        // Insert attachment entity
        $adb->pquery(
            "INSERT INTO vtiger_crmentity
        (crmid, smcreatorid, smownerid, setype, description, createdtime, modifiedtime, presence, deleted)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)",
            [
                $attachmentId,
                $current_user->id,   // Technical creator (admin)
                $contactOwnerId,     // Business owner (client owner)
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

    protected function getContactInfoByClientId($client_id)
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

    protected function initExecutionUser()
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
}
