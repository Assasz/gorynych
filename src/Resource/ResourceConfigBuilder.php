<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Yaml\Yaml;

final class ResourceConfigBuilder
{
    private FileLocatorInterface $configLocator;
    /** @var string[][][] */
    private array $configuration;
    private ?string $selectedResource;

    public function __construct(FileLocatorInterface $configLocator)
    {
        $this->configLocator = $configLocator;
        $this->load();
    }

    /**
     * Loads resources configuration
     *
     * @return $this
     */
    public function load(): self
    {
        $this->configuration = Yaml::parse(file_get_contents($this->configLocator->locate('resources.yaml')));

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
        return true === array_key_exists($resource, $this->configuration['resources']);
    }

    /**
     * Selects resource record - if it does not exist, new one is appended
     *
     * @param string $resource
     * @return $this
     */
    public function selectResource(string $resource): self
    {
        $this->selectedResource = (false === $this->hasResource($resource)) ?
            $this->appendResource($resource)->selectResource($resource) :
            $resource;

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
        $this->configuration['resources'][$resource] = [];

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

        $this->configuration['resources'][$this->selectedResource] = array_values(array_unique(array_merge(
            $operations,
            $this->configuration['resources'][$this->selectedResource]
        )));

        return $this;
    }

    /**
     * Saves configuration
     *
     * @return $this
     */
    public function save(): self
    {
        file_put_contents(
            $this->configLocator->locate('resources.yaml'),
            Yaml::dump($this->configuration, 3, 2)
        );

        return $this;
    }
}
