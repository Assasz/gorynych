<?php

declare(strict_types=1);

namespace Gorynych\Tests\Util;

use Gorynych\Util\CommandLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CommandLoaderTest extends TestCase
{
    public function testLoadsCommands(): void
    {
        $containerMock = $this->createMock(ContainerBuilder::class);
        $cliMock = $this->createMock(Application::class);
        $commandMock = $this->createMock(Command::class);

        $containerMock
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with(CommandLoader::COMMAND_TAG)
            ->willReturn([1 => CommandLoader::COMMAND_TAG]);
        $containerMock
            ->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($commandMock);

        $cliMock->expects($this->once())->method('add')->with($commandMock);

        (new CommandLoader())->load($cliMock, $containerMock);
    }
}
