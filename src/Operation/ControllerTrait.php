<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Operation;

use Gorynych\Adapter\SerializerAdapterInterface;
use Gorynych\Adapter\ValidatorAdapterInterface;
use Gorynych\Resource\Exception\InvalidEntityException;
use Gorynych\Http\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;

trait ControllerTrait
{
    protected SerializerAdapterInterface $serializer;
    protected ValidatorAdapterInterface $validator;

    /**
     * @required
     * @param SerializerAdapterInterface $serializer
     */
    public function setSerializer(SerializerAdapterInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * @required
     * @param ValidatorAdapterInterface $validator
     */
    public function setValidator(ValidatorAdapterInterface $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * Returns deserialized request body in form of entity object
     *
     * @param Request $request
     * @param string $entityClass
     * @param string|null $definition
     * @param array $context
     * @param string $format
     * @return object
     */
    protected function deserializeBody(Request $request, string $entityClass, string $definition = null, array $context = [], string $format = 'json'): object
    {
        return $this->serializer->setup($definition)->deserialize($request->getContent(), $entityClass, $format, $context);
    }

    /**
     * Returns normalized representation of the resource
     *
     * @param object|array $resource
     * @param string|null $definition
     * @param array $context
     * @return array
     */
    protected function normalizeResource($resource, string $definition = null, array $context = []): array
    {
        return $this->serializer->setup($definition)->normalize($resource, $context);
    }

    /**
     * Validates given entity object against specified constraint
     *
     * @param object $entity
     * @param string $constraint
     * @throws BadRequestHttpException
     */
    protected function validate(object $entity, string $constraint): void
    {
        try {
            $this->validator->setup($constraint)->validate($entity);
        } catch (InvalidEntityException $e) {
            throw new BadRequestHttpException($e->getErrors());
        }
    }
}
