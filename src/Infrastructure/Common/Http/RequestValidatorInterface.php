<?php

namespace Infrastructure\Common\Http;

interface RequestValidatorInterface
{
    public function isRequestValid(ConstraintsAwareRequestPayload $payload): bool;
    public function getErrors(): array;
}
