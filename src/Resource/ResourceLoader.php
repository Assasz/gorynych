<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

namespace Gorynych\Resource;

use Gorynych\Operation\ResourceOperationInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

final class ResourceLoader
{
    private ContainerInterface $container;
    /** @var string[][] */
    private array $resourceRegistry;

    /**
     * @param ContainerInterface $container
     * @param FileLocatorInterface $fileLocator
     */
    public function __construct(ContainerInterface $container, FileLocatorInterface $fileLocator)
    {
        $this->container = $container;
        $this->resourceRegistry = $this->parseResourceRegistry($fileLocator->locate('resources.yaml'));
    }

    /**
     * @return string[]
     */
    public function getResources(): array
    {
        return array_keys($this->resourceRegistry);
    }

    /**
     * Loads given resource class
     *
     * @param string $resourceClass
     * @return AbstractResource
     * @throws \RuntimeException
     */
    public function loadResource(string $resourceClass): AbstractResource
    {
        if (false === array_key_exists($resourceClass, $this->resourceRegistry)) {
            throw new \RuntimeException("Non existent resource {$resourceClass}.");
        }

        /** @var AbstractResource $resource */
        $resource = $this->container->get($resourceClass);

        foreach ($this->resourceRegistry[$resourceClass] as $operationClass) {
            /** @var ResourceOperationInterface $operation */
            $operation = $this->container->get($operationClass);
            $resource->addOperation($operation);
        }

        return $resource;
    }

    /**
     * @param string $file
     * @return string[][]
     */
    private function parseResourceRegistry(string $file): array
    {
        return Yaml::parseFile($file)['resources'];
    }
}
