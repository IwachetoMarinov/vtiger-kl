<?php

include_once 'dbo_db/HoldingsDB.php';
include_once 'include/Webservices/Create.php';
include_once 'modules/HoldingCertificate/phpqrcode/qrlib.php';

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

class GPM_CertificateHandler
{

    public $holdings = null;
    protected $site_URL;
    protected $base_url;

    function __construct()
    {
        global $site_URL;
        $this->site_URL = $site_URL;
        $this->base_url = $site_URL . 'id/';
    }

    function generateCertificate($contactID)
    {
        global $current_user, $root_directory;
        $contactRecordModel = Contacts_Record_Model::getInstanceById($contactID, 'Contacts');
        $contactWsID = vtws_getWebserviceEntityId('Contacts', $contactID);
        $guid = $this->guidv4(openssl_random_pseudo_bytes(16));

        $this->createQRCode($guid);

        $meta = $this->createHoldingCertificate($contactRecordModel, $guid);

        // SHOULD uncomment this after tests
        // unlink($root_directory . '/modules/HoldingCertificate/' . $guid . '.png');

        $certficate = array(
            'guid' => $guid,
            'contact_id' => $contactWsID,
            'certificate_status' => 'Active',
            'assigned_user_id' => vtws_getWebserviceEntityId('Users', $current_user->id),
            'notes_id' => $meta[0],
            'certificate_hash' => $meta[1],
            // 'verify_url' => 'https://certificates.global-precious-metals.com/id/' . $guid,
            'verify_url' => $this->base_url . $guid,
            'description' => json_encode($this->holdings),
        );

        $holdingCertificate = vtws_create('HoldingCertificate', $certficate, $current_user);
        return $holdingCertificate;
    }

    function guidv4($data)
    {
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    protected function createQRCode($guid)
    {
        global $root_directory;

        // $text = 'https://certificates.global-precious-metals.com/id/' . $guid;
        $text = $this->base_url . $guid;
        $qrPath = $root_directory . '/modules/HoldingCertificate/' . $guid . '.png';

        QRcode::png($text, $qrPath, QR_ECLEVEL_H, 10);
    }

    protected function createHoldingCertificate(Contacts_Record_Model $recordModel, $guid)
    {
        $clientID = $recordModel->get('cf_898');
        // $comId = $recordModel->get('related_entity');

        $holdings = new dbo_db\HoldingsDB();
        $holdings_data = $holdings->getHoldingsData($clientID);

        $this->holdings = $holdings_data;

        if (!empty($this->holdings)) {

            $viewer = new Vtiger_Viewer();
            $viewer->assign('RECORD_MODEL', $recordModel);
            $viewer->assign('site_URL', $this->site_URL);
            $viewer->assign('ERP_HOLDINGS', $this->processHoldingData($holdings_data));
            $viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($recordModel->get('related_entity')));
            $viewer->assign('GUID', $guid);
            $html = $viewer->view('HoldingCertificate.tpl', 'HoldingCertificate', true);
            $filename = $clientID . "_CERTIFICATE-OF-OWNERSHIP_" . date('d-M-Y');

            $this->createPDF($filename, $html);

            $documentName = $clientID . " - CERTIFICATE OF OWNERSHIP - " . date('d-M-Y');
            $note = $clientID . " - CERTIFICATE OF OWNERSHIP As Of " . date('d-M-Y');

            return $this->createDocument($filename, $documentName, $recordModel, $note);
        } else {

            return true;
        }
    }

    function createDocument($fileName, $documentName, $recordModel, $note = '')
    {
        global $root_directory, $current_user;

        // Full path to PDF generated earlier
        $absFileName = $root_directory . "modules/HoldingCertificate/tmp/$fileName.pdf";

        // ----------------------------------------------------------------------
        // 1️⃣ Create a REAL PHP temp file that vtiger will accept as upload
        // ----------------------------------------------------------------------
        $tmpFile = tempnam(sys_get_temp_dir(), 'vtiger_upload_');

        // Copy our certificate PDF into this real temp file
        copy($absFileName, $tmpFile);

        // ----------------------------------------------------------------------
        // 2️⃣ Build valid $_FILES structure for vtws_create()
        // ----------------------------------------------------------------------
        $_FILES['file'] = [
            'name' => $fileName . '.pdf',
            'type' => 'application/pdf',
            'tmp_name' => $tmpFile,
            'error' => 0,
            'size' => filesize($absFileName),
        ];

        // ----------------------------------------------------------------------
        // 3️⃣ Prepare vtiger Documents fields
        // ----------------------------------------------------------------------
        $params = [
            'notes_title'        => $documentName,
            'assigned_user_id'   => vtws_getWebserviceEntityId('Users', $current_user->id),
            'filelocationtype'   => 'I',
            'filestatus'         => 1,
            'filename'           => $fileName . '.pdf',
            'filetype'           => 'application/pdf',
            'filesize'           => filesize($absFileName),
            'notecontent'        => $note
        ];

        // ----------------------------------------------------------------------
        // 4️⃣ Create the Document record (this copies file to storage/YYYY/MM/)
        // ----------------------------------------------------------------------
        $ent = vtws_create('Documents', $params, $current_user);

        // ----------------------------------------------------------------------
        // 5️⃣ Relate the new Document to the Contact
        // ----------------------------------------------------------------------
        $idParts = explode('x', $ent['id']);
        $documentId = $idParts[1];

        $contact = new Contacts();
        $contact->save_related_module(
            'Contacts',
            $recordModel->getId(),
            'Documents',
            [$documentId]
        );

        // ----------------------------------------------------------------------
        // 6️⃣ Compute SHA-256 hash BEFORE deleting anything
        // ----------------------------------------------------------------------
        $hash = hash_file('sha256', $absFileName);

        // ----------------------------------------------------------------------
        // 7️⃣ Cleanup — COMMENTED OUT during debugging
        // ----------------------------------------------------------------------
        // unlink($absFileName);  // generated PDF in HoldingCertificate/tmp
        // unlink($tmpFile);      // real PHP temporary file

        return [$ent['id'], $hash];
    }



    protected function createPDF($fileName, $html)
    {
        // Check if wkhtmltopdf is installed
        if (!shell_exec("which wkhtmltopdf"))  return false;

        global $root_directory;

        // $handle = fopen($root_directory . $fileName . '.html', 'a') or die('Cannot open file:  ');
        $tmpDir = $root_directory . 'modules/HoldingCertificate/tmp/';
        $handle = fopen($tmpDir . $fileName . '.html', 'a') or die('Cannot open file:  ');

        fwrite($handle, $html);
        fclose($handle);
        // unlink($root_directory . "$fileName.pdf");
        unlink($tmpDir . "$fileName.pdf");
        exec("wkhtmltopdf --enable-local-file-access  -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking " . $tmpDir . "$fileName.html " . $tmpDir . "$fileName.pdf");
        // exec("wkhtmltopdf --enable-local-file-access  -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking " . $root_directory . "$fileName.html " . $root_directory . "$fileName.pdf");
        unlink($tmpDir . $fileName . '.html');
    }

    protected function processHoldingData($datas)
    {
        $newData = [];

        foreach ($datas as $data) {

            if (!is_array($data) || !isset($data['location'])) continue;

            $location = $data['location'];

            // Locations to convert to LFPSG
            $oldLocations = [
                'Brinks Singapore',
                'Fineart',
                'Malca-Amit Singapore',
                'Malca-Amit Singapore (Singapore)'
            ];

            if (in_array($location, $oldLocations)) $location = 'LFPSG';

            $data['location'] = $location;

            $newData[$location][] = $data;
        }

        return $newData;
    }
}
