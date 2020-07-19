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
use Gorynych\Util\EnvAccess;
use Gorynych\Util\FixturesFactory;
use Gorynych\Util\OpenApiScanner;
use Symfony\Component\Yaml\Yaml;

final class ApiGenerator
{
    private TwigAdapter $templateEngine;
    private ResourceRegistryBuilder $resourcesConfigBuilder;
    private FileWriter $fileWriter;
    private FixturesFactory $fixturesFactory;

    /** @var \ReflectionClass<AbstractResource>|null */
    private ?\ReflectionClass $resourceReflection;
    private ?TemplateDto $templateDto;

    public function __construct(
        TwigAdapter $templateEngine,
        ResourceRegistryBuilder $resourcesConfigBuilder,
        FileWriter $fileWriter,
        FixturesFactory $fixturesFactory
    ) {
        $this->templateEngine = $templateEngine;
        $this->resourcesConfigBuilder = $resourcesConfigBuilder;
        $this->fileWriter = $fileWriter;
        $this->fixturesFactory = $fixturesFactory;
    }

    /**
     * @param \ReflectionClass<AbstractResource> $resourceReflection
     */
    public function generate(\ReflectionClass $resourceReflection): void
    {
        $this->resourceReflection = $resourceReflection;
        $this->templateDto = TemplateDto::fromReflection($this->resourceReflection);

        foreach ($this->resolveTemplateSchema() as $schema) {
            $this->generateFromSingleSchema($schema);
        }

        $this->generateFixtures()->updateConfiguration()->updateDocumentation();
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
            $path = sprintf(EnvAccess::get('PROJECT_DIR') . $item['output'], $this->templateDto->entityClassName);
            $content = $this->templateEngine->render($item['template'], (array)$this->templateDto);

            $this->fileWriter->write($path, $content);
        }
    }

    /**
     * Generates test fixtures
     *
     * @return self
     */
    private function generateFixtures(): self
    {
        $path = EnvAccess::get('PROJECT_DIR') . "/config/fixtures/{$this->templateDto->resourceSimpleName}.yaml";
        $fixtures = $this->fixturesFactory->create($this->templateDto->entityNamespace);

        $this->fileWriter->write($path, Yaml::dump($fixtures, 3, 2));

        return $this;
    }

    /**
     * Updates resources.yaml configuration file
     *
     * @return self
     */
    private function updateConfiguration(): self
    {
        $templateParameters = $this->templateDto;

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

        return $this;
    }

    /**
     * Updates openapi.yaml documentation file
     *
     * @return self
     */
    private function updateDocumentation(): self
    {
        $this->fileWriter->forceOverwrite()->write(
            EnvAccess::get('PROJECT_DIR') . '/openapi/openapi.yaml',
            OpenApiScanner::scan()->toYaml()
        );

        return $this;
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
