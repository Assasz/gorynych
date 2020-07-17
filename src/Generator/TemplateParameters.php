<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Generator;

use Gorynych\Resource\AbstractResource;

final class TemplateParameters
{
    public string $rootNamespace;
    public string $resourceNamespace;
    public string $resourceClassName;
    public string $entityNamespace;
    public string $entityClassName;
    public EntityMock $entityMock;

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
        $self->entityNamespace = "{$rootNamespace}\Domain\Entity\\{$entityClassName}";
        $self->entityClassName = $entityClassName;
        $self->entityMock = EntityMock::create((new \ReflectionClass($self->entityNamespace)));

        return $self;
    }
}
