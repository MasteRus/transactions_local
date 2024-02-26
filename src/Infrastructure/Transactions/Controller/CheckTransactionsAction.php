<?php

namespace Infrastructure\Transactions\Controller;

use Application\Transactions\UseCase\Command\CheckTransactionsCommand;
use Application\Transactions\UseCase\Command\CheckTransactionsHandler;
use Infrastructure\Common\Http\JsonResponder;
use Infrastructure\Common\Http\JsonResponse;
use Infrastructure\Common\Http\RequestValidatorInterface;
use Infrastructure\Transactions\Request\CheckTransactionsPayload;
use InvalidArgumentException;
use Symfony\Component\Routing\Annotation\Route;

class CheckTransactionsAction
{
    private JsonResponder $responder;
    private RequestValidatorInterface $validator;
    private CheckTransactionsHandler $handler;

    public function __construct(
        JsonResponder $responder,
        RequestValidatorInterface $validator,
        CheckTransactionsHandler $handler
    )
    {
        $this->responder = $responder;
        $this->validator = $validator;
        $this->handler = $handler;
    }

    #[Route('/api/transactions/check', name: 'transactions_check', methods: ['post'])]
    public function checkTransaction(CheckTransactionsPayload $payload): JsonResponse
    {
        try {
            if (!$this->validator->isRequestValid($payload)) {
                return $this->responder->respondFailValidation($this->validator->getErrors());
            }

            $response = $this->handler->handle(new CheckTransactionsCommand($payload->balance, $payload->transactions));
        } catch (InvalidArgumentException $exception) {
            return $this->responder->respondFail($exception->getMessage());
        }

        return $this->responder->respondSuccess(
            [
                'transactions' => $response,
            ]
        );
    }
}