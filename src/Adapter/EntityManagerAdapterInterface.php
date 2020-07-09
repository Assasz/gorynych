<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Adapter;

interface EntityManagerAdapterInterface
{
    /**
     * Creates database schema
     */
    public function createSchema(): void;

    /**
     * Drops database schema
     */
    public function dropSchema(): void;

    /**
     * Loads fixtures into persistence storage
     *
     * @param array $files
     * @return int
     */
    public function loadFixtures(array $files): int;
}
