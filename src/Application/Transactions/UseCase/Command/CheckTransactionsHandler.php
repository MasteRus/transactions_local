<?php

namespace Application\Transactions\UseCase\Command;

use Infrastructure\Transactions\Request\TransactionDto;
use Infrastructure\Transactions\Response\TransactionsValidityDto;

class CheckTransactionsHandler
{

    public function __construct()
    {
    }

    /**
     * @throws \Exception
     */
    public function handle(CheckTransactionsCommand $command): TransactionsValidityDto
    {
        $balance = $command->getBalance();
        $invalidOrders = $checker = [];
        $validity = true;

        /** @var TransactionDto $transaction */
        foreach ($command->getTransactions() as $transaction) {
            switch ($transaction->txType):
                case TransactionDto::BET_TYPE:
                    $balance = bcsub($balance, (string)$transaction->amount);

                    if (bccomp($balance, '0') === -1) {
                        $result = true;
                        $checker[] = [
                            'transaction' => $transaction,
                            'validity'    => false
                        ];
                    } else {
                        $checker[] = [
                            'transaction' => $transaction,
                            'validity'    => true
                        ];
                    }
                    break;
                case TransactionDto::WIN_TYPE:
                    $balance = bcadd($balance, (string)$transaction->amount);
                    $checker[] = [
                        'transaction' => $transaction,
                        'validity'    => true
                    ];
                    break;
                default:
                    throw new \RuntimeException();
            endswitch;
        }


        return new TransactionsValidityDto($checker);
    }
}