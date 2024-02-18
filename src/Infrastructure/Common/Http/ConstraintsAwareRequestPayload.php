<?php

namespace Infrastructure\Common\Http;

use Symfony\Component\Validator\Constraints;

interface ConstraintsAwareRequestPayload extends RequestPayload
{
    public function constraints(): Constraints\Collection;

    public function isArrayPayload(): bool;
}
