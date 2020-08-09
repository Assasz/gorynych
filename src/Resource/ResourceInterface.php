<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource;

use Cake\Collection\Collection;

interface ResourceInterface
{
    /**
     * Returns TRUE if resource class supports given item type
     *
     * @param mixed $item
     * @return bool
     */
    public function supports($item): bool;

    /**
     * Retrieves resource
     * GET /resources/{id} | /resources
     *
     * @return object|object[]|Collection
     */
    public function retrieve();

    /**
     * Removes resource
     * DELETE /resources/{id} | /resources
     */
    public function remove(): void;

    /**
     * Replaces resource with new one
     * PUT /resources/{id} | /resources
     *
     * @param mixed $item
     * @return string Path to the replaced resource
     */
    public function replace($item): string;

    /**
     * Saves changes made to resource
     */
    public function save(): void;
}
