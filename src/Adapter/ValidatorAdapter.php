<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Adapter;

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
        /** @var ConstraintViolationInterface[] $errors */
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            /** @var string[] $errors */
            $errors = array_map(
                static function (ConstraintViolationInterface $violation): array {
                    return [
                        'property' => $violation->getPropertyPath(),
                        'message' => $violation->getMessage()
                    ];
                },
                $errors
            );

            throw new InvalidEntityException($errors);
        }
    }

    /**
     * Setups validator
     *
     * @param string $constraint Constraint name to validate against
     * @return mixed
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
