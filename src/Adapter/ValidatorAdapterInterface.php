<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Adapter;

use Gorynych\Resource\Exception\InvalidEntityException;

interface ValidatorAdapterInterface
{
    /**
     * @param string $constraint
     * @return mixed
     */
    public function setup(string $constraint);

    /**
     * @param object $entity
     * @throws InvalidEntityException
     */
    public function validate(object $entity): void;
}
