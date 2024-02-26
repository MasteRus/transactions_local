<?php

namespace Infrastructure\Transactions\Request;

class TransactionDto
{
    public const string TYPE_BET = "Bet";
    public const string TYPE_WIN = "Win";
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
