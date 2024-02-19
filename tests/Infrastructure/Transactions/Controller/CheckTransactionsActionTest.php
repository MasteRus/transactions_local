<?php

namespace Tests\Infrastructure\Transactions\Controller;

use Tests\TestCase\ApiTestCase;

class CheckTransactionsActionTest  extends ApiTestCase
{
    const REQUEST_1 = '{
    "balance": 90.00,
    "transactions": [
        {
            "id": 1,
            "orderId": 1,
            "amount": 100.00,
            "txType": "Bet"
        },
        {
            "id": 2,
            "orderId": 1,
            "amount": 130.00,
            "txType": "Win"
        }
    ]
}';

    public function testCreateCorrectly(): void
    {
        $uri = '/api/admins/create';
        $data = json_decode(self::REQUEST_1,true, 512, JSON_THROW_ON_ERROR);
        $this->request('POST', $uri, $data);

        $this->assertResponseIsSuccess();
    }
}