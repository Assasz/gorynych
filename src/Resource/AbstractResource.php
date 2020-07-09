<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource;

use Gorynych\Operation\ResourceOperationInterface;

abstract class AbstractResource
{
    protected const NUMERIC_ID = '(?P<id>[0-9]+)';

    /** @var mixed */
    public $id;

    /** @var ResourceOperationInterface[] */
    protected array $operations = [];

    /**
     * Returns path of the resource
     *
     * @return string
     */
    abstract public function getPath(): string;

    /**
     * Adds given operation to the resource
     *
     * @param ResourceOperationInterface $operation
     */
    public function addOperation(ResourceOperationInterface $operation): void
    {
        $this->operations[] = $operation;
    }

    /**
     * @return ResourceOperationInterface[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }
}
