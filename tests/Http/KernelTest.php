<?php

declare(strict_types=1);

namespace Gorynych\Tests\Http;

use DG\BypassFinals;
use Gorynych\Http\Dto\ProblemDetails;
use Gorynych\Http\Exception\NotAcceptableHttpException;
use Gorynych\Http\Formatter\FormatterFactory;
use Gorynych\Http\Formatter\FormatterInterface;
use Gorynych\Http\Kernel;
use Gorynych\Http\Routing\Router;
use Gorynych\Operation\ResourceOperationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

BypassFinals::enable();

class KernelTest extends TestCase
{
    /** @var ResourceOperationInterface|MockObject */
    private $operationMock;
    /** @var FormatterInterface|MockObject */
    private $formatterMock;
    /** @var FormatterFactory|MockObject */
    private $formatterFactoryMock;
    /** @var Router|MockObject */
    private $routerMock;

    public function setUp(): void
    {
        $this->operationMock = $this->createMock(ResourceOperationInterface::class);
        $this->formatterMock = $this->createMock(FormatterInterface::class);
        $this->formatterFactoryMock = $this->createMock(FormatterFactory::class);
        $this->routerMock = $this->createMock(Router::class);
    }

    public function testHandlesRequest(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getAcceptableContentTypes')->willReturn(['application/json']);

        $responseExpected = new JsonResponse('foo', Response::HTTP_OK);

        $this->operationMock
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn(['foo']);
        $this->operationMock
            ->expects($this->once())
            ->method('getResponseStatus')
            ->willReturn(Response::HTTP_OK);

        $this->formatterMock
            ->expects($this->once())
            ->method('format')
            ->with(['foo'], Response::HTTP_OK)
            ->willReturn($responseExpected);

        $this->routerMock
            ->expects($this->once())
            ->method('findOperation')
            ->with($request)
            ->willReturn($this->operationMock);

        $this->formatterFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(...$request->getAcceptableContentTypes())
            ->willReturn($this->formatterMock);

        $response = $this->setUpKernel()->boot()->handleRequest($request);

        $this->assertSame($responseExpected->getContent(), $response->getContent());
        $this->assertSame($responseExpected->getStatusCode(), $response->getStatusCode());
    }

    public function testThrowsExceptionInDevEnvironment(): void
    {
        $this->expectException(\RuntimeException::class);

        $request = $this->createMock(Request::class);
        $request->method('getAcceptableContentTypes')->willReturn(['application/json']);

        $this->operationMock
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willThrowException(new \RuntimeException());

        $this->routerMock
            ->expects($this->once())
            ->method('findOperation')
            ->with($request)
            ->willReturn($this->operationMock);

        $this->formatterFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(...$request->getAcceptableContentTypes())
            ->willReturn($this->formatterMock);

        $this->setUpKernel()->boot('dev')->handleRequest($request);
    }

    public function testReturnsProblemDetailsInProdEnvironment(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getAcceptableContentTypes')->willReturn(['application/json']);

        $exception = new \RuntimeException('test');
        $problemDetails = ProblemDetails::fromThrowable($exception);
        $responseExpected = new JsonResponse($problemDetails, Response::HTTP_INTERNAL_SERVER_ERROR);

        $this->operationMock
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willThrowException($exception);

        $this->formatterMock
            ->expects($this->once())
            ->method('format')
            ->with($problemDetails, Response::HTTP_INTERNAL_SERVER_ERROR)
            ->willReturn($responseExpected);

        $this->routerMock
            ->expects($this->once())
            ->method('findOperation')
            ->with($request)
            ->willReturn($this->operationMock);

        $this->formatterFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(...$request->getAcceptableContentTypes())
            ->willReturn($this->formatterMock);

        $response = $this->setUpKernel()->boot('prod')->handleRequest($request);

        $this->assertSame($responseExpected->getContent(), $response->getContent());
        $this->assertSame($responseExpected->getStatusCode(), $response->getStatusCode());
    }

    public function testReturnsNotAcceptableResponse(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getAcceptableContentTypes')->willReturn(['application/json']);

        $responseExpected = new Response('test', Response::HTTP_NOT_ACCEPTABLE);

        $this->formatterFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(...$request->getAcceptableContentTypes())
            ->willThrowException(new NotAcceptableHttpException('test'));

        $response = $this->setUpKernel()->boot()->handleRequest($request);

        $this->assertSame($responseExpected->getContent(), $response->getContent());
        $this->assertSame($responseExpected->getStatusCode(), $response->getStatusCode());
    }

    /**
     * @return Kernel|MockObject
     */
    private function setUpKernel()
    {
        /** @var Kernel|MockObject $kernel */
        $kernel = $this->getMockBuilder(Kernel::class)
            ->onlyMethods(['getRouter', 'getFormatterFactory', 'initializeContainer', 'getConfigLocator', 'loadConfiguration'])
            ->getMock();

        $kernel->expects($this->once())->method('initializeContainer');
        $kernel->expects($this->any())->method('getFormatterFactory')->willReturn($this->formatterFactoryMock);
        $kernel->expects($this->any())->method('getRouter')->willReturn($this->routerMock);

        return $kernel;
    }
}
