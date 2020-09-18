<?php

declare(strict_types=1);

namespace Gorynych\Tests\Operation;

use Gorynych\Adapter\SerializerAdapter;
use Gorynych\Adapter\ValidatorAdapter;
use Gorynych\Exception\NotDeserializableException;
use Gorynych\Http\Exception\BadRequestHttpException;
use Gorynych\Http\Exception\UnsupportedMediaTypeHttpException;
use Gorynych\Operation\ControllerTrait;
use Gorynych\Resource\Exception\InvalidEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ControllerTraitTest extends TestCase
{
    /** @var SerializerAdapter|MockObject */
    private $serializerMock;
    /** @var ValidatorAdapter|MockObject */
    private $validatorMock;

    public function setUp(): void
    {
        $this->serializerMock = $this->createMock(SerializerAdapter::class);
        $this->validatorMock = $this->createMock(ValidatorAdapter::class);
    }

    public function testThrowsUnsupportedMediaType(): void
    {
        $this->expectException(UnsupportedMediaTypeHttpException::class);

        $requestMock = $this->createMock(Request::class);
        $requestMock->method('getContent')->willReturn(json_encode(['foo' => 'bar']));

        $this->serializerMock
            ->expects($this->once())
            ->method('setup')
            ->with(null)
            ->willReturn($this->serializerMock);
        $this->serializerMock
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestMock->getContent(), 'Test', 'json', [])
            ->willThrowException(new NotDeserializableException());

        $this->setUpController()->deserializeBody($requestMock, 'Test');
    }

    public function testThrowsBadRequest(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->validatorMock
            ->expects($this->once())
            ->method('setup')
            ->with('Test')
            ->willReturn($this->validatorMock);
        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with(new \stdClass())
            ->willThrowException(new InvalidEntityException());

        $this->setUpController()->validate(new \stdClass(), 'Test');
    }

    private function setUpController(): object
    {
        $controller = new class {
            use ControllerTrait {
                deserializeBody as public;
                validate as public;
            }
        };

        $controller->setSerializer($this->serializerMock);
        $controller->setValidator($this->validatorMock);

        return $controller;
    }
}
