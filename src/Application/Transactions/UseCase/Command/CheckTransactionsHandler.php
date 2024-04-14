<?php

namespace Application\Transactions\UseCase\Command;

use Exception;
use RuntimeException;

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
        (OK)Транзакции обрабатываются от меньшего id к большему. В случае, если

        (OK)Bet уменьшает баланс на сумму в amount, Win увеличивает.
        (OK)Если баланс ушел в минус, транзакция считается невалидной.
        (OK)Если транзакция не валидна, последующие транзакции с тем же orderId тоже считаются невалидными.
        (OK)Если id транзакции повторяется, такая транзакция тоже не валидна,
        но остальные с тем же orderId должны быть обработаны.
         */

        /** @var TransactionDto[] $transactions */
        $transactions = $command->getTransactions();

        usort($transactions, static function ($elemA, $elemB) use (&$transactions) {
            $indexA = array_search($elemA, $transactions, true);
            $indexB = array_search($elemB, $transactions, true);

            if ($elemA->id === $elemB->id) {
                return ($indexA < $indexB) ? -1 : 1;
            }

            return ($elemA->id < $elemB->id) ? -1 : 1;
        });

        foreach ($transactions as $transaction) {
            if (in_array($transaction->id, $usedIds, true)) {
                $checker[] = new TransactionValidityDto($transaction, false);
//                $ordersValidity[$transaction->orderId] = false;
                continue;
            }
            $usedIds[] = $transaction->id;

            if (
                array_key_exists($transaction->orderId, $ordersValidity) &&
                $ordersValidity[$transaction->orderId] === false
            ) {
                $checker[] = new TransactionValidityDto($transaction, false);
                continue;
            }

            switch ($transaction->txType) :
                case TransactionDto::TYPE_BET:
                    $balance = bcsub($balance, (string)$transaction->amount);

                    if (bccomp($balance, '0') === -1) {
                        $checker[] = new TransactionValidityDto($transaction, false);
                        $ordersValidity[$transaction->orderId] = false;
                    } else {
                        $checker[] = new TransactionValidityDto($transaction, true);
                    }
                    break;
                case TransactionDto::TYPE_WIN:
                    $balance = bcadd($balance, (string)$transaction->amount);
                    $checker[] = new TransactionValidityDto($transaction, true);
                    break;
                default:
                    throw new RuntimeException("Unknown Type of Transaction");
            endswitch;
        }

        return $checker;
    }
}
