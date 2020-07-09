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
use Symfony\Component\Yaml\Yaml;

final class ResourceApiGenerator
{
    private TemplateEngineAdapterInterface $templateEngine;
    private ResourceConfigBuilder $resourcesConfigBuilder;

    /** @var string[][][][] */
    private array $templateSchema;

    /** @var \ReflectionClass<AbstractResource>|null */
    private ?\ReflectionClass $resourceReflection;
    private ?TemplateParameters $templateParams;

    /**
     * @param TemplateEngineAdapterInterface $templateEngine
     * @param ResourceConfigBuilder $resourcesConfigBuilder
     */
    public function __construct(TemplateEngineAdapterInterface $templateEngine, ResourceConfigBuilder $resourcesConfigBuilder)
    {
        $this->templateEngine = $templateEngine;
        $this->resourcesConfigBuilder = $resourcesConfigBuilder;
        $this->templateSchema = $this->parseTemplateSchema();
    }

    /**
     * @param \ReflectionClass<AbstractResource> $resourceReflection
     */
    public function generate(\ReflectionClass $resourceReflection): void
    {
        $this->resourceReflection = $resourceReflection;
        $this->templateParams = TemplateParameters::fromReflection($this->resourceReflection);

        foreach ($this->resolveTemplateSchema() as $configuration) {
            $this->generateOperation(
                $configuration['operation']['template'],
                $configuration['operation']['output'],
            );
        }

        $this->updateConfiguration();
    }

    /**
     * @param string $templateName
     * @param string $outputPath
     */
    private function generateOperation(string $templateName, string $outputPath): void
    {
        $operationPath = dirname(__DIR__, 4) . sprintf($outputPath, $this->templateParams->entityClassName);
        $operationContent = $this->templateEngine->render($templateName, (array)$this->templateParams);

        $this->write($operationPath, $operationContent);
    }

    /**
     * Updates resources.yaml configuration file
     */
    private function updateConfiguration(): void
    {
        $templateParams = $this->templateParams;

        $newConfigRecords = (new Collection($this->resolveTemplateSchema()))
            ->unfold()
            ->map(static function (array $schema) use ($templateParams): string {
                return $templateParams->rootNamespace .
                    str_replace('/', '\\',
                        sprintf($schema['operation']['output'], $templateParams->entityClassName));
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
            return $this->templateSchema['collectionResource'];
        }

        if ($this->resourceReflection->implementsInterface(ResourceInterface::class)) {
            return $this->templateSchema['itemResource'];
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

    /**
     * @return string[][][][]
     */
    private function parseTemplateSchema(): array
    {
        return Yaml::parse(__DIR__ . '/ApiGenerator/template_schema.yaml');
    }
}
