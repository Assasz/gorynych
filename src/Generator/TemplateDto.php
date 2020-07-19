<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Generator;

use Gorynych\Resource\AbstractResource;
use Symfony\Component\String\Inflector\EnglishInflector;

final class TemplateDto
{
    public string $rootNamespace;
    public string $resourceNamespace;
    public string $resourceClassName;
    public string $resourceSimpleName;
    public string $entityNamespace;
    public string $entityClassName;

    /**
     * @param \ReflectionClass<AbstractResource> $resourceReflection
     * @return self
     */
    public static function fromReflection(\ReflectionClass $resourceReflection): self
    {
        $resourceNamespaceParts = explode('\\', $resourceReflection->getNamespaceName());
        $rootNamespace = current($resourceNamespaceParts);
        $entityClassName = end($resourceNamespaceParts);

        $self = new self();
        $self->rootNamespace = $rootNamespace;
        $self->resourceNamespace = $resourceReflection->getName();
        $self->resourceClassName = $resourceReflection->getShortName();
        $self->resourceSimpleName = strtolower(current((new EnglishInflector())->pluralize($entityClassName)));
        $self->entityNamespace = self::getEntityNamespace($rootNamespace, $entityClassName);
        $self->entityClassName = $entityClassName;

        return $self;
    }

    /**
     * It's impossible to guess entity namespace from resource itself
     * So let it be a convention for simplicity purpose
     */
    private static function getEntityNamespace(string $rootNamespace, string $entityClassName): string
    {
        return sprintf('%s\Domain\Entity\%s', ...func_get_args());
    }
}
