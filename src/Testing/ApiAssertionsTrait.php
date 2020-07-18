<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Testing;

use Gorynych\Testing\Constraint\MatchesJsonSchema;
use Symfony\Component\HttpFoundation\Response;

trait ApiAssertionsTrait
{
    protected static function assertMatchesItemJsonSchema(Response $response, string $schemaClassName, ?int $checkMode = null, string $message = ''): void
    {
        self::matchesJsonSchema(self::normalizeResponse($response), $schemaClassName, $checkMode, $message);
    }

    protected static function assertMatchesCollectionJsonSchema(Response $response, string $schemaClassName, ?int $checkMode = null, string $message = ''): void
    {
        $data = self::normalizeResponse($response);

        self::matchesJsonSchema($data[0] ?? $data, $schemaClassName, $checkMode, $message);
    }

    /**
     * @param mixed[] $data
     */
    private static function matchesJsonSchema($data, string $schemaClassName, ?int $checkMode, string $message = ''): void
    {
        $constraint = new MatchesJsonSchema($schemaClassName, $checkMode);

        static::assertThat($data, $constraint, $message);
    }

    /**
     * @return mixed[]
     */
    private static function normalizeResponse(Response $response): array
    {
        $data = json_decode($response->getContent(), true);

        return $data['data'] ?? $data;
    }
}
