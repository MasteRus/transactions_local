<?php

namespace Tests\TestCase;

use Infrastructure\Common\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Utils\ArrayHelper;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiTestCase extends WebTestCase
{
    public const INT = 'int';
    public const DATETIME = 'datetime';

    protected ?Response $lastResponse = null;
    protected static KernelBrowser $client;

    protected function setUp(): void
    {

        static::$client = self::createAuthenticatedClient();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->lastResponse = null;
    }

    public function request(string $method, string $uri, array $parameters = [], array $files = []): Response
    {
        static::$client->request($method, $uri, $parameters, $files);
        $this->lastResponse = static::$client->getResponse();

        return $this->lastResponse;
    }

    public function jsonRequest(string $method, string $uri, array $value = []): Response
    {
        static::$client->request($method, $uri, [], [], [], json_encode($value, JSON_THROW_ON_ERROR));
        $this->lastResponse = static::$client->getResponse();

        return $this->lastResponse;
    }

    public function getLastResponse(): Response
    {
        return $this->lastResponse;
    }

    public function assertResponseIsSuccess(): void
    {
        self::assertEquals(JsonResponse::HTTP_OK, $this->lastResponse->getStatusCode());
        self::assertEquals('success', $this->lastResponse->getStatus());
    }

    public function assertResponseIsFail(): void
    {
        self::assertEquals(JsonResponse::HTTP_BAD_REQUEST, $this->lastResponse->getStatusCode());
        self::assertEquals('fail', $this->lastResponse->getStatus());
    }

    public function assertResponseIsError(): void
    {
        self::assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $this->lastResponse->getStatusCode());
        self::assertEquals('error', $this->lastResponse->getStatus());
    }

    public function assertResponseIsNotFound(): void
    {
        self::assertEquals(JsonResponse::HTTP_NOT_FOUND, $this->lastResponse->getStatusCode());
        self::assertEquals('fail', $this->lastResponse->getStatus());
    }

    public function assertResponseForbidden(): void
    {
        self::assertEquals(JsonResponse::HTTP_FORBIDDEN, $this->lastResponse->getStatusCode());
        self::assertEquals('fail', $this->lastResponse->getStatus());
    }

    public function assertResponseFieldEquals(string $fieldName, mixed $value): void
    {
        self::assertTrue(
            ArrayHelper::has($this->lastResponse->getFullData(), $fieldName),
            'Response don\'t contain key: ' . $fieldName
        );
        self::assertEquals(
            $value,
            ArrayHelper::get($this->lastResponse->getFullData(), $fieldName),
            sprintf(
                "Failed asserting response field '%s' that '%s' matches expected '%s'.",
                $fieldName,
                var_export(ArrayHelper::get($this->lastResponse->getData(), $fieldName), true),
                var_export($value, true)
            )
        );
    }

    public function assertResponseMessageEquals(string $value): void
    {
        self::assertEquals($this->lastResponse->getMessage(), $value);
    }

    public function assertResponseDataFieldExists(string $fieldName): void
    {
        self::assertTrue(
            ArrayHelper::has($this->lastResponse->getData(), $fieldName),
            'Response don\'t contain key: ' . $fieldName
        );
    }

    public function assertResponseDataFieldNotExists(string $fieldName): void
    {
        self::assertFalse(
            ArrayHelper::has($this->lastResponse->getData(), $fieldName),
            'Response don\'t contain key: ' . $fieldName
        );
    }

    public function assertResponseDataEquals(string $fieldName, mixed $value): void
    {
        $content = $this->lastResponse->getData();

        self::assertTrue(
            ArrayHelper::has($content, $fieldName),
            'Response don\'t contain key: ' . $fieldName
        );
        self::assertEquals(
            $value,
            ArrayHelper::get($content, $fieldName),
            sprintf(
                "Failed asserting response field '%s' that '%s' matches expected '%s'.",
                $fieldName,
                var_export(ArrayHelper::get($content, $fieldName), true),
                var_export($value, true)
            )
        );
    }

    public function assertResponseDataFieldType(string $fieldName, string $type): void
    {
        $content = $this->lastResponse->getData();

        self::assertArrayHasKey($fieldName, $content);

        switch ($type) {
            case self::INT:
                self::assertIsInt($content[$fieldName]);
                break;

            case self::DATETIME:
                $datetime = strtotime($content[$fieldName]);
                self::assertEquals(date('Y-m-d\TH:i:s\Z', $datetime), $content[$fieldName]);
                break;

            default:
                $this->addWarning('Unknown type: ' . $type);
        }
    }

    public function assertResponseFieldCount(string $fieldName, int $value): void
    {
        $content = $this->lastResponse->getData();

        $this->assertTrue(
            ArrayHelper::has($content, $fieldName),
            'Response don\'t contain key: ' . $fieldName
        );
        $this->assertCount(
            $value,
            ArrayHelper::get($content, $fieldName),
            sprintf(
                implode(
                    "\r\n",
                    [
                        "Failed asserting response field '%s' count '%s' matches expected '%s'.",
                        'Response payload: ' . $this->lastResponse->getContent()
                    ]
                ),
                $fieldName,
                var_export(count(ArrayHelper::get($content, $fieldName)), true),
                var_export($value, true)
            )
        );
    }

    protected static function createAuthenticatedClient(): KernelBrowser
    {
        $client = self::createClient();
        $client->disableReboot();

        return $client;
    }

    public function getPostRequest(array $data = []): RequestStack
    {
        $request = new Request([], $data, [], [], [], [], (string)json_encode($data, JSON_THROW_ON_ERROR));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        return $requestStack;
    }
}
