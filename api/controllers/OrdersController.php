<?php

namespace Api\Controller;

use Api\Model\OrdersModel;

class OrdersController
{
    private OrdersModel $model;

    public function __construct(OrdersModel $model)
    {
        $this->model = $model;
    }

    /** GET /api/orders */
    public function list(array $query): array
    {
        $limit  = max(1, (int)($query['limit']  ?? 50));
        $offset = max(0, (int)($query['offset'] ?? 0));

        $rows  = $this->model->fetchList($limit, $offset);

        return ['orders' => $rows, 'limit' => $limit, 'offset' => $offset];
    }

    /** POST /api/orders */
    public function create(string $rawBody): array
    {
        //    Todo: implement order creation
        throw new \BadMethodCallException('Not implemented');
    }

    /** GET /api/orders/search?q=... */
    public function search(array $query): array
    {
        //    Todo: implement order search
        throw new \BadMethodCallException('Not implemented');
    }

    /** GET /api/orders/stats */
    public function stats(): array
    {
        //    Todo: implement order stats
        throw new \BadMethodCallException('Not implemented');
    }
}
