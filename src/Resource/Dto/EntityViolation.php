<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource\Dto;

final class EntityViolation
{
    private string $property;
    private string $message;

    public function __construct(string $property, string $message)
    {
        $this->property = $property;
        $this->message = $message;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
