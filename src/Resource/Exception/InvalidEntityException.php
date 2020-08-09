<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource\Exception;

use Gorynych\Resource\Dto\EntityViolation;

class InvalidEntityException extends \LogicException
{
    /** @var EntityViolation[]  */
    private array $errors;

    /**
     * @return EntityViolation[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function fromViolations(EntityViolation ...$violations): self
    {
        $message = array_map(
            static function (EntityViolation $violation): string {
                return "{$violation->getProperty()}: {$violation->getMessage()}";
            },
            $violations
        );

        $self = new self(implode('; ', $message));
        $self->errors = $violations;

        return $self;
    }
}
