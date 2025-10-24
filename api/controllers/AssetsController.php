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
        //    Todo: implement asset creation
        throw new \BadMethodCallException('Not implemented');
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
