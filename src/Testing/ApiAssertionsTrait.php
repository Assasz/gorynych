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
    protected static function assertStatusCodeSame(int $statusCode, string $message = ''): void
    {
        static::assertSame($statusCode, static::$client->getResponse()->getStatusCode(), $message);
    }

    protected static function assertMatchesItemJsonSchema(string $schemaClassName, ?int $checkMode = null, string $message = ''): void
    {
        self::matchesJsonSchema(static::normalizeResponse(), $schemaClassName, $checkMode, $message);
    }

    protected static function assertMatchesCollectionJsonSchema(string $schemaClassName, ?int $checkMode = null, string $message = ''): void
    {
        $data = static::normalizeResponse();

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
}
