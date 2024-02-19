<?php

namespace Tests\TestCase;

use Infrastructure\Common\Http\JsonResponse;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ApiTestCase extends WebTestCase
{
    protected ?Response $lastResponse = null;
    protected static KernelBrowser $client;

    protected function setUp(): void
    {
        self::bootKernel();
//        if (null === static::$kernel) {
//            static::bootKernel();
//        }
//
//        /** @var KernelBrowser $client */
//        $client = static::$kernel->getContainer()->get('test.client');
//        $client->disableReboot();
//
//        static::$client = $client;
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
        static::$client->request($method, $uri, [], [], [], json_encode($value));
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

    /**
     * @param string $fieldName
     * @param mixed $value
     */
    public function assertResponseFieldEquals(string $fieldName, $value): void
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

    /**
     * @param string $fieldName
     */
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

    /**
     * @param string $fieldName
     * @param mixed $value
     */
    public function assertResponseDataEquals(string $fieldName, $value): void
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



    public function getPostRequest(array $data = []): RequestStack
    {
        $request = new Request([], $data, [], [], [], [], (string)json_encode($data));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        return $requestStack;
    }
}
