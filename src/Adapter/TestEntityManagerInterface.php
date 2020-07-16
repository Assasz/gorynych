<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Adapter;

interface TestEntityManagerInterface extends FixturesLoaderInterface
{
    /**
     * Creates persistence storage schema
     */
    public function createSchema(): void;

    /**
     * Drops persistence storage schema
     */
    public function dropSchema(): void;
}
