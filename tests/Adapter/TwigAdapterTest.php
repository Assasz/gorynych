<?php

declare(strict_types=1);

namespace Gorynych\Tests\Adapter;

use Gorynych\Adapter\TwigAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class TwigAdapterTest extends TestCase
{
    /** @var Environment|MockObject */
    private $twigMock;

    public function setUp(): void
    {
        $this->twigMock = $this->createMock(Environment::class);
    }

    public function testRendersTemplate(): void
    {
        $this->twigMock
            ->expects($this->once())
            ->method('render')
            ->with('foo.twig.html', ['foo' => 'bar'])
            ->willReturn('foo');

        $this->setUpTwig()->render('foo.twig.html', ['foo' => 'bar']);
    }

    private function setUpTwig(): TwigAdapter
    {
        $reflection = new \ReflectionClass(TwigAdapter::class);
        $twigAdapter = $reflection->newInstanceWithoutConstructor();

        $reflectionProperty = $reflection->getProperty('twig');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($twigAdapter, $this->twigMock);

        return $twigAdapter;
    }
}
