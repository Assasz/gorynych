<?php

declare(strict_types=1);

namespace Http\Formatter;

use Gorynych\Http\Formatter\JsonFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JsonFormatterTest extends TestCase
{
    /**
     * @dataProvider provideCases
     * @param mixed $content
     * @param mixed $expectedContent
     */
    public function testFormatsJsonResponse($content, $expectedContent, int $statusCode, string $contentType): void
    {
        $response = (new JsonFormatter())->format($content, $statusCode);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedContent), $response->getContent());
        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame($contentType, $response->headers->get('Content-Type'));
    }

    /**
     * @return \Generator<array>
     */
    public function provideCases(): \Generator
    {
        yield 'successful response' => [
            ['foo' => 'bar'],
            ['data' => ['foo' => 'bar']],
            Response::HTTP_OK,
            'application/json',
        ];

        yield 'client error response' => [
            ['foo' => 'bar'],
            ['errors' => [['foo' => 'bar']]],
            Response::HTTP_BAD_REQUEST,
            'application/problem+json',
        ];

        yield 'server error response' => [
            ['foo' => 'bar'],
            ['errors' => [['foo' => 'bar']]],
            Response::HTTP_BAD_GATEWAY,
            'application/problem+json',
        ];
    }
}
