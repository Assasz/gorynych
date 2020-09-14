<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource;

use Gorynych\Exception\NotExistentResourceException;

interface ResourceLoaderInterface
{
    /**
     * @return string[]
     */
    public function getResources(): array;

    /**
     * Loads resource by given classname
     *
     * @throws NotExistentResourceException
     */
    public function loadResource(string $resourceClass): AbstractResource;
}
