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
            $this->generateOperation(
                $schema['operation']['template'],
                $schema['operation']['output'],
            );
            // TODO: generate tests
        }

        $this->updateConfiguration();
    }

    /**
     * @param string $templateName
     * @param string $outputPath
     */
    private function generateOperation(string $templateName, string $outputPath): void
    {
        $operationPath = dirname(__DIR__, 4) . sprintf($outputPath, $this->templateParameters->entityClassName);
        $operationContent = $this->templateEngine->render($templateName, (array)$this->templateParameters);

        $this->write($operationPath, $operationContent);
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
