<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Adapter;

use Cake\Collection\Collection;
use Gorynych\Resource\Exception\InvalidEntityException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorAdapter
{
    protected ValidatorInterface $validator;
    protected FileLocatorInterface $configLocator;

    public function __construct(FileLocatorInterface $configLocator)
    {
        $this->configLocator = $configLocator;
    }

    /**
     * Validates provided entity object
     *
     * @param object $entity
     * @throws InvalidEntityException if entity is not valid
     */
    public function validate(object $entity): void
    {
        $errors = $this->validator->validate($entity);

        if ($errors->count() > 0) {
            $errors = (new Collection($errors))->map(
                static function (ConstraintViolationInterface $violation): array {
                    return [
                        'property' => $violation->getPropertyPath(),
                        'message' => $violation->getMessage(),
                    ];
                }
            );

            throw InvalidEntityException::fromArray($errors->toArray());
        }
    }

    /**
     * Setups validator
     *
     * @param string $constraint Constraint name to validate against
     * @return self
     */
    public function setup(string $constraint): self
    {
        $constraintPath = $this->configLocator->locate("validator/{$constraint}.yaml");

        $this->validator = Validation::createValidatorBuilder()
            ->addYamlMapping($constraintPath)
            ->getValidator();

        return $this;
    }
}
