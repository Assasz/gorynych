<?php

declare(strict_types=1);

namespace Gorynych\Tests\Resource;

use Gorynych\Exception\NotExistentResourceException;
use Gorynych\Operation\ResourceOperationInterface;
use Gorynych\Resource\AbstractResource;
use Gorynych\Resource\ResourceLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceLoaderTest extends TestCase
{
    /** @var ContainerInterface|MockObject */
    private $containerMock;

    public function setUp(): void
    {
        $this->containerMock = $this->createMock(ContainerInterface::class);
    }

    public function testLoadsResource(): void
    {
        $operationMock = $this->createMock(ResourceOperationInterface::class);

        $resourceMock = $this->createMock(AbstractResource::class);
        $resourceMock->expects($this->once())->method('addOperation')->with($operationMock);

        $this->containerMock
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['TestResource'], ['TestOperation'])
            ->willReturnOnConsecutiveCalls($resourceMock, $operationMock);

        $this->setUpLoader()->loadResource('TestResource');
    }

    public function testThrowsNotExistentResource(): void
    {
        $this->expectException(NotExistentResourceException::class);

        $this->setUpLoader()->loadResource('NotExistentResource');
    }

    private function setUpLoader(): ResourceLoader
    {
        return new ResourceLoader($this->containerMock, new FileLocator(__DIR__ . '/Resource'));
    }
}
