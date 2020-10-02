<?php

declare(strict_types=1);

namespace Gorynych\Tests\Testing;

use Gorynych\Testing\EntityMock;
use Gorynych\Tests\Testing\Resource\TestObject;
use PHPUnit\Framework\TestCase;

class EntityMockTest extends TestCase
{
    public function testCreatesEntityMock(): void
    {
        $entityMock = EntityMock::create(TestObject::class);

        /** @phpstan-ignore-next-line */
        $this->assertIsString($entityMock->foo);
        /** @phpstan-ignore-next-line */
        $this->assertIsInt($entityMock->bar);
        /** @phpstan-ignore-next-line */
        $this->assertIsBool($entityMock->baz);
        /** @phpstan-ignore-next-line */
        $this->assertIsString($entityMock->date);
    }
}
