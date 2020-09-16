<?php

declare(strict_types=1);

namespace Gorynych\Tests\Util;

use Gorynych\Exception\MissingEnvVariableException;
use Gorynych\Util\EnvAccess;
use PHPUnit\Framework\TestCase;

class EnvAccessTest extends TestCase
{
    public function testReturnsEnv(): void
    {
        $_ENV['TEST'] = 'foo';

        $this->assertSame($_ENV['TEST'], EnvAccess::get('TEST'));
    }

    public function testReturnsEnvDefault(): void
    {
        $this->assertSame('foo', EnvAccess::get('TEST_DEFAULT', 'foo'));
    }

    public function testThrowsMissingEnvException(): void
    {
        $this->expectException(MissingEnvVariableException::class);

        EnvAccess::get('NOT_EXISTENT');
    }
}
