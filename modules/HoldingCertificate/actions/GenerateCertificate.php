<?php

include_once 'modules/HoldingCertificate/CertificateHandler.php';

class HoldingCertificate_GenerateCertificate_Action extends Vtiger_SaveAjax_Action {

    private $holdings = [];

    public function process(Vtiger_Request $request) {
        $contactID = $request->get('contactId');
        $certificateHandler = new GPM_CertificateHandler();
        $result = $certificateHandler->generateCertificate($contactID);

        $response = new Vtiger_Response();
        try {
            $response->setEmitType(Vtiger_Response::$EMIT_JSON);
            $response->setResult($result);
        } catch (DuplicateException $e) {
            $response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
        } catch (Exception $e) {
            $response->setError($e->getMessage());
        }
        $response->emit();
    }

}
