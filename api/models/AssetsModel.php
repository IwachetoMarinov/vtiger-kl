<?php

namespace Api\Model;

class AssetsModel
{
    /** @var \PearDatabase */
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }


    // Fetch list of assets
    public function fetchList(int $limit = 5, int $offset = 0): array
    {
        try {
            //    List all metals with SQL
            $sql = 'SELECT * FROM vtiger_metals ORDER BY metalsid DESC LIMIT ? OFFSET ?';
            $params[] = (int)$limit;
            $params[] = (int)$offset;

            $rs = $this->db->pquery($sql, $params);

            $rows = [];
            while ($row = $this->db->fetchByAssoc($rs)) {
                $rows[] = [
                    'id'            => $row['metalsid'],
                    'name'          => $row['name'],
                    'fineoz'       => $row['fineoz'],
                    'metal_type'        => (float)$row['metal_type'],
                    'created_time'  => $row['createdtime'],
                    'assigned_user_id' => $row['assigned_user_id'],
                ];
            }
            return $rows;
        } catch (\Exception $e) {
            return [];
        }
    }

    //    Create asset
    public function create(array $data): bool
    {
        try {
            $sql = 'INSERT INTO vtiger_metals (name, fineoz, metal_type, createdtime, assigned_user_id) VALUES (?, ?, ?, ?, ?)';
            $params = [
                $data['name'],
                $data['fineoz'],
                $data['metal_type'],
                date('Y-m-d H:i:s'),
                $data['assigned_user_id'],
            ];

            $this->db->pquery($sql, $params);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
