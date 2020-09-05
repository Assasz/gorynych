<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Testing;

use Faker\Factory;

final class EntityMock
{
    public static function create(string $entityClassName): self
    {
        $self = new self();
        $valueResolver = new FakerValueResolver(Factory::create());

        foreach ((new \ReflectionClass($entityClassName))->getProperties() as $property) {
            if (
                'id' === $property->getName() ||
                null === $value = $valueResolver->resolvePropertyValue($property)
            ) {
                continue;
            }

            $self->{$property->getName()} = $value;
        }

        return $self;
    }
}
