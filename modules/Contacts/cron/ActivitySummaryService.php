<?php
/* modules/Contacts/cron/ActivitySummaryService.php */

include_once 'dbo_db/Helper.php';
include_once 'dbo_db/ActivitySummary.php';
require_once 'modules/Documents/Documents.php';
require_once 'data/CRMEntity.php';

// ini_set('display_errors', 1); error_reporting(E_ALL);

class Contacts_ActivitySummaryService
{
    public function __construct() {}

    public function generateAndStoreForClient($client_id, $date_range = [])
    {
        $activity = new dbo_db\ActivitySummary();

        $selected_year = !empty($date_range) ? date('Y', strtotime($date_range[0])) : date('Y');
        $start_date = !empty($date_range) ? $date_range[0] : date('Y-m-01');
        $end_date = !empty($date_range) ? $date_range[1] : date('Y-m-t');

        $activities = $activity->getMonthlyTransactions($client_id, $start_date, $end_date);
        $contactRecord = $this->getContactRecordByClientId($client_id);
        $currency_list = $activity->getTransactionCurrencies($client_id);
        $selected_currency = !empty($currency_list) ? $currency_list[0] : '';
        $company_record = Contacts_DefaultCompany_View::process();
        $company_full_address = Helper::getCompanyFullAddress($company_record);
        $opening_balance = $activity->getActivitySummaryOpeningBalance($client_id, $selected_currency, $start_date);
        $pages = $this->makeDataPage($activities);

        $smarty = new Smarty();
        $smarty->setCompileDir(dirname(__DIR__, 3) . '/test/templates_c/');
        $smarty->setCacheDir(dirname(__DIR__, 3) . '/test/cache/');
        $smarty->setConfigDir(dirname(__DIR__, 3) . '/test/config/');

        $templateRoot = dirname(__DIR__, 3) . '/layouts/v7/modules';
        $smarty->registerPlugin('modifier', 'vtemplate_path', function ($templateName, $moduleName) use ($templateRoot) {
            return $templateRoot . '/' . $moduleName . '/' . $templateName;
        });

        $smarty->assign('RECORD_MODEL', $contactRecord);
        $smarty->assign('TRANSACTIONS', $activities);
        $smarty->assign('COMPANY', $company_record);
        $smarty->assign('PAGES', $pages);
        $smarty->assign('OPENING_BALANCE', $opening_balance);
        $smarty->assign('COMPANY_FULL_ADDRESS', $company_full_address);
        $smarty->assign('ENABLE_DOWNLOAD_BUTTON', false);
        $smarty->assign('EARLIEST_DATE', $start_date ? date('Y-M-d', strtotime($start_date)) : null);
        $smarty->assign('LATEST_DATE', $end_date ? date('Y-M-d', strtotime($end_date)) : null);

        $templatePath = dirname(__DIR__, 3) . '/layouts/v7/modules/Contacts/ActivtySummeryPrintPreview.tpl';
        $html = $smarty->fetch('file:' . $templatePath);

        // echo $html; // For debugging purposes, to see the generated HTML
        // exit;

        $pdfPath = $this->generatePdf($html, $client_id, $date_range);

        echo "<pre>";
        echo "Client ID: $client_id\n";
        echo "PDF Path: $pdfPath\n";
        echo "Exists: " . (file_exists($pdfPath) ? 'YES' : 'NO') . "\n";
        echo "</pre>";

        if (!file_exists($pdfPath)) {
            echo "<pre>PDF was not generated. Skip storing in Documents.</pre>";
            return;
        }

        $this->storePdfInDocuments($pdfPath, $client_id, $selected_year, $selected_currency);
    }

    protected function getContactRecordByClientId($client_id)
    {
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

        if (!$row) {
            return null;
        }

        return Vtiger_Record_Model::getInstanceById($row['contactid'], 'Contacts');
    }

    protected function getContactIdByClientId($client_id)
    {
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

        // Example file name M2001-AS-01-Mar-2026-31-Mar-2026
        $startDate = date('d-M-Y', strtotime($date_range[0]));
        $endDate = date('d-M-Y', strtotime($date_range[1]));

        // $fileName = $client_id . "_activity-summary";
        $fileName = sprintf(
            'M%s-AS-%s-%s-%s',
            $client_id,
            $startDate,
            $endDate,
            date('YmdHis')
        );
        $htmlPath = $root_directory . $fileName . '.html';
        $pdfPath = $root_directory . $fileName . '.pdf';

        file_put_contents($htmlPath, $html);

        $command = 'wkhtmltopdf --enable-local-file-access -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking '
            . escapeshellarg($htmlPath) . ' '
            . escapeshellarg($pdfPath) . ' 2>&1';

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        echo '<pre>';
        echo "Command:\n" . $command . "\n\n";
        echo "Return code: " . $returnVar . "\n";
        echo "Output:\n";
        print_r($output);
        echo "PDF exists: " . (file_exists($pdfPath) ? 'YES' : 'NO') . "\n";
        echo '</pre>';

        if (file_exists($htmlPath)) {
            unlink($htmlPath);
        }

        return $pdfPath;
    }

    protected function storePdfInDocuments($pdfPath, $client_id, $selected_year, $selected_currency)
    {
        global $adb, $current_user;

        // Needed by CRMEntity internally
        $this->initExecutionUser();

        if (!file_exists($pdfPath)) {
            throw new Exception("PDF file does not exist: " . $pdfPath);
        }

        $contactInfo = $this->getContactInfoByClientId($client_id);
        if (!$contactInfo) {
            throw new Exception("No contact found for client_id: " . $client_id);
        }

        $contactId = $contactInfo['contact_id'];
        $contactOwnerId = $contactInfo['owner_id'];

        $fileName = basename($pdfPath);
        $fileSize = filesize($pdfPath);
        $mimeType = 'application/pdf';

        $documentTitle = sprintf(
            'Monthly Activity Summary - %s - %s%s',
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
        $notes->column_fields['notecontent'] = 'Auto-generated monthly activity summary.';
        $notes->column_fields['assigned_user_id'] = $contactOwnerId; // assign to Client owner

        $notes->save('Documents');

        $documentId = $notes->id;
        if (!$documentId) {
            throw new Exception('Failed to create Documents record.');
        }

        $attachmentId = $adb->getUniqueID('vtiger_crmentity');

        $uploadDir = decideFilePath();
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $storedFileName = $attachmentId . '_' . $fileName;
        $destination = $uploadDir . $storedFileName;

        if (!copy($pdfPath, $destination)) {
            throw new Exception('Failed to copy PDF to storage directory: ' . $destination);
        }

        $adb->pquery(
            "INSERT INTO vtiger_crmentity
        (crmid, smcreatorid, smownerid, setype, description, createdtime, modifiedtime, presence, deleted)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)",
            [
                $attachmentId,
                $current_user->id,   // technical creator
                $contactOwnerId,     // actual owner = client owner
                'Documents Attachment',
                $documentTitle,
                1,
                0
            ]
        );

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

        echo "<pre>";
        echo "Document created successfully\n";
        echo "Document ID: {$documentId}\n";
        echo "Attachment ID: {$attachmentId}\n";
        echo "Related Contact ID: {$contactId}\n";
        echo "Document Owner ID: {$contactOwnerId}\n";
        echo "Stored file: {$destination}\n";
        echo "</pre>";
    }

    protected function getContactInfoByClientId($client_id)
    {
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

        if (!$row) {
            return null;
        }

        return [
            'contact_id' => (int)$row['contactid'],
            'owner_id'   => (int)$row['smownerid'],
        ];
    }

    protected function initExecutionUser()
    {
        global $current_user;

        if ($current_user && !empty($current_user->id)) {
            return $current_user;
        }

        require_once 'modules/Users/Users.php';
        $current_user = Users::getActiveAdminUser();

        if (!$current_user || empty($current_user->id)) {
            throw new Exception('Unable to initialize execution user.');
        }

        return $current_user;
    }
}
