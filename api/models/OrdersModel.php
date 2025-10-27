<?php

namespace Api\Model;

class OrdersModel
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
            //    List all metals with SQL
            // $sql = 'SELECT * FROM vtiger_metals ORDER BY metalsid DESC LIMIT ? OFFSET ?';
            // $params[] = (int)$limit;
            // $params[] = (int)$offset;

            // $rs = $this->db->pquery($sql, $params);

            // $rows = [];
            // while ($row = $this->db->fetchByAssoc($rs)) {
            //     $rows[] = [
            //         'id'            => $row['metalsid'],
            //         'name'          => $row['name'],
            //         'fineoz'       => $row['fineoz'],
            //         'metal_type'        => (float)$row['metal_type'],
            //         'created_time'  => $row['createdtime'],
            //         'assigned_user_id' => $row['assigned_user_id'],
            //     ];
            // }
            // Hardcode for now until implemented
            $rows = [
                [
                    'id' => 1,
                    'order_number' => 'ORD-001',
                    'customer_name' => 'John Doe',
                    'total_amount' => 150.00,
                    'status' => 'Processing',
                ],
                [
                    'id' => 2,
                    'order_number' => 'ORD-002',
                    'customer_name' => 'Jane Smith',
                    'total_amount' => 250.00,
                    'status' => 'Shipped',
                ],
            ];

            return $rows;
        } catch (\Exception $e) {
            return [];
        }
    }
}
