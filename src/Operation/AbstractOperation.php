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

        if ($this->serializer->canDeserialize($argumentType)) {
            $input = $this->deserializeBody(
                $request,
                $argumentType,
                $this->getDeserializationContext()['definition'],
                $this->getDeserializationContext()['context'],
                $request->getContentType()
            );

            $this->validate($input, (new \ReflectionClass($input))->getShortName());
        }

        /** @var callable|AbstractOperation $this */
        $output = $this($input ?? $request);

        return $this->serializer->canNormalize($output) ?
            $this->normalizeResource(
                $output,
                $this->getNormalizationContext()['definition'],
                $this->getNormalizationContext()['context']
            ) :
            $output;
    }

    /**
     * @return array{definition: ?string, context: array}
     */
    protected function getDeserializationContext(): array
    {
        return [
            'definition' => null,
            'context' => [],
        ];
    }

    /**
     * @return array{definition: ?string, context: array}
     */
    protected function getNormalizationContext(): array
    {
        return [
            'definition' => null,
            'context' => [],
        ];
    }
}
