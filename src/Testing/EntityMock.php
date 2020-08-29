<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Testing;

use Faker\Factory;
use Webmozart\Assert\Assert;

final class EntityMock
{
    public static function create(string $entityNamespace): self
    {
        $self = new self();

        foreach ((new \ReflectionClass($entityNamespace))->getProperties() as $property) {
            if (
                'id' === $property->getName() ||
                null === $value = $self->resolvePropertyValue($property)
            ) {
                continue;
            }

            $self->{$property->getName()} = $value;
        }

        return $self;
    }

    /**
     * @return bool|int|mixed|string|null
     */
    private function resolvePropertyValue(\ReflectionProperty $property)
    {
        $propertyType = $property->getType();
        Assert::isInstanceOf($propertyType, \ReflectionNamedType::class);

        $faker = Factory::create();

        switch ($propertyType->getName()) {
            case 'string':
                try {
                    $value = $faker->{$property->getName()};
                } catch (\Throwable $t) {
                    $value = $faker->word;
                }
                break;
            case 'int':
                $value = $faker->randomDigit;
                break;
            case 'bool':
                $value = $faker->boolean;
                break;
            case 'DateTime':
                $value = $faker->dateTime->format('Y-m-d H:i');
                break;
            default:
                $value = null;
        }

        return $value;
    }
}
