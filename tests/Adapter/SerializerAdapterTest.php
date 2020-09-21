<?php

declare(strict_types=1);

namespace Gorynych\Tests\Adapter;

use Gorynych\Adapter\SerializerAdapter;
use Gorynych\Exception\NotDeserializableException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerAdapterTest extends TestCase
{
    /** @var SerializerInterface|MockObject  */
    private $serializerMock;

    public function setUp(): void
    {
        $this->serializerMock = $this->createMock(SerializerInterface::class);
    }

    public function testThrowsNotDeserializable(): void
    {
        $this->expectException(NotDeserializableException::class);

        $this->serializerMock
            ->expects($this->once())
            ->method('deserialize')
            ->with('foo', self::class, 'json')
            ->willThrowException(new NotEncodableValueException());

        $this->setUpAdapter()->deserialize('foo', self::class, 'json');
    }

    /**
     * @dataProvider provideNormalizationCases
     * @param mixed $data
     */
    public function testCanNormalize($data, bool $expectedResult): void
    {
        $this->assertSame($expectedResult, $this->setUpAdapter()->canNormalize($data));
    }

    /**
     * @return \Generator<array>
     */
    public function provideNormalizationCases(): \Generator
    {
        yield 'object' => [new \stdClass(), true];

        yield 'array of objects' => [[new \stdClass()], true];

        yield 'scalar' => ['foo', false];

        yield 'empty array' => [[], false];
    }

    /**
     * @dataProvider provideDeserializationCases
     */
    public function testCanDeserialize(string $type, bool $expectedResult): void
    {
        $this->assertSame($expectedResult, $this->setUpAdapter()->canDeserialize($type));
    }

    /**
     * @return \Generator<array>
     */
    public function provideDeserializationCases(): \Generator
    {
        yield 'existing class' => [self::class, true];

        yield 'non existent class' => ['Test', false];

        yield 'request' => [Request::class, false];
    }

    private function setUpAdapter(): SerializerAdapter
    {
        $reflection = new \ReflectionClass(SerializerAdapter::class);
        $serializerAdapter = $reflection->newInstanceWithoutConstructor();

        $reflectionProperty = $reflection->getProperty('serializer');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($serializerAdapter, $this->serializerMock);

        return $serializerAdapter;
    }
}
