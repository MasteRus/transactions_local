<?php

namespace Application\Transactions\UseCase\Command;

class TransactionDto
{
    public const TYPE_BET = "Bet";
    public const TYPE_WIN = "Win";
    public int $id;
    public int $orderId;
    public float $amount;
    public string $txType;

    public function __construct(int $id, int $orderId, float $amount, string $txType)
    {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->txType = $txType;
    }
}
