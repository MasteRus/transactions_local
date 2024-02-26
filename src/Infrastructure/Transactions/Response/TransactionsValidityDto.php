<?php

namespace Infrastructure\Transactions\Response;

class TransactionsValidityDto
{
    public array $result;

    public function __construct(array $result)
    {
        $this->result = $result;
    }
}
