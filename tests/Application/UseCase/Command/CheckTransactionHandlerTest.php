<?php

namespace Tests\Application\UseCase\Command;

use Application\Transactions\UseCase\Command\CheckTransactionsCommand;
use Application\Transactions\UseCase\Command\CheckTransactionsHandler;
use Application\Transactions\UseCase\Command\TransactionDto;
use PHPUnit\Framework\TestCase;

class CheckTransactionHandlerTest extends TestCase
{
    /*
    Транзакции обрабатываются от меньшего id к большему.

    (OK)Bet уменьшает баланс на сумму в amount, Win увеличивает.
    (OK)Если баланс ушел в минус, транзакция считается невалидной.

    (OK)Если транзакция не валидна, последующие транзакции с тем же orderId тоже считаются невалидными.

    (OK)Если id транзакции повторяется, такая транзакция тоже не валидна,
    но остальные с тем же orderId должны быть обработаны.
     */

    public function testSameIdsTest(): void
    {
        $balance = 10.0;
        $transactions[] = new TransactionDto(1, 1, 100, TransactionDto::TYPE_WIN);
        $transactions[] = new TransactionDto(1, 2, 300, TransactionDto::TYPE_WIN);
        $transactions[] = new TransactionDto(2, 1, 300, TransactionDto::TYPE_WIN);
        $command = new CheckTransactionsCommand($balance, $transactions);
        $handler = new CheckTransactionsHandler();
        $result = $handler->handle($command);

        $this->assertTrue($result[0]->validity);
        $this->assertFalse($result[1]->validity);
        $this->assertTrue($result[2]->validity);
    }

    public function testFirstMinusTransactionValidationFailedTest(): void
    {
        $balance = 10.0;
        $transactions[] = new TransactionDto(1, 1, 100, TransactionDto::TYPE_BET);
        $transactions[] = new TransactionDto(2, 1, 100, TransactionDto::TYPE_WIN);
        $transactions[] = new TransactionDto(3, 2, 100, TransactionDto::TYPE_WIN);
        $command = new CheckTransactionsCommand($balance, $transactions);
        $handler = new CheckTransactionsHandler();
        $result = $handler->handle($command);

        $this->assertFalse($result[0]->validity);
        $this->assertFalse($result[1]->validity);
        $this->assertTrue($result[2]->validity);
    }

    public function testFirstNormalTransactionValidationFailedTest(): void
    {
        $balance = 100.0;
        $transactions[] = new TransactionDto(1, 1, 50, TransactionDto::TYPE_BET);
        $transactions[] = new TransactionDto(2, 1, 60, TransactionDto::TYPE_BET);
        $transactions[] = new TransactionDto(3, 1, 100, TransactionDto::TYPE_WIN);
        $command = new CheckTransactionsCommand($balance, $transactions);
        $handler = new CheckTransactionsHandler();
        $result = $handler->handle($command);

        $this->assertTrue($result[0]->validity);
        $this->assertFalse($result[1]->validity);
        $this->assertFalse($result[2]->validity);
    }

    public function testReverseOrderTest(): void
    {
        $balance = 10.0;
        $transactions[] = new TransactionDto(2, 1, 100, TransactionDto::TYPE_BET);
        $transactions[] = new TransactionDto(1, 1, 300, TransactionDto::TYPE_WIN);
        $command = new CheckTransactionsCommand($balance, $transactions);
        $handler = new CheckTransactionsHandler();
        $result = $handler->handle($command);

        $this->assertTrue($result[0]->validity);
        $this->assertTrue($result[1]->validity);
    }

    public function testReverseOrderWithSameIdTest(): void
    {
        $balance = 10.0;
        $transactions[] = new TransactionDto(2, 1, 100, TransactionDto::TYPE_BET);
        $transactions[] = new TransactionDto(2, 2, 200, TransactionDto::TYPE_BET);
        $transactions[] = new TransactionDto(3, 2, 1, TransactionDto::TYPE_WIN);
        $transactions[] = new TransactionDto(1, 1, 300, TransactionDto::TYPE_WIN);
        $command = new CheckTransactionsCommand($balance, $transactions);
        $handler = new CheckTransactionsHandler();
        $result = $handler->handle($command);


        $this->assertTrue($result[0]->validity);
        $this->assertTrue($result[1]->validity);
        $this->assertFalse($result[2]->validity);
        $this->assertTrue($result[3]->validity);
        $this->assertEquals(100, $result[1]->amount);
        $this->assertEquals(200, $result[2]->amount);
        $this->assertEquals(3, $result[3]->id);
    }
}
