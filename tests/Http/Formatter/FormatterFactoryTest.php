<?php

declare(strict_types=1);

namespace Gorynych\Tests\Http\Formatter;

use Gorynych\Http\Exception\NotAcceptableHttpException;
use Gorynych\Http\Formatter\FormatterFactory;
use Gorynych\Http\Formatter\JsonFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;

class FormatterFactoryTest extends TestCase
{
    public function testCreatesFormatter(): void
    {
        $formatter = $this->setUpFormatterFactory()->create('application/json');

        $this->assertInstanceOf(JsonFormatter::class, $formatter);
    }

    public function testThrowsExceptionOnNotAcceptableFormat(): void
    {
        $this->expectException(NotAcceptableHttpException::class);

        $this->setUpFormatterFactory()->create('application/xml');
    }

    private function setUpFormatterFactory(): FormatterFactory
    {
        return new FormatterFactory(new FileLocator(__DIR__ . '/Resource'));
    }
}
