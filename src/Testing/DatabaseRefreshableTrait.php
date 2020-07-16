<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Testing;

use Gorynych\Adapter\TestEntityManagerInterface;

trait DatabaseRefreshableTrait
{
    protected ?TestEntityManagerInterface $entityManager;

    protected function recreateDatabaseSchema(): void
    {
        $this->dropDatabaseSchema();
        $this->entityManager->createSchema();
    }

    protected function dropDatabaseSchema(): void
    {
        $this->entityManager->dropSchema();
    }
}
