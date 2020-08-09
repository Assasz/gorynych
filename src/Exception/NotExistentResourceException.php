<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Exception;

final class NotExistentResourceException extends \RuntimeException
{
    public function __construct(string $resource, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct("Resource {$resource} not found.", $code, $previous);
    }
}
