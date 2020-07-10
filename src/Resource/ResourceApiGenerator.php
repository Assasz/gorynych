<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource;

use Cake\Collection\Collection;
use Gorynych\Adapter\TemplateEngineAdapterInterface;
use Gorynych\Resource\ApiGenerator\TemplateParameters;
use Gorynych\Resource\ApiGenerator\TemplateSchemas;

final class ResourceApiGenerator
{
    private TemplateEngineAdapterInterface $templateEngine;
    private ResourceConfigBuilder $resourcesConfigBuilder;

    /** @var \ReflectionClass<AbstractResource>|null */
    private ?\ReflectionClass $resourceReflection;
    private ?TemplateParameters $templateParameters;

    /**
     * @param TemplateEngineAdapterInterface $templateEngine
     * @param ResourceConfigBuilder $resourcesConfigBuilder
     */
    public function __construct(TemplateEngineAdapterInterface $templateEngine, ResourceConfigBuilder $resourcesConfigBuilder)
    {
        $this->templateEngine = $templateEngine;
        $this->resourcesConfigBuilder = $resourcesConfigBuilder;
    }

    /**
     * @param \ReflectionClass<AbstractResource> $resourceReflection
     */
    public function generate(\ReflectionClass $resourceReflection): void
    {
        $this->resourceReflection = $resourceReflection;
        $this->templateParameters = TemplateParameters::fromReflection($this->resourceReflection);

        foreach ($this->resolveTemplateSchema() as $schema) {
            $this->generateFromSingleSchema($schema);
        }

        $this->updateConfiguration();
    }

    /**
     * Generates all items specified by given schema
     * That includes resource operation and its corresponding test case
     *
     * @param string[][] $schema
     */
    private function generateFromSingleSchema(array $schema): void
    {
        foreach ($schema as $item) {
            $path = sprintf(dirname(__DIR__, 4) . $item['output'], $this->templateParameters->entityClassName);
            $content = $this->templateEngine->render($item['template'], (array)$this->templateParameters);

            $this->write($path, $content);
        }
    }

    /**
     * Updates resources.yaml configuration file
     */
    private function updateConfiguration(): void
    {
        $templateParameters = $this->templateParameters;

        $newConfigRecords = (new Collection($this->resolveTemplateSchema()))
            ->unfold()
            ->map(static function (array $schema) use ($templateParameters): string {
                return $templateParameters->rootNamespace .
                    str_replace('/', '\\',
                        sprintf($schema['operation']['output'], $templateParameters->entityClassName));
            })
            ->toList();

        $this->resourcesConfigBuilder
            ->selectResource($this->resourceReflection->getName())
            ->mergeOperations(...$newConfigRecords)
            ->save();
    }

    /**
     * @return string[][][]
     * @throws \LogicException
     */
    private function resolveTemplateSchema(): array
    {
        if ($this->resourceReflection->implementsInterface(CollectionResourceInterface::class)) {
            return TemplateSchemas::COLLECTION_RESOURCE_SCHEMA;
        }

        if ($this->resourceReflection->implementsInterface(ResourceInterface::class)) {
            return TemplateSchemas::ITEM_RESOURCE_SCHEMA;
        }

        throw new \LogicException('Unknown resource type.');
    }

    /**
     * @param string $path
     * @param string $content
     */
    private function write(string $path, string $content): void
    {
        $path .= '.php';
        $dir = dirname($path);

        if (false === is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (false === file_exists($path)) {
            file_put_contents($path, $content);
        }
    }
}
