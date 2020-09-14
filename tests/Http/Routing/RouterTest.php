<?php

declare(strict_types=1);

namespace Http\Routing;

use Cake\Collection\Collection;
use DG\BypassFinals;
use Gorynych\Http\Exception\MethodNotAllowedHttpException;
use Gorynych\Http\Exception\NotFoundHttpException;
use Gorynych\Http\Routing\Router;
use Gorynych\Operation\ResourceOperationInterface;
use Gorynych\Resource\AbstractResource;
use Gorynych\Resource\ResourceLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RouterTest extends TestCase
{
    /** @var ResourceLoader|MockObject  */
    private $resourceLoaderMock;

    public function setUp(): void
    {
        BypassFinals::enable();
        $this->resourceLoaderMock = $this->createMock(ResourceLoader::class);
    }

    public function testFindsOperation(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/bar/baz');
        $request->method('getMethod')->willReturn(Request::METHOD_GET);

        $operation = $this->setUpRouter()->findOperation($request);

        $this->assertSame('/baz', $operation->getPath());
        $this->assertSame(Request::METHOD_GET, $operation->getMethod());
    }

    public function testThrowsNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/bar/foo');
        $request->method('getMethod')->willReturn(Request::METHOD_GET);

        $this->setUpRouter()->findOperation($request);
    }

    public function testThrowsMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedHttpException::class);

        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/bar/baz');
        $request->method('getMethod')->willReturn(Request::METHOD_POST);

        $this->setUpRouter()->findOperation($request);
    }

    private function setUpResource(string $path): MockObject
    {
        $operation = $this->createMock(ResourceOperationInterface::class);
        $operation->method('getPath')->willReturn('/baz');
        $operation->method('getMethod')->willReturn(Request::METHOD_GET);

        $resource = $this->createMock(AbstractResource::class);
        $resource->method('getOperations')->willReturn(new Collection([$operation]));
        $resource->method('getPath')->willReturn($path);

        return $resource;
    }

    private function setUpRouter(): Router
    {
        $this->resourceLoaderMock
            ->expects($this->once())
            ->method('getResources')
            ->willReturn(['FooResource', 'BarResource']);

        $this->resourceLoaderMock
            ->expects($this->exactly(2))
            ->method('loadResource')
            ->withConsecutive(['FooResource'], ['BarResource'])
            ->willReturnOnConsecutiveCalls(
                $this->setUpResource('/foo'),
                $this->setUpResource('/bar'),
            );

        return new Router($this->resourceLoaderMock);
    }
}
