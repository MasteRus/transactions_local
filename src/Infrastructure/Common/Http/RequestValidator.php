<?php

namespace Infrastructure\Common\Http;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidator;

class RequestValidator implements RequestValidatorInterface
{
    private RequestStack $requestStack;
    //TEst
    protected SymfonyValidator $validator;
    protected ConstraintViolationListInterface $errors;

    public function __construct(RequestStack $requestStack, SymfonyValidator $validator)
    {
        $this->requestStack = $requestStack;
        $this->validator = $validator;
    }

    public function isRequestValid(ConstraintsAwareRequestPayload $payload): bool
    {
        $constraints = $payload->isArrayPayload() ? new All([$payload->constraints()]) : $payload->constraints();

        $this->errors = $this->validator->validate(
            $this->getRequestData(),
            $constraints
        );

        if ($this->errors->count()) {
            return false;
        }

        $payload->fillFromPayload($this->getRequestData());

        return true;
    }

    protected function getRequestData(): array
    {
        if (
            $this->requestStack->getCurrentRequest()->request->count() ||
            $this->requestStack->getCurrentRequest()->files->count()
        ) {
            return array_merge(
                $this->requestStack->getCurrentRequest()->request->all(),
                $this->requestStack->getCurrentRequest()->files->all()
            );
        }

        if ($this->requestStack->getCurrentRequest()?->isMethod('GET')) {
            return $this->requestStack->getCurrentRequest()->query->all();
        }

        $content = (string)$this->requestStack->getCurrentRequest()?->getContent();

        if (empty($content)) {
            throw new InvalidArgumentException('Empty POST request.');
        }

        $content = json_decode(
            $content,
            true,
            512,
            JSON_BIGINT_AS_STRING | (PHP_VERSION_ID >= 70300 ? JSON_THROW_ON_ERROR : 0)
        );

        if (PHP_VERSION_ID < 70300 && JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        if (!is_array($content)) {
            throw new InvalidArgumentException(sprintf(
                'JSON content was expected to decode to an array, "%s"',
                get_debug_type($content)
            ));
        }

        return $content;
    }

    public function getErrors(): array
    {
        $errors = [];
        foreach ($this->errors as $error) {
            $fieldNameWithoutBraces = trim($error->getPropertyPath(), '][');
            $fieldNameWithoutBraces = str_replace('][', '.', $fieldNameWithoutBraces);
            $errors[$fieldNameWithoutBraces] = $error->getMessage();
        }
        return $errors;
    }
}
