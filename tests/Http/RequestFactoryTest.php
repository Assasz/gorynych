<?php

declare(strict_types=1);

namespace Gorynych\Tests\Http;

use Gorynych\Http\RequestFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestFactoryTest extends TestCase
{
    public function testCreatesRequest(): void
    {
        $_ENV['BASE_URI'] = 'http://localhost';

        $request = (new RequestFactory())->create(Request::METHOD_POST, '/resources', [
            RequestFactory::REQUEST_JSON => ['foo' => 'bar']
        ]);

        $this->assertSame(json_encode(['foo' => 'bar']), $request->getContent());
        $this->assertSame(Request::METHOD_POST, $request->getMethod());
        $this->assertSame('http://localhost/resources', $request->getUri());
        $this->assertSame('application/json', $request->headers->get('Accept'));
        $this->assertSame('application/json', $request->headers->get('Content-Type'));
    }
}
