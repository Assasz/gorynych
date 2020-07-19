<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Util;

use Gorynych\Exception\MissingEnvVariableException;

final class EnvAccess
{
    /**
     * Returns environmental variable by name
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @throws MissingEnvVariableException
     */
    public static function get(string $name, $default = null)
    {
        if (null === $default && false === array_key_exists($name, $_ENV)) {
            throw new MissingEnvVariableException($name);
        }

        return $_ENV[$name] ?? $default;
    }
}
