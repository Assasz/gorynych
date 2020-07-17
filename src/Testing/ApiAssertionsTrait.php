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
    public static function assertMatchesItemJsonSchema(Response $response, string $schemaClassName, ?int $checkMode = null, string $message = ''): void
    {
        $data = json_decode($response->getContent(), true);

        self::matchesJsonSchema($data, $schemaClassName, $checkMode, $message);
    }

    public static function assertMatchesCollectionJsonSchema(Response $response, string $schemaClassName, ?int $checkMode = null, string $message = ''): void
    {
        $data = json_decode($response->getContent(), true);
        $data = (0 === count($data)) ? $data : $data[0];

        self::matchesJsonSchema($data, $schemaClassName, $checkMode, $message);
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
