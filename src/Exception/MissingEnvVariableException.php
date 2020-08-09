<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Exception;

final class MissingEnvVariableException extends \RuntimeException
{
    public function __construct(string $variable, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct("Please make sure that {$variable} environmental variable is defined.", $code, $previous);
    }
}
