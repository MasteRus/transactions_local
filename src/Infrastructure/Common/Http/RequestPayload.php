<?php

namespace Infrastructure\Common\Http;

interface RequestPayload
{
    public function fillFromPayload(array $payload): void;
}
