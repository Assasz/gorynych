<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\TestUtil;

use Gorynych\Adapter\EntityManagerAdapterInterface;

trait DatabaseRefreshableTrait
{
    protected ?EntityManagerAdapterInterface $entityManager;

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
