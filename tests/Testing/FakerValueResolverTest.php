<?php

declare(strict_types=1);

namespace Gorynych\Tests\Testing;

use Faker\Factory;
use Gorynych\Testing\FakerValueResolver;
use Gorynych\Tests\Testing\Resource\TestObject;
use PHPUnit\Framework\TestCase;

class FakerValueResolverTest extends TestCase
{
    /**
     * @dataProvider provideProperties
     */
    public function testResolvesPropertyValue(\ReflectionProperty $reflectionProperty, string $expectedType): void
    {
        $value = (new FakerValueResolver(Factory::create()))->resolvePropertyValue($reflectionProperty);

        $assertionName = 'assertIs' . ucfirst($expectedType);
        $this->{$assertionName}($value);
    }

    /**
     * @return \Generator<array>
     */
    public function provideProperties(): \Generator
    {
        yield 'string' => [
            new \ReflectionProperty(TestObject::class, 'foo'),
            'string',
        ];

        yield 'int' => [
            new \ReflectionProperty(TestObject::class, 'bar'),
            'int',
        ];

        yield 'bool' => [
            new \ReflectionProperty(TestObject::class, 'baz'),
            'bool',
        ];

        yield 'datetime' => [
            new \ReflectionProperty(TestObject::class, 'date'),
            'string',
        ];
    }
}
