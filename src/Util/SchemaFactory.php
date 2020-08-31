<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Util;

use Cake\Collection\Collection;
use Cake\Collection\CollectionInterface;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use const OpenApi\Annotations\UNDEFINED;

final class SchemaFactory
{
    /** @var Collection<int, Schema> */
    private Collection $schemas;

    public function __construct(OAReader $oaReader)
    {
        $this->schemas = new Collection($oaReader->read()->components->schemas);
    }

    /**
     * Creates single JSON schema for provided class name
     */
    public function create(string $className): string
    {
        $schemaName = (new \ReflectionClass($className))->getShortName();

        $schema = $this->schemas->filter(
            static fn(Schema $schema): bool => $schema->schema === $schemaName
        )->first();

        $relatedSchemas = $this->getRelatedSchemas($schema)->toArray();
        $schema = json_decode(json_encode($schema), true);
        $schema['components']['schemas'] = $relatedSchemas;

        return json_encode($schema);
    }

    /**
     * @return CollectionInterface<int, Schema>
     */
    private function getRelatedSchemas(Schema $schema): CollectionInterface
    {
        $schemas = $this->schemas;
        $relatedSchemas = new Collection([]);

        (new Collection($schema->properties))
            ->reject(
                static function(Property $property): bool {
                    /** @phpstan-ignore-next-line */
                    return UNDEFINED === $property->ref && UNDEFINED === $property->items;
                }
            )
            ->each(
                static function(Property $property) use ($schemas, &$relatedSchemas): void {
                    /** @phpstan-ignore-next-line */
                    $ref = $property->items instanceof Items ? $property->items->ref : $property->ref;

                    $relatedSchemas = $relatedSchemas->append(
                        $schemas->filter(
                            static function(Schema $schema) use ($ref): bool {
                                $ref = explode('/', $ref);
                                return $schema->schema === end($ref);
                            }
                        )
                    );
                }
            );

        return $relatedSchemas->indexBy(
            static fn(Schema $schema): string => $schema->schema
        );
    }
}
