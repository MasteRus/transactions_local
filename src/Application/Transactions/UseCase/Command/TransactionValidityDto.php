<?php

namespace Application\Transactions\UseCase\Command;

class TransactionValidityDto
{
    public int $id;
    public int $orderId;
    public float $amount;
    public string $txType;
    public bool $validity;

    public function __construct(TransactionDto $transactionDto, bool $validity)
    {
        $this->id = $transactionDto->id;
        $this->orderId = $transactionDto->orderId;
        $this->amount = $transactionDto->amount;
        $this->txType = $transactionDto->txType;
        $this->validity = $validity;
    }
}
