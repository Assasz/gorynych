<?php

declare(strict_types=1);

namespace Gorynych\Tests\Resource\Exception;

use Gorynych\Resource\Dto\EntityViolation;
use Gorynych\Resource\Exception\InvalidEntityException;
use PHPUnit\Framework\TestCase;

class InvalidEntityExceptionTest extends TestCase
{
    public function testCreatesFromViolations(): void
    {
        $exception = InvalidEntityException::fromViolations(
            new EntityViolation('foo', 'bar'),
            new EntityViolation('baz', 'bar'),
        );

        $this->assertSame('foo: bar; baz: bar', $exception->getMessage());
        $this->assertCount(2, $exception->getErrors());
    }
}
