<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource;

interface CollectionResourceInterface extends ResourceInterface
{
    /**
     * Inserts new item to the resource collection
     * POST /resources
     *
     * @param mixed $item
     * @return string Path to the inserted resource
     */
    public function insert($item): string;
}
