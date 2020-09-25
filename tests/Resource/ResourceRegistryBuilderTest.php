<?php

declare(strict_types=1);

namespace Gorynych\Tests\Resource;

use Gorynych\Resource\ResourceRegistryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;

class ResourceRegistryBuilderTest extends TestCase
{
    public function testHasResource(): void
    {
        $builder = $this->setUpBuilder();

        $this->assertTrue($builder->hasResource('TestResource'));
        $this->assertFalse($builder->hasResource('FooResource'));
    }

    public function testAppendsResource(): void
    {
        $this->assertTrue(
            $this->setUpBuilder()->appendResource('FooResource')->hasResource('FooResource')
        );
    }

    /**
     * @dataProvider provideOperationsToMerge
     * @param string[] $operationsToMerge
     * @param string[] $expectedOperations
     */
    public function testMergesOperations(array $operationsToMerge, array $expectedOperations): void
    {
        $builder = $this->setUpBuilder();
        $builder->selectResource('TestResource')->mergeOperations(...$operationsToMerge);

        $this->assertSame($expectedOperations, $builder->getRegistry()['TestResource']);
    }

    public function testThrowsBadMethodCallOnNotSelectedResource(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->setUpBuilder()->mergeOperations('FooOperation', 'BarOperations');
    }

    /**
     * @return \Generator<array>
     */
    public function provideOperationsToMerge(): \Generator
    {
        yield 'two new operations' => [
            ['FooOperation', 'BarOperation'],
            ['TestOperation', 'FooOperation', 'BarOperation'],
        ];

        yield 'one new operation and one already registered' => [
            ['TestOperation', 'FooOperation'],
            ['TestOperation', 'FooOperation'],
        ];
    }

    private function setUpBuilder(): ResourceRegistryBuilder
    {
        return new ResourceRegistryBuilder(new FileLocator(__DIR__ . '/Resource'));
    }
}
