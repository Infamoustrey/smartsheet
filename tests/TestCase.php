<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\RequestInterface;
use Smartsheet\SmartsheetClient;

class TestCase extends BaseTestCase
{
    protected function getClient(array $responses = [], array &$history = []): SmartsheetClient
    {
        $mockHandler = new MockHandler(
            array_map(function ($response) {
                if ($response instanceof Response) {
                    return $response;
                }

                return $this->mockJsonResponse($response);
            }, $responses)
        );

        $history = [];
        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push(Middleware::history($history));

        return new SmartsheetClient([
            'token' => 'test-token',
            'handler' => $handlerStack,
        ]);
    }

    protected function mockJsonResponse(array $payload, int $statusCode = 200): Response
    {
        return new Response(
            $statusCode,
            ['Content-Type' => 'application/json'],
            json_encode($payload)
        );
    }

    protected function assertRequest(
        array $history,
        int $index,
        string $method,
        string $path,
        ?string $query = null
    ): void {
        $this->assertArrayHasKey($index, $history, "Expected request #$index to exist.");

        /** @var RequestInterface $request */
        $request = $history[$index]['request'];

        $this->assertSame($method, $request->getMethod());
        $this->assertSame('/2.0/'.ltrim($path, '/'), $request->getUri()->getPath());

        if ($query !== null) {
            $this->assertSame($query, $request->getUri()->getQuery());
        }
    }
}
