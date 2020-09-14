<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

namespace Gorynych\Resource;

use Gorynych\Exception\NotExistentResourceException;
use Gorynych\Operation\ResourceOperationInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

final class ResourceLoader implements ResourceLoaderInterface
{
    private ContainerInterface $container;
    /** @var string[][] */
    private array $resourceRegistry;

    public function __construct(ContainerInterface $container, FileLocatorInterface $fileLocator)
    {
        $this->container = $container;
        $this->resourceRegistry = $this->parseResourceRegistry($fileLocator->locate('resources.yaml'));
    }

    /**
     * {@inheritdoc}
     */
    public function getResources(): array
    {
        return array_keys($this->resourceRegistry);
    }

    /**
     * {@inheritdoc}
     */
    public function loadResource(string $resourceClass): AbstractResource
    {
        if (false === array_key_exists($resourceClass, $this->resourceRegistry)) {
            throw new NotExistentResourceException($resourceClass);
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
     * @return string[][]
     */
    private function parseResourceRegistry(string $file): array
    {
        return Yaml::parseFile($file)['resources'];
    }
}
