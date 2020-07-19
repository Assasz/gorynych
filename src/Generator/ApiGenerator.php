<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Generator;

use Cake\Collection\Collection;
use Gorynych\Adapter\TwigAdapter;
use Gorynych\Resource\AbstractResource;
use Gorynych\Resource\CollectionResourceInterface;
use Gorynych\Resource\ResourceRegistryBuilder;
use Gorynych\Resource\ResourceInterface;
use Gorynych\Util\OpenApiScanner;

final class ApiGenerator
{
    private TwigAdapter $templateEngine;
    private ResourceRegistryBuilder $resourcesConfigBuilder;
    private FileWriter $fileWriter;

    /** @var \ReflectionClass<AbstractResource>|null */
    private ?\ReflectionClass $resourceReflection;
    private ?TemplateParameters $templateParameters;

    public function __construct(
        TwigAdapter $templateEngine,
        ResourceRegistryBuilder $resourcesConfigBuilder,
        FileWriter $fileWriter
    ) {
        $this->templateEngine = $templateEngine;
        $this->resourcesConfigBuilder = $resourcesConfigBuilder;
        $this->fileWriter = $fileWriter;
    }

    /**
     * @param \ReflectionClass<AbstractResource> $resourceReflection
     * @throws \RuntimeException
     */
    public function generate(\ReflectionClass $resourceReflection): void
    {
        if (false === array_key_exists('PROJECT_DIR', $_ENV)) {
            throw new \RuntimeException('Please make sure that PROJECT_DIR environmental variable is defined.');
        }

        $this->resourceReflection = $resourceReflection;
        $this->templateParameters = TemplateParameters::fromReflection($this->resourceReflection);

        foreach ($this->resolveTemplateSchema() as $schema) {
            $this->generateFromSingleSchema($schema);
        }

        // TODO: generate fixtures
        $this->updateConfiguration();
        $this->updateDocumentation();
    }

    /**
     * Generates all items specified by given schema
     * That includes resource operation and its corresponding test case
     *
     * @param string[][] $schema
     */
    private function generateFromSingleSchema(array $schema): void
    {
        foreach ($schema as $itemName => $item) {
            $path = sprintf($_ENV['PROJECT_DIR'] . $item['output'], $this->templateParameters->entityClassName);
            $content = $this->templateEngine->render($item['template'], (array)$this->templateParameters);

            $this->fileWriter->write($path, $content);
        }
    }

    /**
     * Updates resources.yaml configuration file
     */
    private function updateConfiguration(): void
    {
        $templateParameters = $this->templateParameters;

        $newConfigRecords = (new Collection($this->resolveTemplateSchema()))
            ->map(static function (array $operationSchema) use ($templateParameters): string {
                return $templateParameters->rootNamespace . '\\' .
                    str_replace('/', '\\',
                        sprintf(
                            rtrim(ltrim($operationSchema['operation']['output'], '/src'), '.php'),
                            $templateParameters->entityClassName
                        ));
            })
            ->toList();

        $this->resourcesConfigBuilder
            ->selectResource($this->resourceReflection->getName())
            ->mergeOperations(...$newConfigRecords)
            ->save();
    }

    /**
     * Updates openapi.yaml documentation file
     */
    private function updateDocumentation(): void
    {
        $this->fileWriter
            ->forceOverwrite()
            ->write("{$_ENV['PROJECT_DIR']}/openapi/openapi.yaml", OpenApiScanner::scan()->toYaml());
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
}
