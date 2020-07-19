<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource\Exception;

use Throwable;

class InvalidEntityException extends \LogicException
{
    /** @var string[]  */
    private array $errors;

    /**
     * @param string[] $errors
     */
    public function __construct(array $errors = [], $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
