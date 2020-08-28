<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Util;

use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

final class Debug
{
    /**
     * Enables debug mode for web environment (only for non-production)
     *
     * @param HandlerInterface|null $handler custom error handler
     */
    public static function web(HandlerInterface $handler = null): void
    {
        if ('prod' === EnvAccess::get('APP_ENV', 'dev')) {
            error_reporting(0);
            ini_set('display_errors', '0');

            return;
        }

        (new Run())->pushHandler($handler ?? new PrettyPageHandler())->register();
    }

    /**
     * Enables debug mode for CLI environment
     */
    public static function cli(): void
    {
        (new Run())->pushHandler(new PlainTextHandler())->register();
    }
}
