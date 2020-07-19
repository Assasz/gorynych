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
    public static function create(string $entityNamespace): self
    {
        $self = new self();

        foreach ((new \ReflectionClass($entityNamespace))->getProperties() as $property) {
            if ('id' === $property->getName()) {
                continue;
            }

            $self->{$property->getName()} = self::resolvePropertyValue($property);
        }

        return $self;
    }

    /**
     * @return bool|int|mixed|string|null
     */
    private static function resolvePropertyValue(\ReflectionProperty $property)
    {
        /** @var \ReflectionNamedType $propertyType */
        $propertyType = $property->getType();
        $faker = Factory::create();

        switch ($propertyType->getName()) {
            case 'int':
                $value = $faker->randomDigit;
                break;
            case 'bool':
                $value = $faker->boolean;
                break;
            case 'string':
                try {
                    $value = $faker->{$property->getName()};
                } catch (\Throwable $t) {
                    $value = $faker->word;
                }
                break;
            default:
                $value = null;
        }

        return $value;
    }
}
