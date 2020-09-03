<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Exception;

final class KernelNotBootedException extends \RuntimeException
{
    public function __construct($message = "Please, boot kernel before this operation.")
    {
        parent::__construct($message);
    }
}
