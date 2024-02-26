<?php

namespace Infrastructure\Common\Http;

use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

class JsonResponse extends SymfonyJsonResponse
{
    protected array $responseData;

    public function getStatus(): string
    {
        return $this->responseData['status'];
    }

    public function getData(): array
    {
        return $this->responseData['data'];
    }

    public function getFullData(): array
    {
        return $this->responseData;
    }

    public function getMessage(): string
    {
        return $this->responseData['message'];
    }

    public function setData(mixed $data = []): static
    {
        parent::setData($data);

        $this->responseData = json_decode((string)$this->data, true, 512, JSON_THROW_ON_ERROR);
        return $this;
    }
}
