<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Generator;

use Faker\Factory;

final class EntityMockFactory
{
    /**
     * @param \ReflectionClass<object> $entityReflection
     * @return string[]
     */
    public static function create(\ReflectionClass $entityReflection): array
    {
        foreach ($entityReflection->getProperties() as $property) {
            if ('id' === $property->getName()) {
                continue;
            }

            $entityMock[$property->getName()] = self::resolvePropertyValue($property);
        }

        return $entityMock ?? [];
    }

    private static function resolvePropertyValue(\ReflectionProperty $property): string
    {
        $faker = Factory::create();

        switch ((string)$property->getType()) {
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
                $value = $faker->word;
        }

        return $value;
    }
}
