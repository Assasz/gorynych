<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Operation;

use Gorynych\Adapter\SerializerAdapter;
use Gorynych\Adapter\ValidatorAdapter;
use Gorynych\Exception\NotDeserializableException;
use Gorynych\Http\Exception\UnsupportedMediaTypeHttpException;
use Gorynych\Resource\Exception\InvalidEntityException;
use Gorynych\Http\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;

trait ControllerTrait
{
    protected SerializerAdapter $serializer;
    protected ValidatorAdapter $validator;

    /**
     * @required
     * @param SerializerAdapter $serializer
     */
    public function setSerializer(SerializerAdapter $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * @required
     * @param ValidatorAdapter $validator
     */
    public function setValidator(ValidatorAdapter $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * Returns deserialized request body in form of entity object
     *
     * @param Request $request
     * @param string $entityClass
     * @param string|null $definition
     * @param mixed[] $context
     * @param string $format
     * @return object
     *
     * @throws UnsupportedMediaTypeHttpException
     */
    protected function deserializeBody(Request $request, string $entityClass, string $definition = null, array $context = [], string $format = 'json'): object
    {
        try {
            return $this->serializer->setup($definition)->deserialize($request->getContent(), $entityClass, $format, $context);
        } catch (NotDeserializableException $e) {
            throw new UnsupportedMediaTypeHttpException();
        }
    }

    /**
     * Returns normalized representation of the resource
     *
     * @param object|object[] $resource
     * @param string|null $definition
     * @param mixed[] $context
     * @return mixed[]
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
     *
     * @throws BadRequestHttpException
     */
    protected function validate(object $entity, string $constraint): void
    {
        try {
            $this->validator->setup($constraint)->validate($entity);
        } catch (InvalidEntityException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
