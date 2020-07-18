<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http;

use Cake\Collection\Collection;
use Gorynych\Resource\AbstractResource;
use Gorynych\Http\Exception\MethodNotAllowedHttpException;
use Gorynych\Http\Exception\NotFoundHttpException;
use Gorynych\Operation\ResourceOperationInterface;
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
        $operation = null;

        foreach ($this->resourceLoader->getResources() as $resourceClass) {
            try {
                $resource = $this->resourceLoader->loadResource($resourceClass);
                $operation = $this->filterOperationsByMethod($this->filterOperationsByUri($resource));

                $this->matchUri($resource->getPath(), $operation->getPath(), $matches);
                $resource->id = $matches['id'] ?? null;

                break;
            } catch (NotFoundHttpException $e) {
                continue;
            }
        }

        if (!$operation instanceof ResourceOperationInterface) {
            throw new NotFoundHttpException();
        }

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
        $self = $this;

        $operations = (new Collection($resource->getOperations()))->filter(
            static function (ResourceOperationInterface $operation) use ($self, $resource) {
                return $self->matchUri($resource->getPath(), $operation->getPath());
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
            static function (ResourceOperationInterface $operation) use ($method) {
                return $method === $operation->getMethod();
            }
        );

        if (true === $operations->isEmpty()) {
            throw new MethodNotAllowedHttpException();
        }

        return $operations->first();
    }

    /**
     * Returns TRUE if provided paths combination matches request URI
     *
     * @param string $resourcePath
     * @param string $operationPath
     * @param mixed[]|null $matches
     * @return bool
     */
    private function matchUri(string $resourcePath, string $operationPath, &$matches = null): bool
    {
        $resourcePath = self::normalizeUri($resourcePath);
        $operationPath = self::normalizeUri($operationPath);
        $uri = self::normalizeUri($this->request->getPathInfo());

        return (bool) preg_match("#^{$resourcePath}{$operationPath}$#", $uri, $matches);
    }

    /**
     * @param string $path
     * @return string
     */
    private static function normalizeUri(string $path): string
    {
        return rtrim($path, '/');
    }
}
