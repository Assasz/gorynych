<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Util;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CommandLoader
{
    public const COMMAND_TAG = 'console.command';

    /**
     * Loads tagged command services into CLI application
     *
     * @param Application $cliApp
     * @param ContainerBuilder $container
     */
    public function load(Application $cliApp, ContainerBuilder $container): void
    {
        $commandServices = $container->findTaggedServiceIds(self::COMMAND_TAG);

        foreach ($commandServices as $commandId => $tags) {
            /** @var Command $command */
            $command = $container->get($commandId);
            $cliApp->add($command);
        }
    }
}
