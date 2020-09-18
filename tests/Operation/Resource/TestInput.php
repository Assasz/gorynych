<?php

declare(strict_types=1);

namespace Gorynych\Tests\Operation\Resource;

final class TestInput
{
    public string $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }
}
