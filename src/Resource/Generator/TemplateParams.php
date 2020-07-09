<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource\Generator;

use Gorynych\Resource\AbstractResource;

final class TemplateParams
{
    public string $rootNamespace;
    public string $resourceNamespace;
    public string $resourceClassName;
    public string $entityNamespace;
    public string $entityClassName;

    /**
     * @param \ReflectionClass<AbstractResource> $resourceReflection
     * @return self
     */
    public static function fromReflection(\ReflectionClass $resourceReflection): self
    {
        $rootNamespace = current(explode('\\', $resourceReflection->getNamespaceName()));
        $entityClassName = preg_replace('(Resource|CollectionResource)', '', $resourceReflection->getShortName());

        $self = new self();
        $self->rootNamespace = $rootNamespace;
        $self->resourceNamespace = $resourceReflection->getName();
        $self->resourceClassName = $resourceReflection->getShortName();
        $self->entityNamespace = "{$rootNamespace}\Domain\Entity\\{$entityClassName}";
        $self->entityClassName = (string)$entityClassName;

        return $self;
    }
}
