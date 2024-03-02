<?php

namespace Infrastructure\Transactions\Request;

use Application\Transactions\UseCase\Command\TransactionDto;
use Infrastructure\Common\Http\ConstraintsAwareRequestPayload;
use Symfony\Component\Validator\Constraints as Assert;

class CheckTransactionsPayload implements ConstraintsAwareRequestPayload
{
    /** @readonly  */
    public string $balance = '0';
    /**
     * @var TransactionDto[]
     */
    public array $transactions = [];

    public function fillFromPayload(array $payload): void
    {
        if (isset($payload['balance'])) {
            $this->balance = $payload['balance'];
        }

        if (isset($payload['transactions'])) {
            foreach ($payload['transactions'] as $transaction) {
                $this->transactions[] = new TransactionDto(
                    (int)$transaction['id'],
                    (int)$transaction['orderId'],
                    (float)$transaction['amount'],
                    (string)$transaction['txType'],
                );
            }
        }
    }

    public function constraints(): Assert\Collection
    {
        return new Assert\Collection(
            [
                'fields' => [
                    'balance' => new Assert\Required(
                        [
                            new Assert\Positive(),
                            new Assert\NotBlank(),
                        ]
                    ),
                    'transactions' => new Assert\Required(
                        [
                            new Assert\Type('array'),
                            new Assert\All([
                                new Assert\Collection([
                                    'fields' => [
                                        'id' => new Assert\Required(
                                            [
                                                new Assert\Positive(),
                                                new Assert\NotBlank(),
                                            ]
                                        ),
                                        'orderId' => new Assert\Required(
                                            [
                                                new Assert\Positive(),
                                                new Assert\NotBlank(),
                                            ]
                                        ),
                                        'amount' => new Assert\Required(
                                            [
                                                new Assert\Positive(),
                                                new Assert\NotBlank(),
                                            ]
                                        ),
                                        'txType' => new Assert\Required([
                                                new Assert\Choice(["Win", "Bet"]),
                                            ]),
                                    ]
                                ])
                            ])
                        ]
                    ),
                ]
            ]
        );
    }

    public function isArrayPayload(): bool
    {
        return false;
    }
}
