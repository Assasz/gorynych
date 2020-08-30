<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Util;

use Cake\Collection\Collection;
use OpenApi\Annotations\Schema;
use const OpenApi\Annotations\UNDEFINED;

final class SchemaFactory
{
    /** @var Collection<int, Schema> */
    private Collection $schemas;

    public function __construct(OAReader $oaReader)
    {
        $this->schemas = new Collection($oaReader->read()->components->schemas);;
    }

    /**
     * Creates single JSON schema for provided class name
     */
    public function create(string $className): string
    {
        $schemaName = (new \ReflectionClass($className))->getShortName();

        $schema = $this->schemas->filter(
            static function(Schema $schema) use ($schemaName): bool {
                return $schema->schema === $schemaName;
            }
        )->first();

        $relatedSchemas = new Collection([$schema]);

        foreach ($schema->properties as $property) {
            if (UNDEFINED === $property->ref) {
                continue;
            }

            $relatedSchemas = $relatedSchemas->append(
                $this->schemas->filter(
                    static function(Schema $schema) use ($property): bool {
                        return $schema->schema === ucfirst($property->property);
                    }
                )
            );
        }

        return json_encode([
            'schemas' => $relatedSchemas->toArray()
        ]);
    }
}
