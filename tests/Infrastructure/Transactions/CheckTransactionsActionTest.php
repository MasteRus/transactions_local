<?php

namespace Tests\Infrastructure\Transactions;

use Tests\TestCase\ApiTestCase;

class CheckTransactionsActionTest extends ApiTestCase
{
    public const URL = '/api/transactions/check';

    public function testCheckTransactionsValidationErrorsTest(): void
    {
        $requestData = [
            'balance1'      => 90.0,
            'transactions2' => [
                [
                    'id'      => 1,
                    'orderId' => 1,
                    'amount'  => 50.00,
                    'txType'  => 'Bet'
                ]
            ]
        ];

        $this->request('POST', self::URL, $requestData);

        $this->assertResponseIsFail();
        $this->assertResponseMessageEquals('Validation errors');
        $this->assertResponseDataEquals('balance', 'This field is missing.');
        $this->assertResponseDataEquals('transactions', 'This field is missing.');
        $this->assertResponseDataEquals('balance1', 'This field was not expected.');
        $this->assertResponseDataEquals('transactions2', 'This field was not expected.');
    }


    public function testCheckTransactionsSuccessTest(): void
    {
        $requestData = [
            'balance'      => 90.0,
            'transactions' => [
                [
                    'id'      => 1,
                    'orderId' => 1,
                    'amount'  => 50.00,
                    'txType'  => 'Bet'
                ],
                [
                    'id'      => 2,
                    'orderId' => 1,
                    'amount'  => 10.00,
                    'txType'  => 'Bet'
                ],
                [
                    'id'      => 3,
                    'orderId' => 2,
                    'amount'  => 100.00,
                    'txType'  => 'Bet',
                ],
                [
                    'id'      => 4,
                    'orderId' => 3,
                    'amount'  => 100.00,
                    'txType'  => 'Win',
                ],
            ]
        ];

        $expectedTransactions = [
            [
                'id'       => 1,
                'orderId'  => 1,
                'amount'   => 50.00,
                'txType'   => 'Bet',
                'validity' => true
            ],
            [
                'id'       => 2,
                'orderId'  => 1,
                'amount'   => 10.00,
                'txType'   => 'Bet',
                'validity' => true
            ],
            [
                'id'       => 3,
                'orderId'  => 2,
                'amount'   => 100.00,
                'txType'   => 'Bet',
                'validity' => false
            ],
            [
                'id'       => 4,
                'orderId'  => 3,
                'amount'   => 100.00,
                'txType'   => 'Win',
                'validity' => true
            ],
        ];
        $this->request('POST', self::URL, $requestData);

        $this->assertResponseIsSuccess();
        $this->assertResponseDataFieldExists('transactions');
        $this->assertResponseDataEquals('transactions', $expectedTransactions);
    }

    public function testCheckTransactionsDuplicateIdSuccessTest(): void
    {
        $requestData = [
            'balance'      => 90.0,
            'transactions' => [
                [
                    'id'      => 1,
                    'orderId' => 1,
                    'amount'  => 5.01,
                    'txType'  => 'Bet'
                ],
                [
                    'id'      => 1, //Duplicate ID
                    'orderId' => 1,
                    'amount'  => 15.01,
                    'txType'  => 'Bet'
                ],
                [
                    'id'      => 3, //Next one with same OrderID also failed
                    'orderId' => 1,
                    'amount'  => 5.01,
                    'txType'  => 'Bet',
                ],
            ]
        ];

        $expectedTransactions = [
            [
                'id'       => 1,
                'orderId'  => 1,
                'amount'   => 5.01,
                'txType'   => 'Bet',
                'validity' => true
            ],
            [
                'id'       => 1,
                'orderId'  => 1,
                'amount'   => 15.01,
                'txType'   => 'Bet',
                'validity' => false
            ],
            [
                'id'       => 3,
                'orderId'  => 1,
                'amount'   => 5.01,
                'txType'   => 'Bet',
                'validity' => false
            ],
        ];
        $this->request('POST', self::URL, $requestData);

        $this->assertResponseIsSuccess();
        $this->assertResponseDataFieldExists('transactions');
        $this->assertResponseDataEquals('transactions', $expectedTransactions);
    }

    public function testCheckTransactionsSumInMinusSuccessTest(): void
    {
        $requestData = [
            'balance'      => 90.0,
            'transactions' => [
                [
                    'id'      => 1,
                    'orderId' => 1,
                    'amount'  => 50.00,
                    'txType'  => 'Bet'
                ],
                [
                    'id'      => 2,
                    'orderId' => 2,
                    'amount'  => 100.00, // SUm become minus
                    'txType'  => 'Bet'
                ],
                [
                    'id'      => 3, //Already minus
                    'orderId' => 1,
                    'amount'  => 5.01,
                    'txType'  => 'Bet',
                ],
            ]
        ];

        $expectedTransactions = [
            [
                'id'       => 1,
                'orderId'  => 1,
                'amount'   => 50,
                'txType'   => 'Bet',
                'validity' => true
            ],
            [
                'id'       => 2,
                'orderId'  => 2,
                'amount'   => 100,
                'txType'   => 'Bet',
                'validity' => false
            ],
            [
                'id'       => 3,
                'orderId'  => 1,
                'amount'   => 5.01,
                'txType'   => 'Bet',
                'validity' => false
            ],
        ];
        $this->request('POST', self::URL, $requestData);

        $this->assertResponseIsSuccess();
        $this->assertResponseDataFieldExists('transactions');
        $this->assertResponseDataEquals('transactions', $expectedTransactions);
    }
}