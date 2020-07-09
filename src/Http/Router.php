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
        $operation = null;

        foreach ($this->resourceLoader->getResources() as $resourceClass) {
            try {
                $resource = $this->resourceLoader->loadResource($resourceClass);
                $operation = $this
                    ->filterOperationsByMethod(
                        $this->filterOperationsByUri($resource, $request->getPathInfo()),
                        $request->getMethod()
                    )
                    ->setResource($resource);

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
     * Returns resource operations matching given URI
     *
     * @param AbstractResource $resource
     * @param string $uri
     * @return Collection
     * @throws NotFoundHttpException if even one operation does not match URI pattern
     */
    private function filterOperationsByUri(AbstractResource $resource, string $uri): Collection
    {
        $operations = (new Collection($resource->getOperations()))->filter(
            static function (ResourceOperationInterface $operation) use ($uri, $resource) {
                $resourcePath = self::normalize($resource->getPath());
                $operationPath = self::normalize($operation->getPath());

                if (true === $isMatch = (bool)preg_match("#^{$resourcePath}{$operationPath}$#", self::normalize($uri), $matches)) {
                    $resource->id = $matches['id'] ?? null;
                }

                return $isMatch;
            }
        );

        if (true === $operations->isEmpty()) {
            throw new NotFoundHttpException();
        }

        return $operations;
    }

    /**
     * Returns resource operation matching given HTTP method
     *
     * @param Collection $operations matching request URI
     * @param string $method
     * @return ResourceOperationInterface
     * @throws MethodNotAllowedHttpException if method does not match the method of any available operations
     */
    private function filterOperationsByMethod(Collection $operations, string $method): ResourceOperationInterface
    {
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
     * @param string $path
     * @return string
     */
    private static function normalize(string $path): string
    {
        return rtrim($path, '/');
    }
}
