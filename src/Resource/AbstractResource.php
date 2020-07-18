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
    protected const NUMERIC_ID = '(?P<id>[\d]+)';
    protected const ALNUM_ID = '(?P<id>[\w]+)';

    public ?string $id;

    /** @var ResourceOperationInterface[] */
    protected array $operations = [];

    /**
     * Returns path of the resource
     * Item resources should follow given pattern:
     * '/resources/' . $this->id ?? self::NUMERIC_ID (or any other regex)
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
        $operation->setResource($this);
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
