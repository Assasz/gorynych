<?php

declare(strict_types=1);

namespace Gorynych\Tests\Http;

use Gorynych\Http\Kernel;
use Gorynych\Http\KernelClient;
use Gorynych\Http\RequestFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KernelClientTest extends TestCase
{
    /** @var Kernel|MockObject */
    private $kernelMock;
    /** @var RequestFactoryInterface|MockObject */
    private $requestFactoryMock;

    public function setUp(): void
    {
        $this->kernelMock = $this->createMock(Kernel::class);
        $this->requestFactoryMock = $this->createMock(RequestFactoryInterface::class);
    }

    public function testPerformsRequestOnKernel(): void
    {
        $request = Request::create('/resources', Request::METHOD_GET);
        $responseExpected = new Response('test_content');

        $this->requestFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(Request::METHOD_GET, '/resources')
            ->willReturn($request);

        $this->kernelMock
            ->expects($this->once())
            ->method('reboot')
            ->willReturn($this->kernelMock);
        $this->kernelMock
            ->expects($this->once())
            ->method('handleRequest')
            ->with($request)
            ->willReturn($responseExpected);

        $response = $this->setUpKernelClient()->request(Request::METHOD_GET, '/resources');

        $this->assertSame($responseExpected->getContent(), $response->getContent());
    }

    private function setUpKernelClient(): KernelClient
    {
        return new KernelClient($this->kernelMock, $this->requestFactoryMock);
    }
}
