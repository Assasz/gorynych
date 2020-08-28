<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource;

use Cake\Collection\Collection;
use Gorynych\Operation\ResourceOperationInterface;

abstract class AbstractResource
{
    protected const NUMERIC_ID = '(?P<id>[\d]+)';
    protected const ALNUM_ID = '(?P<id>[\w]+)';

    public ?string $id;

    /** @var ResourceOperationInterface[] */
    protected array $operations = [];

    /**
     * Returns path of the resource
     *
     * @return string
     * @example '/resources/'
     * @example '/resources/' . $this->id ?? self::NUMERIC_ID
     */
    abstract public function getPath(): string;

    /**
     * Adds given operation to the resource
     *
     * @param ResourceOperationInterface $operation
     */
    public function addOperation(ResourceOperationInterface $operation): void
    {
        $operation->setResource($this);
        $this->operations[] = $operation;
    }

    /**
     * @return ResourceOperationInterface[]|Collection
     */
    public function getOperations(): Collection
    {
        return new Collection($this->operations);
    }
}
