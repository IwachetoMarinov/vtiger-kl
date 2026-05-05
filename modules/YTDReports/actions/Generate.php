<?php

include_once 'modules/Contacts/cron/CronHelpers.php';
include_once 'modules/Contacts/cron/ActivitySummaryService.php';
include_once 'modules/Contacts/cron/StatementOfHoldingsService.php';

class YTDReports_Generate_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $contactId = $request->get('contact_id');

            if (!$contactId) throw new Exception('Missing contact_id');

            $contactRecord = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
            $client_id = $contactRecord->get('cf_898');

            if (!$client_id)  throw new Exception('Client ID not found');

            $date_range = Contacts_CronHelpers::buildMonthlyDateRange();

            $activityService = new Contacts_ActivitySummaryService();
            $activityService->generateAndStoreForClient(strval($client_id), $date_range);

            $holdingsService = new Contacts_StatementOfHoldingsService();
            $holdingsService->processClient(strval($client_id), $date_range);

            $response->setResult([
                'success' => true,
                'message' => 'YTD Reports generated successfully'
            ]);
        } catch (Throwable $e) {
            $response->setError($e->getMessage());
        }

        $response->emit();
    }
}
