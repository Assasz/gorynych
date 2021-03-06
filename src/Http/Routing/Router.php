<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http\Routing;

use Cake\Collection\Collection;
use Gorynych\Http\Exception\MethodNotAllowedHttpException;
use Gorynych\Http\Exception\NotFoundHttpException;
use Gorynych\Operation\ResourceOperationInterface;
use Gorynych\Resource\AbstractResource;
use Gorynych\Resource\ResourceLoader;
use Symfony\Component\HttpFoundation\Request;

final class Router
{
    private ResourceLoader $resourceLoader;
    private ?Request $request;

    public function __construct(ResourceLoader $resourceLoader)
    {
        $this->resourceLoader = $resourceLoader;
    }

    /**
     * Finds resource operation able to process given request
     *
     * @param Request $request
     * @return ResourceOperationInterface
     * @throws NotFoundHttpException if there is no proper operation mapped to any resource
     */
    public function findOperation(Request $request): ResourceOperationInterface
    {
        $this->request = $request;
        $resource = $operation = null;

        foreach ($this->resourceLoader->getResources() as $resourceClass) {
            try {
                $resource = $this->resourceLoader->loadResource($resourceClass);
                $operation = $this->filterOperationsByMethod($this->filterOperationsByUri($resource));

                break;
            } catch (NotFoundHttpException $e) {
                continue;
            }
        }

        if (!$operation instanceof ResourceOperationInterface) {
            throw new NotFoundHttpException();
        }

        $this->resolveResourceIdentity($resource, $operation);

        return $operation;
    }

    /**
     * Returns resource operations matching request URI
     *
     * @param AbstractResource $resource
     * @return Collection
     * @throws NotFoundHttpException if even one operation does not match URI pattern
     */
    private function filterOperationsByUri(AbstractResource $resource): Collection
    {
        $request = $this->request;

        $operations = $resource->getOperations()->filter(
            static function (ResourceOperationInterface $operation) use ($request, $resource): bool {
                return UriMatcher::matchUri($request->getPathInfo(), $resource->getPath(), $operation->getPath());
            }
        );

        if (true === $operations->isEmpty()) {
            throw new NotFoundHttpException();
        }

        return $operations;
    }

    /**
     * Returns resource operation matching request HTTP method
     *
     * @param Collection $operations matching request URI
     * @return ResourceOperationInterface
     * @throws MethodNotAllowedHttpException if method does not match the method of any available operations
     */
    private function filterOperationsByMethod(Collection $operations): ResourceOperationInterface
    {
        $method = $this->request->getMethod();

        $operations = $operations->filter(
            static function (ResourceOperationInterface $operation) use ($method): bool {
                return $method === $operation->getMethod();
            }
        );

        if (true === $operations->isEmpty()) {
            throw new MethodNotAllowedHttpException();
        }

        return $operations->first();
    }

    /**
     * Resolves identity of the resource
     */
    private function resolveResourceIdentity(AbstractResource $resource, ResourceOperationInterface $operation): void
    {
        UriMatcher::matchUri(
            $this->request->getPathInfo(),
            $resource->getPath(),
            $operation->getPath(),
            $matches
        );

        $resource->id = $matches['id'] ?? null;

        if (true === property_exists($resource, 'subId')) {
            $resource->subId = $matches['subId'] ?? null;
        }
    }
}
