<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource\Exception;

class InvalidEntityException extends \LogicException
{
    /** @var string[][]  */
    private array $errors;

    /**
     * @return string[][]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string[][] $errors
     */
    public static function fromArray(array $errors): self
    {
        $message = array_map(
            static function (array $error): string {
                return "{$error['property']}: {$error['message']}";
            },
            $errors
        );

        $self = new self(implode('; ', $message));
        $self->errors = $errors;

        return $self;
    }
}
