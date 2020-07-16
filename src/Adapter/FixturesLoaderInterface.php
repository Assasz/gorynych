<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Adapter;

interface FixturesLoaderInterface
{
    /**
     * Loads fixtures into persistence context
     *
     * @param string[] $files
     * @return int Number of loaded fixtures
     */
    public function loadFixtures(array $files): int;
}
