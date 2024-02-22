<?php

namespace Infrastructure\Transactions\Response;

use Infrastructure\Transactions\Request\TransactionDto;

class TransactionResponseDto
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