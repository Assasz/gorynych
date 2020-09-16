<?php

declare(strict_types=1);

namespace Gorynych\Tests\Util;

use Cake\Collection\Collection;
use DG\BypassFinals;
use Gorynych\Tests\Util\Resource\TestSchema;
use Gorynych\Util\SchemaFactory;
use Gorynych\Util\SchemaStorage;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SchemaFactoryTest extends TestCase
{
    /** @var SchemaStorage|MockObject  */
    private $schemaStorageMock;

    public function setUp(): void
    {
        BypassFinals::enable();
        $this->schemaStorageMock = $this->createMock(SchemaStorage::class);
    }

    /**
     * @dataProvider provideSchemas
     * @param mixed[] $schemas
     */
    public function testCreatesSchema(array $schemas, string $schemaClassName, string $expectedSchema): void
    {
        $this->schemaStorageMock->method('getSchemas')->willReturn(new Collection($schemas));
        $schema = $this->setUpFactory()->create($schemaClassName);

        $this->assertSame($expectedSchema, $schema);
    }

    /**
     * @return \Generator<array>
     */
    public function provideSchemas(): \Generator
    {
        $relatedSchema = new Schema([]);
        $relatedSchema->schema = 'RelatedSchema';

        $property = new Property([]);
        $property->property = 'related';
        $property->ref = '#/components/schemas/RelatedSchema';

        $schema = new Schema([]);
        $schema->schema = 'TestSchema';
        $schema->properties = [$property];

        yield 'one schema with one related' => [
            [$schema, $relatedSchema],
            TestSchema::class,
            '{"schema":"TestSchema","properties":{"related":{"$ref":"#\/components\/schemas\/RelatedSchema"}},"components":{"schemas":{"RelatedSchema":{"schema":"RelatedSchema"}}}}',
        ];
    }

    private function setUpFactory(): SchemaFactory
    {
        return new SchemaFactory($this->schemaStorageMock);
    }
}
