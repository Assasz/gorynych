<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Testing;

use Gorynych\Testing\Constraint\ContainsSubset;
use Gorynych\Testing\Constraint\MatchesJsonSchema;

trait ApiAssertionsTrait
{
    /**
     * Asserts that response status code matches given one
     */
    protected static function assertStatusCodeSame(int $statusCode, string $message = ''): void
    {
        static::assertSame($statusCode, static::$client->getResponse()->getStatusCode(), $message);
    }

    /**
     * Asserts that response contains given subset
     *
     * @param mixed $subset
     */
    protected static function assertContainsSubset($subset, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        $constraint = new ContainsSubset($subset, $checkForObjectIdentity);

        static::assertThat(static::normalizeResponse(), $constraint, $message);
    }

    /**
     * Asserts that response matches given item resource JSON schema
     */
    protected static function assertMatchesItemJsonSchema(string $schemaClassName, ?int $checkMode = null, string $message = ''): void
    {
        self::matchesJsonSchema(static::normalizeResponse(), $schemaClassName, $checkMode, $message);
    }

    /**
     * Asserts that response matches given collection resource JSON schema
     */
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
