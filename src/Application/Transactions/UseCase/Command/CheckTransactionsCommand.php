<?php

namespace Application\Transactions\UseCase\Command;

class CheckTransactionsCommand
{
    private string $balance;

    private array $transactions;

    public function __construct(
        string $balance,
        array $transactions
    ) {
        $this->balance = $balance;
        $this->transactions = $transactions;
    }

    public function getBalance(): string
    {
        return $this->balance;
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }
}
