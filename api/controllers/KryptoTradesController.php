<?php

namespace Api\Controller;

use Api\Model\KryptoTradesModel;

class KryptoTradesController
{
    private KryptoTradesModel $model;

    public function __construct(KryptoTradesModel $model)
    {
        $this->model = $model;
    }

    /** GET /api/krypto-trades */
    public function list(array $query): array
    {
        $limit = max(1, (int)($query['limit']  ?? 50));
        $offset = max(0, (int)($query['offset'] ?? 0));

        $rows = $this->model->fetchList($limit, $offset);

        return ['krypto_trades' => $rows, 'limit' => $limit, 'offset' => $offset];
    }

    /** POST /api/krypto-trades */
    public function create(string $rawBody): array
    {
        // return json_decode($rawBody, true);
        try {
            $errors = $this->validateCreate($rawBody);
            if (!empty($errors)) {
                return ['status' => 'error', 'errors' => $errors];
            }

            $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);

            $result = $this->model->create($data);

            return $result;
        } catch (\JsonException $e) {
            return ['status' => 'error', 'error' => 'Invalid JSON body'];
        }
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

    protected function validateCreate(string $rawBody): array
    {
        $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);

        $rules = [
            'admin_id'     => 'number|required',
            'contact_id'     => 'number|required',
            'wallet_address' => 'string|required',
            'trx_date'       => 'date|optional',
            'asset'          => 'option:[BTC,Ether,LTC]|required', // ✅ use ":" for clarity
        ];

        try {
            $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            $errors = \Api\Helper\Validator::validate($data, $rules);

            return $errors;
        } catch (\JsonException $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }
}
