<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Testing;

use Gorynych\Adapter\EntityManagerAdapterInterface;

trait DatabaseRefreshableTrait
{
    protected static ?EntityManagerAdapterInterface $entityManager;

    protected static function recreateDatabaseSchema(): void
    {
        static::dropDatabaseSchema();
        static::$entityManager->createSchema();
    }

    protected static function dropDatabaseSchema(): void
    {
        static::$entityManager->dropSchema();
    }
}
