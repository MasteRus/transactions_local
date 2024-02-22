<?php

namespace Application\Transactions\UseCase\Command;

use Exception;
use Infrastructure\Transactions\Request\TransactionDto;
use Infrastructure\Transactions\Response\TransactionResponseDto;

class CheckTransactionsHandler
{


    /**
     * @throws Exception
     */
    public function handle(CheckTransactionsCommand $command): array
    {
        $balance = $command->getBalance();
        $checker = $ordersValidity = $usedIds = [];

        /*
        Транзакции обрабатываются от меньшего id к большему.

        (OK)Bet уменьшает баланс на сумму в amount, Win увеличивает.
        (OK)Если баланс ушел в минус, транзакция считается невалидной.
        (OK)Если транзакция не валидна, последующие транзакции с тем же orderId тоже считаются невалидными.
        (OK)Если id транзакции повторяется, такая транзакция тоже не валидна, но остальные с тем же orderId должны быть обработаны.
         */

        /** @var TransactionDto $transaction */
        $transactions = $command->getTransactions();
        foreach ($transactions as $transaction) {
            if (in_array($transaction->id, $usedIds, true)) {
                $checker[] = new TransactionResponseDto($transaction, false);
                $ordersValidity[$transaction->orderId] = false;
                continue;
            }
            $usedIds[] = $transaction->id;

            if (array_key_exists($transaction->orderId, $ordersValidity) && $ordersValidity[$transaction->orderId] === false) {
                $checker[] = new TransactionResponseDto($transaction, false);
                continue;
            }

            switch ($transaction->txType):
                case TransactionDto::TYPE_BET:
                    $balance = bcsub($balance, (string)$transaction->amount);

                    if (bccomp($balance, '0') === -1) {
                        $checker[] = new TransactionResponseDto($transaction, false);
                        $ordersValidity[$transaction->orderId] = false;
                    } else {
                        $checker[] = new TransactionResponseDto($transaction, true);
                    }
                    break;
                case TransactionDto::TYPE_WIN:
                    $balance = bcadd($balance, (string)$transaction->amount);
                    $checker[] = new TransactionResponseDto($transaction, true);
                    break;
                default:
                    throw new \RuntimeException();
            endswitch;
        }

        return $checker;
    }
}