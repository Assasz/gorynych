<?php

declare(strict_types=1);

namespace Http\Routing;

use Gorynych\Http\Routing\UriMatcher;
use Gorynych\Resource\AbstractResource;
use PHPUnit\Framework\TestCase;

class UriMatcherTest extends TestCase
{
    /**
     * @dataProvider provideCases
     */
    public function testMatchesUri(
        string $uri,
        string $resourcePath,
        string $operationPath,
        bool $isMatch,
        ?string $id
    ): void {
        $result = UriMatcher::matchUri($uri, $resourcePath, $operationPath, $matches);

        $this->assertSame($isMatch, $result);
        $this->assertSame($id, $matches['id'] ?? null);
    }

    /**
     * @return \Generator<array>
     */
    public function provideCases(): \Generator
    {
        yield 'uri with id' => [
            '/resources/1/foo',
            '/resources/' . AbstractResource::NUMERIC_ID,
            '/foo',
            true,
            '1',
        ];

        yield 'uri without id' => [
            '/resources/foo',
            '/resources',
            '/foo',
            true,
            null,
        ];

        yield 'different uri' => [
            '/resources/foo',
            '/resources',
            '/bar',
            false,
            null,
        ];
    }
}
