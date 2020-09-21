<?php

declare(strict_types=1);

namespace Adapter;

use Gorynych\Adapter\ValidatorAdapter;
use Gorynych\Resource\Exception\InvalidEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorAdapterTest extends TestCase
{
    /** @var ValidatorInterface|MockObject  */
    private $validatorMock;

    public function setUp(): void
    {
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
    }

    public function testThrowsInvalidEntityOnFailure(): void
    {
        $this->expectException(InvalidEntityException::class);

        $constraintViolation = $this->createMock(ConstraintViolationInterface::class);
        $constraintViolation->method('getPropertyPath')->willReturn('foo');
        $constraintViolation->method('getMessage')->willReturn('bar');

        $constraintViolationList = new ConstraintViolationList([$constraintViolation]);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willReturn($constraintViolationList);

        $this->setUpAdapter()->validate(new \stdClass());
    }

    private function setUpAdapter(): ValidatorAdapter
    {
        $reflection = new \ReflectionClass(ValidatorAdapter::class);
        $validatorAdapter = $reflection->newInstanceWithoutConstructor();

        $reflectionProperty = $reflection->getProperty('validator');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($validatorAdapter, $this->validatorMock);

        return $validatorAdapter;
    }
}
