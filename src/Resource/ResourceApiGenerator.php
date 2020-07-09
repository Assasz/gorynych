<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource;

use Gorynych\Adapter\TemplateEngineAdapterInterface;
use Gorynych\Resource\Generator\TemplateParams;

final class ResourceApiGenerator
{
    // TODO: read from config file
    private const ITEM_RESOURCE_OPERATIONS = [
        'operations/item/get.php.twig' => '/src/Ports/Operation/%s/GetOperation',
        'operations/item/remove.php.twig' => '/src/Ports/Operation/%s/RemoveOperation',
        'operations/item/replace.php.twig' => '/src/Ports/Operation/%s/ReplaceOperation',
    ];

    private const COLLECTION_RESOURCE_OPERATIONS = [
        'operations/collection/get.php.twig' => '/src/Ports/Operation/%sCollection/GetOperation',
        'operations/collection/insert.php.twig' => '/src/Ports/Operation/%sCollection/InsertOperation',
    ];

    private TemplateEngineAdapterInterface $templateEngine;
    private ResourceConfigBuilder $resourcesConfigBuilder;
    private ?\ReflectionClass $resourceReflection;

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
     * @param \ReflectionClass $resourceReflection
     */
    public function generate(\ReflectionClass $resourceReflection): void
    {
        $this->resourceReflection = $resourceReflection;

        foreach ($this->resolveOperationSet() as $templateName => $outputPath) {
            $this->generateOperation($templateName, $outputPath);
        }

        $this->updateConfiguration();
    }

    /**
     * @param string $templateName
     * @param string $outputPath
     */
    private function generateOperation(string $templateName, string $outputPath): void
    {
        $templateParams = TemplateParams::fromReflection($this->resourceReflection);
        $operationPath = dirname(__DIR__, 4) . sprintf($outputPath, $templateParams->entityClassName);

        $operationContent = $this->templateEngine->render($templateName, (array)$templateParams);

        $this->write($operationPath, $operationContent);
    }

    /**
     * Updates resources.yaml configuration file
     */
    private function updateConfiguration(): void
    {
        $templateParams = TemplateParams::fromReflection($this->resourceReflection);

        $newConfigRecords = array_map(
            static function (string $operationPath) use ($templateParams): string {
                return $templateParams->rootNamespace . str_replace('/', '\\', sprintf($operationPath, $templateParams->entityClassName));
            },
            array_values($this->resolveOperationSet())
        );

        $this->resourcesConfigBuilder
            ->selectResource($this->resourceReflection->getName())
            ->mergeOperations(...$newConfigRecords)
            ->save();
    }

    /**
     * @return string[]
     * @throws \InvalidArgumentException
     */
    private function resolveOperationSet(): array
    {
        if ($this->resourceReflection->implementsInterface(CollectionResourceInterface::class)) {
            return self::COLLECTION_RESOURCE_OPERATIONS;
        }

        if ($this->resourceReflection->implementsInterface(ResourceInterface::class)) {
            return self::ITEM_RESOURCE_OPERATIONS;
        }

        throw new \InvalidArgumentException('Unknown resource type.');
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
