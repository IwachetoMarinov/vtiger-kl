<?php

namespace Api\Model;

use Users;

require_once 'include/utils/utils.php';
require_once 'data/CRMEntity.php';
require_once 'modules/Users/Users.php';
require_once 'includes/main/WebUI.php';
require_once 'include/Webservices/Create.php';

class KryptoTradesModel
{
    /** @var \PearDatabase */
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }


    public function fetchList(int $limit = 5, int $offset = 0): array
    {
        try {
            // Hardcode for now until implemented
            $rows = [
                [
                    'id' => 1,
                    'trade_pair' => 'BTC/USD',
                    'amount' => 0.1,
                    'price' => 50000,
                    'status' => 'Completed',
                ],
                [
                    'id' => 2,
                    'trade_pair' => 'ETH/USD',
                    'amount' => 1.5,
                    'price' => 4000,
                    'status' => 'Pending',
                ],
            ];

            return $rows;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function create(array $data): array
    {
        try {
            // 1️⃣ Set admin user context
            $adminId = $data['admin_id'] ?? 1;
            $adminUser = new Users();
            $adminUser->retrieveCurrentUserInfoFromFile($adminId);

            // 2️⃣ Build proper webservice ID for contact
            if (!empty($data['contact_id']) && is_numeric($data['contact_id'])) {
                $contactEntityId = 12; // vtiger_contactdetails entity ID
                $data['contact_id'] = $contactEntityId . 'x' . $data['contact_id'];
            }

            // 3️⃣ Prepare record data for vtiger webservice
            $recordData = [
                'contact_id'       => $data['contact_id'] ?? '',
                'wallet_address'   => $data['wallet_address'] ?? '',
                'asset'            => $data['asset'] ?? '',
                'trx_date'         => $data['trx_date'] ?? date('Y-m-d'),
                'assigned_user_id' => vtws_getWebserviceEntityId('Users', $adminId),
            ];

            // 4️⃣ Create record in vTiger (visible in CRM)
            $result = vtws_create('GPMCryptoTrx', $recordData, $adminUser);

            return [
                'status'  => 'success',
                'data'    => $result,
                'message' => 'Record created successfully and backup stored',
            ];
        } catch (\Throwable $e) {
            error_log('GPMCryptoTrx creation error: ' . $e->getMessage());

            return [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
