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
     * Setups validator
     *
     * @param string $constraint Constraint name to validate against
     * @return mixed
     */
    public function setup(string $constraint);

    /**
     * Validates provided entity object
     *
     * @param object $entity
     * @throws InvalidEntityException if entity is not valid
     */
    public function validate(object $entity): void;
}
