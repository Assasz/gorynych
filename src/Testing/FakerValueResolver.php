<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Testing;

use Faker\Generator;
use Webmozart\Assert\Assert;

final class FakerValueResolver
{
    private Generator $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    /**
     * @return bool|int|string|null
     */
    public function resolvePropertyValue(\ReflectionProperty $property)
    {
        $propertyType = $property->getType();
        Assert::isInstanceOf($propertyType, \ReflectionNamedType::class);

        switch ($propertyType->getName()) {
            case 'string':
                try {
                    $value = $this->faker->{$property->getName()};
                } catch (\Throwable $t) {
                    $value = $this->faker->word;
                }
                break;
            case 'int':
                $value = $this->faker->randomDigit;
                break;
            case 'bool':
                $value = $this->faker->boolean;
                break;
            case 'DateTime':
                $value = $this->faker->dateTime->format('Y-m-d H:i');
                break;
            default:
                $value = null;
        }

        return $value;
    }
}
