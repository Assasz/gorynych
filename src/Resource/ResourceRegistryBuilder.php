<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Yaml\Yaml;

final class ResourceRegistryBuilder
{
    private FileLocatorInterface $configLocator;
    /** @var string[][][] */
    private array $registry;
    private ?string $selectedResource;

    public function __construct(FileLocatorInterface $configLocator)
    {
        $this->configLocator = $configLocator;
        $this->load();
    }

    /**
     * Loads resources registry
     *
     * @return $this
     */
    public function load(): self
    {
        $this->registry = Yaml::parse(file_get_contents($this->configLocator->locate('resources.yaml')));

        return $this;
    }

    /**
     * Returns TRUE if resource record exists
     *
     * @param string $resource
     * @return bool
     */
    public function hasResource(string $resource): bool
    {
        return true === array_key_exists($resource, $this->registry['resources']);
    }

    /**
     * Selects resource record - if it does not exist, new one is appended
     *
     * @param string $resource
     * @return $this
     */
    public function selectResource(string $resource): self
    {
        if (false === $this->hasResource($resource)) {
            return $this->appendResource($resource)->selectResource($resource);
        }

        $this->selectedResource = $resource;

        return $this;
    }

    /**
     * Appends new resource record
     *
     * @param string $resource
     * @return $this
     */
    public function appendResource(string $resource): self
    {
        $this->registry['resources'][$resource] = [];

        return $this;
    }

    /**
     * Merges provided operations with already registered to the resource
     *
     * @param string ...$operations
     * @return $this
     * @throws \BadMethodCallException
     */
    public function mergeOperations(string ...$operations): self
    {
        if (null === $this->selectedResource) {
            throw new \BadMethodCallException('There is no selected resource to merge operations into. See selectResource().');
        }

        $this->registry['resources'][$this->selectedResource] = array_values(array_unique(array_merge(
            $this->registry['resources'][$this->selectedResource],
            $operations,
        )));

        return $this;
    }

    /**
     * Saves registry
     *
     * @return $this
     */
    public function save(): self
    {
        file_put_contents(
            $this->configLocator->locate('resources.yaml'),
            Yaml::dump($this->registry, 3, 2)
        );

        return $this;
    }
}
