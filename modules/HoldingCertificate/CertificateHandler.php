<?php

include_once 'include/Webservices/Create.php';
include_once 'modules/Settings/OROSoft/api.php';
class GPM_CertificateHandler
{

    public $holdings = null;

    function generateCertificate($contactID)
    {
        global $current_user, $root_directory;
        $contactRecordModel = Contacts_Record_Model::getInstanceById($contactID, 'Contacts');
        $contactWsID = vtws_getWebserviceEntityId('Contacts', $contactID);

        $guid = $this->guidv4(openssl_random_pseudo_bytes(16));
        $this->createQRCode($guid);
        $meta = $this->createHoldingCertificate($contactRecordModel, $guid);
        unlink($root_directory . '/modules/HoldingCertificate/' . $guid . '.png');

        $certficate = array(
            'guid' => $guid,
            'contact_id' => $contactWsID,
            'certificate_status' => 'Active',
            'assigned_user_id' => vtws_getWebserviceEntityId('Users', $current_user->id),
            'notes_id' => $meta[0],
            'certificate_hash' => $meta[1],
            'verify_url' => 'https://certificates.global-precious-metals.com/id/' . $guid,
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

    function createQRCode($guid)
    {
        global $root_directory;
        $data = 'https://certificates.global-precious-metals.com/id/' . $guid;
        $size = '200x200';
        $logo = $root_directory . '/layouts/v7/modules/HoldingCertificate/gpm_logo_qr.png';
        $QR = imagecreatefrompng('https://chart.googleapis.com/chart?cht=qr&chld=H|1&chs=' . $size . '&chl=' . urlencode($data));

        $logo = imagecreatefromstring(file_get_contents($logo));

        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);

        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);

        // Scale logo to fit in the QR Code
        $logo_qr_width = $QR_width / 3;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;

        imagecopyresampled($QR, $logo, $QR_width / 3, $QR_height / 3, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);

        imagepng($QR, $root_directory . '/modules/HoldingCertificate/' . $guid . '.png');
    }

    function createHoldingCertificate(Contacts_Record_Model $recordModel, $guid)
    {
        $clientID = $recordModel->get('cf_950');
        $comId = $recordModel->get('related_entity');
        //echo "Trying to fetch Holding details of $clientID from ORO Soft\n";
        $this->holdings = getOROSoftHolding($clientID, $comId);
        //$holdings = getOROSoftHolding($clientID);
        if (!empty($this->holdings)) {
            //  echo "Holding information recieved\n";

            $viewer = new Vtiger_Viewer();
            $viewer->assign('RECORD_MODEL', $recordModel);
            $viewer->assign('OROSOFT_HOLDINGS', $this->processHoldingData($this->holdings));
            $viewer->assign('COMPANY', GPMCompany_Record_Model::getInstanceByCode($recordModel->get('related_entity')));
            $viewer->assign('GUID', $guid);
            $html = $viewer->view('HoldingCertificate.tpl', 'HoldingCertificate', true);
            $filename = $clientID . "_CERTIFICATE-OF-OWNERSHIP_" . date('d-M-Y');
            //echo "Creating PDF\n";
            $this->createPDF($filename, $html);
            //ec//ho "PDF Created $filename\n";
            //echo "Attaching the Holding Document.\n";
            $documentName = $clientID . " - CERTIFICATE OF OWNERSHIP - " . date('d-M-Y');
            $note = $clientID . " - CERTIFICATE OF OWNERSHIP As Of " . date('d-M-Y');
            return $this->createDocument($filename, $documentName, $recordModel, $note);
        } else {
            // echo "No Holding for this client, Exiting..\n";
            return true;
        }
    }

    function createDocument($fileName, $documentName, $recordModel, $note = '')
    {
        global $root_directory, $current_user;
        $absFileName = $root_directory . "$fileName.pdf";

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

        $file = array(
            'tmp_name' => $absFileName,
            'name' => end(explode('/', $absFileName)),
            'type' => finfo_file($fileInfo, $absFileName),
            'size' => filesize($absFileName),
            'error' => 0
        );



        $_FILES['file'] = $file;
        $params = array();
        $params['notes_title'] = $documentName;
        $params['filelocationtype'] = 'I'; // location type is internal
        $params['filestatus'] = '1'; //status always active
        $params['filename'] = $file['name'];
        $params['filetype'] = $file['type'];
        $params['filesize'] = $file['size'];
        $params['notecontent'] = $note;
        $params['assigned_user_id'] = vtws_getWebserviceEntityId('Users', $current_user->id);
        $ent = vtws_create('Documents', $params, $current_user);

        $id = explode('x', $ent['id']);
        $recordId = $id[1];
        $contact = new Contacts();
        $contact->save_related_module('Contacts', $recordModel->getId(), 'Documents', array($recordId));
        $hash = hash_file('sha256', $absFileName);
        unlink($absFileName);

        return [$ent['id'], $hash];
    }

    function createPDF($fileName, $html)
    {
        global $root_directory;
        $handle = fopen($root_directory . $fileName . '.html', 'a') or die('Cannot open file:  ');
        fwrite($handle, $html);
        fclose($handle);
        unlink($root_directory . "$fileName.pdf");
        exec("wkhtmltopdf --enable-local-file-access  -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking " . $root_directory . "$fileName.html " . $root_directory . "$fileName.pdf");
        unlink($root_directory . $fileName . '.html');
    }

    function processHoldingData($datas)
    {
        $newData = [];
        foreach ($datas as $data) {
            $modifiedSerials = $this->sumSerials($data->serials);
            $data->modiefiedSerials = $modifiedSerials;
            if (in_array($data->location, array('Brinks Singapore', 'Fineart', 'Malca-Amit Singapore'))) {
                $data->location = 'LFPSG';
            }
            $newData[$data->location][] = $data;
        }
        //print_r(array_values($newData));exit();
        return $newData;
    }

    function splitAndOrder($values)
    {
        $b = [];
        $subList = [];
        $prev_n = -1;
        foreach ($values as $n) {
            if ($prev_n + 1 != $n && !empty($subList)) {
                $b[] = $subList;
                $subList = [];
            }
            $subList[] = $n;
            $prev_n = $n;
        }
        $b[] = $subList;
        return $b;
    }

    function genSerialSequance($prefix, $serialsSequance)
    {
        $serialStringrray = [];
        foreach ($serialsSequance as $serials) {
            if (count($serials) > 1) {
                $serialGroup = $prefix . $serials[0] . '-' . $prefix . end($serials);
            } else {
                $serialGroup = $prefix . $serials[0];
            }

            $serialStringrray[] = str_replace('single', '', $serialGroup);
        }
        return $serialStringrray;
    }

    function sumSerials($serials)
    {
        $splitList = array();
        foreach ($serials as $serial) {
            $part = preg_split('/(?<=\D)(?=\d)/', $serial);
            if (count($part) > 1) {
                $splitList[$part[0]][] = $part[1];
            } else {
                $splitList['single'][] = $part[0];
            }
        }
        $lsitOfSerials = [];
        foreach ($splitList as $key => $value) {
            sort($value);
            $lsitOfSerials = array_merge($lsitOfSerials, $this->genSerialSequance($key, $this->splitAndOrder($value)));
        }
        return $lsitOfSerials;
    }
}
