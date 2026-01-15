<?php

namespace Api\Controller;

use Api\Model\AssetsModel;

class AssetsController
{
    private AssetsModel $model;

    public function __construct(AssetsModel $model)
    {
        $this->model = $model;
    }

    /** GET /api/assets */
    public function list(array $query): array
    {
        $limit  = max(1, (int)($query['limit']  ?? 50));
        $offset = max(0, (int)($query['offset'] ?? 0));

        $rows  = $this->model->fetchList($limit, $offset);

        return ['assets' => $rows, 'limit' => $limit, 'offset' => $offset];
    }

    /** POST /api/assets */
    public function create(string $rawBody): array
    {
        try {
            $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            // Validate required fields
            if (empty($data['name']) || !isset($data['fineoz']) || !isset($data['metal_type']) || empty($data['assigned_user_id'])) {
                throw new \InvalidArgumentException('Missing required fields');
            }

            // $this->model->create($data);
            return ['status' => 'success', 'message' => 'Asset created', "data" => $data];
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Invalid JSON body');
        }
    }

    /** GET /api/assets/search?q=... */
    public function search(array $query): array
    {
        //    Todo: implement asset search
        throw new \BadMethodCallException('Not implemented');
    }

    /** GET /api/books/stats */
    public function stats(): array
    {
        //    Todo: implement asset stats
        throw new \BadMethodCallException('Not implemented');
    }
}
