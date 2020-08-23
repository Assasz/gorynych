<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Operation;

use Gorynych\Resource\AbstractResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

abstract class AbstractOperation implements ResourceOperationInterface
{
    use ControllerTrait;

    public const GET_METHOD = 'GET';
    public const POST_METHOD = 'POST';
    public const PUT_METHOD = 'PUT';
    public const PATCH_METHOD = 'PATCH';
    public const DELETE_METHOD = 'DELETE';

    /** @var AbstractResource */
    protected $resource;

    /**
     * {@inheritdoc}
     */
    public function setResource(AbstractResource $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseStatus(): int
    {
        return Response::HTTP_OK;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request)
    {
        Assert::isCallable($this);

        $invoke = new \ReflectionMethod($this, '__invoke');
        $argumentType = empty($invoke->getParameters()) ?
            Request::class : current($invoke->getParameters())->getType()->getName();

        if ($this->isDeserializationNeeded($argumentType)) {
            $input = $this->deserializeBody($request, $argumentType, null, [], $request->getContentType());
            $this->validate($input, (new \ReflectionClass($input))->getShortName());
        }

        /** @var callable|AbstractOperation $this */
        $output = $this($input ?? $request);

        return $this->isNormalizationNeeded($output) ? $this->normalizeResource($output) : $output;
    }

    private function isDeserializationNeeded(string $argumentType): bool
    {
        return Request::class !== $argumentType && class_exists($argumentType);
    }

    /**
     * @param mixed $resource
     */
    private function isNormalizationNeeded($resource): bool
    {
        return (
            is_object($resource) ||
            (is_array($resource) && !empty($resource) && is_object(current($resource)))
        );
    }
}
