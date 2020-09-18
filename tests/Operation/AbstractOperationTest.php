<?php

declare(strict_types=1);

namespace Gorynych\Tests\Operation;

use Gorynych\Adapter\SerializerAdapter;
use Gorynych\Adapter\ValidatorAdapter;
use Gorynych\Operation\AbstractOperation;
use Gorynych\Tests\Operation\Resource\TestInput;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class AbstractOperationTest extends TestCase
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

    public function testHandlesRequest(): void
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock->method('getContentType')->willReturn('json');
        $requestMock->method('getContent')->willReturn(json_encode(['foo' => 'bar']));

        $this->serializerMock
            ->expects($this->exactly(2))
            ->method('setup')
            ->with(null)
            ->willReturn($this->serializerMock);
        $this->serializerMock
            ->expects($this->once())
            ->method('canDeserialize')
            ->with(TestInput::class)
            ->willReturn(true);
        $this->serializerMock
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestMock->getContent(), TestInput::class, 'json', [])
            ->willReturn(new TestInput('bar'));

        $this->validatorMock
            ->expects($this->once())
            ->method('setup')
            ->with('TestInput')
            ->willReturn($this->validatorMock);
        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with(new TestInput('bar'));

        $this->serializerMock
            ->expects($this->once())
            ->method('canNormalize')
            ->with(new \stdClass())
            ->willReturn(true);
        $this->serializerMock
            ->expects($this->once())
            ->method('normalize')
            ->with(new \stdClass(), [])
            ->willReturn(['foo' => 'bar']);

        $output = $this->setUpOperation()->handle($requestMock);

        $this->assertIsArray($output);
        $this->assertSame($output['foo'], 'bar');
    }

    private function setUpOperation(): AbstractOperation
    {
        $operation = new class extends AbstractOperation {
            public function getMethod(): string
            {
                return AbstractOperation::GET_METHOD;
            }

            public function getPath(): string
            {
                return '/';
            }

            public function __invoke(TestInput $input): object
            {
                return new \stdClass();
            }
        };

        $operation->setSerializer($this->serializerMock);
        $operation->setValidator($this->validatorMock);

        return $operation;
    }
}
